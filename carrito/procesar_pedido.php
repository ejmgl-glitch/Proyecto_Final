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

$items = $input['items'];$metodosValidos = ['tarjeta', 'pay pal', 'transferencia'];
$metodoPago = in_array($input['metodo_pago'] ?? '', $metodosValidos, true) ?$input['metodo_pago'] : 'tarjeta';
$userId = (int)$_SESSION['user']['id'];

try {
    $pdo->beginTransaction();

    // 3. Extraer IDs de productos y validar existencias/precios en la base de datos
    $productIds = array_map(function($i) { return (int)$i['id']; },$items);
    $inPlaceholders = implode(',', array_fill(0, count($productIds), '?'));
    
    $stmt =$pdo->prepare("SELECT id, precio, nombre FROM producto WHERE id IN ($inPlaceholders)");
    $stmt->execute($productIds);$productosDb = $stmt->fetchAll(PDO::FETCH_ASSOC);$listaDb = [];
    foreach ($productosDb as $p) {$listaDb[$p['id']] =$p;
    }

    $totalCalculado = 0.00;
    $detallesAInsertar = [];

    foreach ($items as$item) {
        $idProd = (int)$item['id'];
        $cantidad = max(1, (int)($item['cantidad'] ?? 1));

        if (!isset($listaDb[$idProd])) {
            throw new Exception("El producto con ID {$idProd} no existe en catálogo.");
        }

        $precioDb = (float)$listaDb[$idProd]['precio'];$subtotal = $precioDb * $cantidad;
        $totalCalculado +=$subtotal;

        $detallesAInsertar[] = [
            'id_producto' => $idProd,
            'cantidad'    => $cantidad,
            'subtotal'    => $subtotal
        ];
    }

    // 4. Crear el registro en `pedido`
    $stmtPedido =$pdo->prepare(
        'INSERT INTO pedido (id_usuario, fecha, total, estado, metodo_pago) 
         VALUES (?, NOW(), ?, "realizado", ?)'
    );
    $stmtPedido->execute([$userId, $totalCalculado,$metodoPago]);
    $idPedido = (int)$pdo->lastInsertId();

    // 5. Crear los registros en `detalle_pedido`
    $stmtDetalle =$pdo->prepare(
        'INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, subtotal) 
         VALUES (?, ?, ?, ?)'
    );

    foreach ($detallesAInsertar as $det) {$stmtDetalle->execute([
            $idPedido,$det['id_producto'],
            $det['cantidad'],$det['subtotal']
        ]);
    }

    $pdo->commit();

    echo json_encode([
        'success'   => true,
        'mensaje'   => '¡Tu pedido fue realizado con éxito!',
        'pedido_id' => $idPedido,
        'total'     => number_format($totalCalculado, 2)     ]); } catch (Exception$e) {
    if ($pdo->inTransaction()) {$pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}