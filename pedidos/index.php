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

require __DIR__ . '/../includes/header.php';
?>

<div class="container" style="max-width: 960px; margin: 30px auto; padding: 0 15px;">
    
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
        <?php foreach ($pedidos as $pedido): ?>
            <div class="order-card">
                <div class="order-header">
                    <div>
                        <strong style="font-size: 1.15rem; color: #1e293b;">Pedido #<?= (int)$pedido['id'] ?></strong>
                        <div class="muted" style="font-size: 0.85rem; margin-top: 3px;">
                            Fecha: <?= h(date('d/m/Y H:i', strtotime($pedido['fecha']))) ?>
                        </div>
                        <?php if ($esStaff): ?>
                            <div style="margin-top: 5px; font-size: 0.9rem;">
                                👤 <strong>Cliente:</strong> <?= h($pedido['cliente_nombre']) ?> (<?= h($pedido['cliente_correo']) ?>)
                                <?php if (!empty($pedido['cliente_telefono'])): ?>
                                    | 📞 <?= h($pedido['cliente_telefono']) ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <span class="price" style="font-size: 1.25rem;">
                            Q <?= number_format((float)$pedido['total'], 2) ?>
                        </span>
                    </div>
                </div>

                <!-- Control de Estado y Método de Pago -->
                <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px; margin-bottom: 15px; padding-bottom: 10px; border-bottom: 1px solid var(--border);">
                    <div style="font-size: 0.9rem;">
                        <strong>Método de pago:</strong> 
                        <span style="text-transform: capitalize; color: var(--muted);">
                            <?= h($pedido['metodo_pago']) ?>
                        </span>
                    </div>

                    <?php if ($esStaff): ?>
                        <!-- Formulario para que Admin y Trabajador cambien el estado -->
                        <form method="post" action="<?= url('/pedidos/cambiar_estado.php') ?>" style="display: flex; align-items: center; gap: 8px;">
                            <input type="hidden" name="pedido_id" value="<?= (int)$pedido['id'] ?>">
                            <label style="margin: 0; font-size: 0.85rem; font-weight: 600;">Estado:</label>
                            <select name="estado" style="width: auto; padding: 4px 8px; font-size: 0.85rem;">
                                <option value="realizado" <?= $pedido['estado'] === 'realizado' ? 'selected' : '' ?>>Realizado</option>
                                <option value="enviado" <?= $pedido['estado'] === 'enviado' ? 'selected' : '' ?>>Enviado</option>
                                <option value="entregado" <?= $pedido['estado'] === 'entregado' ? 'selected' : '' ?>>Entregado</option>
                            </select>
                            <button type="submit" class="btn btn-sm">Actualizar</button>
                        </form>
                    <?php else: ?>
                        <span class="badge-estado badge-<?= h($pedido['estado']) ?>">
                            <?= h($pedido['estado']) ?>
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Lista de artículos en el pedido -->
                <div class="order-items-list">
                    <?php 
                    $items = $detallesPorPedido[$pedido['id']] ?? [];
                    foreach ($items as $item): 
                    ?>
                        <div class="order-item-row">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <?php if (!empty($item['imagen'])): ?>
                                    <img src="<?= h($item['imagen']) ?>" alt="<?= h($item['nombre']) ?>" style="width: 45px; height: 45px; object-fit: cover; border-radius: 6px; border: 1px solid var(--border);">
                                <?php else: ?>
                                    <div style="width: 45px; height: 45px; background: #e2e8f0; border-radius: 6px; display: flex; align-items: center; justify-content: center;">👟</div>
                                <?php endif; ?>
                                <div>
                                    <div style="font-weight: 600; font-size: 0.92rem;"><?= h($item['nombre']) ?></div>
                                    <div class="muted" style="font-size: 0.8rem;"><?= h($item['marca']) ?> • Cantidad: <?= (int)$item['cantidad'] ?></div>
                                </div>
                            </div>
                            <div style="font-weight: 600; font-size: 0.92rem;">
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