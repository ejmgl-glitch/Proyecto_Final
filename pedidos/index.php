<?php
// pedidos/index.php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$role = currentRole();
$userId = (int)$_SESSION['user']['id'];
$esStaff = in_array($role, ['admin', 'trabajador'], true);

// Si es admin o trabajador consulta todos los pedidos junto con los datos del cliente
if ($esStaff) {
    $stmt = $pdo->query(
        'SELECT p.*, u.nombre AS cliente_nombre, u.correo AS cliente_correo, u.telefono AS cliente_telefono 
         FROM pedido p 
         JOIN usuario u ON u.id = p.id_usuario 
         ORDER BY p.fecha DESC, p.id DESC'
    );
    $pedidos = $stmt->fetchAll();
    $pageTitle = 'Gestión de Todos los Pedidos';
} else {
    // Si es cliente, solo consulta sus propios pedidos
    $stmt = $pdo->prepare(
        'SELECT * FROM pedido 
         WHERE id_usuario = ? 
         ORDER BY fecha DESC, id DESC'
    );
    $stmt->execute([$userId]);
    $pedidos = $stmt->fetchAll();
    $pageTitle = 'Historial de Mis Pedidos';
}

// Consultar detalles de los pedidos encontrados
$detallesPorPedido = [];
if (!empty($pedidos)) {
    $pedidoIds = array_column($pedidos, 'id');
    $placeholders = implode(',', array_fill(0, count($pedidoIds), '?'));
    
    $stmtDetalle = $pdo->prepare(
        "SELECT dp.*, p.nombre, p.marca, p.imagen, p.precio AS precio_catalogo
         FROM detalle_pedido dp 
         JOIN producto p ON p.id = dp.id_producto 
         WHERE dp.id_pedido IN ($placeholders)"
    );
    $stmtDetalle->execute($pedidoIds);
    $detalles = $stmtDetalle->fetchAll();
    foreach ($detalles as $det) {
        $detallesPorPedido[$det['id_pedido']][] = $det;
    }
}

// Asignar número correlativo por cliente (el primer pedido histórico del cliente es el #1)
if (!$esStaff && !empty($pedidos)) {
    $totalMisPedidos = count($pedidos);
    foreach ($pedidos as $idx => &$ped) {
        // Al estar ordenados de más reciente a más antiguo (DESC), el correlativo va descendiendo
        $ped['numero_cliente'] = $totalMisPedidos - $idx;
    }
    unset($ped);
}

require __DIR__ . '/../includes/header.php';
?>

