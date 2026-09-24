<?php
// carrito/procesar_pedido.php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// 1. Validar que el usuario esté logueado y sea 'cliente'
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Debes iniciar sesión para comprar.']);
    exit;
}

if (currentRole() !== 'cliente') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'error' => 'Solo los usuarios con rol "cliente" pueden realizar compras.'
    ]);
    exit;
}

// 2. Leer payload JSON enviado por React
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['items'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'El carrito está vacío o la solicitud es inválida.']);
    exit;
}

$items = $input['items'];
$metodosValidos = ['tarjeta', 'pay pal', 'transferencia'];
$metodoPago = in_array($input['metodo_pago'] ?? '', $metodosValidos, true) ? $input['metodo_pago'] : 'tarjeta';
$userId = (int)$_SESSION['user']['id'];

try {
    $pdo->beginTransaction();

    $totalCalculado = 0.00;
    $detallesAInsertar = [];

    // Bloqueamos cada variante mientras validamos, para que dos compras simultáneas
    // no puedan vender el mismo par dos veces.
    $stmtVariante = $pdo->prepare(
        'SELECT vp.id, vp.talla, vp.stock, vp.id_producto, p.nombre, p.precio
         FROM variante_producto vp
         JOIN producto p ON p.id = vp.id_producto
         WHERE vp.id = ?
         FOR UPDATE'
    );

    foreach ($items as $item) {
        $idVariante = (int)($item['id_variante'] ?? 0);
        $cantidad = max(1, (int)($item['cantidad'] ?? 1));

        if (!$idVariante) {
            throw new Exception('Falta indicar la talla de uno de los productos.');
        }

        $stmtVariante->execute([$idVariante]);
        $variante = $stmtVariante->fetch();

        if (!$variante) {
            throw new Exception("La talla seleccionada ya no está disponible.");
        }

        if ((int)$variante['stock'] < $cantidad) {
            throw new Exception(
                "Ya no hay stock suficiente de \"{$variante['nombre']}\" talla {$variante['talla']}. " .
                "Disponible: {$variante['stock']}."
            );
        }

        $precioDb = (float)$variante['precio'];
        $subtotal = $precioDb * $cantidad;
        $totalCalculado += $subtotal;

        $detallesAInsertar[] = [
            'id_producto' => (int)$variante['id_producto'],
            'id_variante' => $idVariante,
            'talla'       => $variante['talla'],
            'cantidad'    => $cantidad,
            'subtotal'    => $subtotal,
        ];
    }

    // 3. Crear el registro en `pedido`
    $stmtPedido = $pdo->prepare(
        'INSERT INTO pedido (id_usuario, fecha, total, estado, metodo_pago)
         VALUES (?, NOW(), ?, "realizado", ?)'
    );
    $stmtPedido->execute([$userId, $totalCalculado, $metodoPago]);
    $idPedido = (int)$pdo->lastInsertId();

    // 4. Crear los registros en `detalle_pedido` y descontar el stock de cada variante
    $stmtDetalle = $pdo->prepare(
        'INSERT INTO detalle_pedido (id_pedido, id_producto, id_variante, talla, cantidad, subtotal)
         VALUES (?, ?, ?, ?, ?, ?)'
    );
    $stmtDescontar = $pdo->prepare(
        'UPDATE variante_producto SET stock = stock - ? WHERE id = ?'
    );

    foreach ($detallesAInsertar as $det) {
        $stmtDetalle->execute([
            $idPedido, $det['id_producto'], $det['id_variante'], $det['talla'],
            $det['cantidad'], $det['subtotal']
        ]);
        $stmtDescontar->execute([$det['cantidad'], $det['id_variante']]);
    }

    $pdo->commit();

    echo json_encode([
        'success'   => true,
        'mensaje'   => '¡Tu pedido fue realizado con éxito!',
        'pedido_id' => $idPedido,
        'total'     => number_format($totalCalculado, 2)
    ]);
} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}