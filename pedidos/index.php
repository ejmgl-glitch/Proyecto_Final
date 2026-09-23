<?php
// pedidos/index.php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Solo usuarios autenticados pueden ver su historial
requireLogin();

$userId = (int)$_SESSION['user']['id'];

// Consultar todos los pedidos del usuario ordenados por fecha descendente
$stmt = $pdo->prepare(
    'SELECT * FROM pedido 
     WHERE id_usuario = ? 
     ORDER BY fecha DESC, id DESC'
);
$stmt->execute([$userId]);
$pedidos = $stmt->fetchAll();

// Si existen pedidos, consultar los detalles asociados agrupados por id_pedido
$detallesPorPedido = [];
if (!empty($pedidos)) {
    $pedidoIds = array_column($pedidos, 'id');
    $placeholders = implode(',', array_fill(0, count($pedidoIds), '?'));
    
    $stmtDetalle = $pdo->prepare(
        "SELECT dp.*, p.nombre, p.marca, p.imagen, p.precio AS precio_unitario
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

$pageTitle = 'Historial de Mis Pedidos';
require __DIR__ . '/../includes/header.php';
?>

<div class="container" style="max-width: 900px; margin: 30px auto; padding: 0 15px;">
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; flex-wrap: wrap; gap: 10px;">
        <h1 style="margin: 0;">Mis Pedidos Realizados</h1>
        <a href="<?= url('/productos/index.php') ?>" class="btn btn-secondary btn-sm">&larr; Seguir comprando</a>
    </div>

    <?php if (empty($pedidos)): ?>
        <div class="card empty-state" style="text-align: center; padding: 50px 20px;">
            <div style="font-size: 3rem; margin-bottom: 10px;">📦</div>
            <h2>Aún no has realizado pedidos</h2>
            <p class="muted">Tus compras y órdenes confirmadas aparecerán aquí con su detalle.</p>
            <div style="margin-top: 20px;">
                <a href="<?= url('/productos/index.php') ?>" class="btn">Explorar catálogo</a>
            </div>
        </div>
    <?php else: ?>
        <?php foreach ($pedidos as $pedido): ?>
            <div class="order-card">
                <div class="order-header">
                    <div>
                        <strong style="font-size: 1.1rem; color: #1e293b;">Pedido #<?= (int)$pedido['id'] ?></strong>
                        <div class="muted" style="font-size: 0.85rem; margin-top: 3px;">
                            Fecha: <?= h(date('d/m/Y H:i', strtotime($pedido['fecha']))) ?>
                        </div>
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <span class="badge-estado badge-<?= h($pedido['estado']) ?>">
                            <?= h($pedido['estado']) ?>
                        </span>
                        <span class="price" style="font-size: 1.25rem;">
                            Q <?= number_format((float)$pedido['total'], 2) ?>
                        </span>
                    </div>
                </div>

                <div style="margin-bottom: 12px; font-size: 0.9rem;">
                    <strong>Método de pago:</strong> 
                    <span style="text-transform: capitalize; color: var(--muted);">
                        <?= h($pedido['metodo_pago']) ?>
                    </span>
                </div>

                <!-- Lista de artículos comprados en este pedido -->
                <div class="order-items-list">
                    <?php 
                    $items = $detallesPorPedido[$pedido['id']] ?? [];
                    foreach ($items as $item): 
                    ?>
                        <div class="order-item-row">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <?php if (!empty($item['imagen'])): ?>
                                    <img src="<?= h($item['imagen']) ?>" alt="<?= h($item['nombre']) ?>" style="width: 48px; height: 48px; object-fit: cover; border-radius: 6px; border: 1px solid var(--border);">
                                <?php else: ?>
                                    <div style="width: 48px; height: 48px; background: #e2e8f0; border-radius: 6px; display: flex; align-items: center; justify-content: center;">👟</div>
                                <?php endif; ?>
                                <div>
                                    <div style="font-weight: 600; font-size: 0.95rem;"><?= h($item['nombre']) ?></div>
                                    <div class="muted" style="font-size: 0.8rem;"><?= h($item['marca']) ?> &bull; Cantidad: <?= (int)$item['cantidad'] ?></div>
                                </div>
                            </div>
                            <div style="font-weight: 600; font-size: 0.95rem;">
                                Q <?= number_format((float)$item['subtotal'], 2) ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>