<div class="container" style="max-width: 850px; margin: 30px auto; padding: 0 15px;">
    
    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash flash-<?= h($_SESSION['flash']['type']) ?>">
            <?= h($_SESSION['flash']['msg']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 10px;">
        <h1 style="margin: 0;"><?= $esStaff ? 'Gestión de Pedidos de Clientes' : 'Mis Pedidos Realizados' ?></h1>
        <a href="<?= url('/productos/index.php') ?>" class="btn btn-secondary btn-sm">&larr; Ver productos</a>
    </div>

    <?php if (empty($pedidos)): ?>
        <div class="card empty-state" style="text-align: center; padding: 50px 20px;">
            <div style="font-size: 3rem; margin-bottom: 10px;">📦</div>
            <h2><?= $esStaff ? 'No hay pedidos registrados en la plataforma' : 'Aún no has realizado pedidos' ?></h2>
            <p class="muted"><?= $esStaff ? 'Los pedidos que hagan los clientes aparecerán listados aquí.' : 'Tus compras confirmadas aparecerán aquí con su detalle.' ?></p>
        </div>
    <?php else: ?>
        <div style="display: flex; flex-direction: column; gap: 24px;">
            <?php foreach ($pedidos as $pedido): ?>
                <div class="card" style="padding: 24px; border-radius: 12px; margin-bottom: 0;">
                    
                    <!-- 1. PEDIDO (Y ESTADO) -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px;">
                        <h2 style="margin: 0; font-size: 1.35rem; color: #1e293b;">
                            <?= $esStaff ? 'Pedido #' . (int)$pedido['id'] : 'Pedido #' . (int)$pedido['numero_cliente'] ?>
                        </h2>
                        
                        <?php if ($esStaff): ?>
                            <!-- Formulario de cambio de estado para Admin y Empleado -->
                            <form method="post" action="<?= url('/pedidos/cambiar_estado.php') ?>" style="display: flex; align-items: center; gap: 8px; margin: 0;">
                                <input type="hidden" name="pedido_id" value="<?= (int)$pedido['id'] ?>">
                                <select name="estado" style="width: auto; padding: 5px 9px; font-size: 0.85rem; border-radius: 6px;">
                                    <option value="realizado" <?= $pedido['estado'] === 'realizado' ? 'selected' : '' ?>>Realizado</option>
                                    <option value="enviado" <?= $pedido['estado'] === 'enviado' ? 'selected' : '' ?>>Enviado</option>
                                    <option value="entregado" <?= $pedido['estado'] === 'entregado' ? 'selected' : '' ?>>Entregado</option>
                                </select>
                                <button type="submit" class="btn btn-sm">Actualizar</button>
                            </form>
                        <?php else: ?>
                            <span class="badge" style="background: #e2e8f0; font-weight: 600; text-transform: capitalize; padding: 5px 12px; font-size: 0.85rem;">
                                <?= h($pedido['estado']) ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <?php if ($esStaff): ?>
                        <div style="margin-bottom: 6px; font-size: 0.9rem; color: var(--text);">
                            <strong>Cliente:</strong> <?= h($pedido['cliente_nombre']) ?> (<?= h($pedido['cliente_correo']) ?>)
                            <?php if (!empty($pedido['cliente_telefono'])): ?>
                                | Tel: <?= h($pedido['cliente_telefono']) ?>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <!-- 2. FECHA -->
                    <div class="muted" style="font-size: 0.9rem; margin-bottom: 18px;">
                        Fecha: <?= h(date('d/m/Y H:i', strtotime($pedido['fecha']))) ?>
                    </div>

                    <!-- 3. IMAGEN Y DETALLES DEL PRODUCTO (CON PRECIO UNITARIO) -->
                    <div style="background: var(--bg); border: 1px solid var(--border); border-radius: 8px; padding: 14px; margin-bottom: 18px;">
                        <?php 
                        $items = $detallesPorPedido[$pedido['id']] ?? [];
                        foreach ($items as $idxItem => $item): 
                            $cant = max(1, (int)$item['cantidad']);
                            $precioUnitario = (float)($item['subtotal'] / $cant);
                        ?>
                            <div style="display: flex; justify-content: space-between; align-items: center; gap: 15px; <?= $idxItem > 0 ? 'margin-top: 14px; padding-top: 14px; border-top: 1px dashed var(--border);' : '' ?>">
                                <div style="display: flex; align-items: center; gap: 14px;">
                                    <?php if (!empty($item['imagen'])): ?>
                                        <img src="<?= h($item['imagen']) ?>" alt="<?= h($item['nombre']) ?>" style="width: 60px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border);">
                                    <?php else: ?>
                                        <div style="width: 60px; height: 60px; background: #e2e8f0; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.5rem;">👟</div>
                                    <?php endif; ?>
                                    <div>
                                        <div style="font-weight: 700; font-size: 1rem; color: #1e293b;"><?= h($item['nombre']) ?></div>
                                        <div class="muted" style="font-size: 0.85rem; margin-top: 2px;">
                                            <?= h($item['marca']) ?> &bull; Cantidad: <strong><?= $cant ?></strong>
                                        </div>
                                    </div>
                                </div>
                                <div style="text-align: right;">
                                    <div style="font-size: 0.85rem; color: var(--muted);">Precio unitario:</div>
                                    <div style="font-weight: 600; font-size: 0.98rem; color: #1e293b;">
                                        Q <?= number_format($precioUnitario, 2) ?>
                                    </div>
                                    <div style="font-size: 0.8rem; color: var(--muted); margin-top: 2px;">
                                        Subtotal: Q <?= number_format((float)$item['subtotal'], 2) ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- 4. TOTAL DEL PEDIDO -->
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                        <span style="font-size: 1rem; font-weight: 600; color: #1e293b;">Total del pedido:</span>
                        <span class="price" style="font-size: 1.35rem; font-weight: 800;">
                            Q <?= number_format((float)$pedido['total'], 2) ?>
                        </span>
                    </div>

                    <!-- 5. MÉTODO DE PAGO -->
                    <div style="font-size: 0.92rem; color: var(--muted);">
                        <strong>Método de pago:</strong> 
                        <span style="text-transform: capitalize; color: #1e293b; font-weight: 500;">
                            <?= h($pedido['metodo_pago']) ?>
                        </span>
                    </div>

                    <!-- 6. LÍNEA QUE DIVIDE -->
                    <hr style="border: none; border-top: 1px solid var(--border); margin: 20px 0 0 0;">

                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>