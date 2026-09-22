<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/functions.php';

// Cualquier usuario logueado tiene su propia wishlist (pensado sobre todo para clientes,
// pero trabajador/admin también pueden usarla).
requireLogin();
$userId = (int)$_SESSION['user']['id'];

$stmt = $pdo->prepare(
    'SELECT w.id AS wishlist_id, p.*
     FROM wishlist w
     JOIN producto p ON p.id = w.id_producto
     WHERE w.id_usuario = ?
     ORDER BY w.id DESC'
);
$stmt->execute([$userId]);
$items = $stmt->fetchAll();

$pageTitle = 'Mi wishlist';
require __DIR__ . '/../includes/header.php';
?>
<h1>Mi wishlist</h1>

<?php if (!$items): ?>
    <p class="empty-state">Todavía no agregaste productos a tu wishlist.
        <br><a href="<?= url('/productos/index.php') ?>">Ver productos</a>
    </p>
<?php else: ?>
<div class="grid-products">
    <?php foreach ($items as $p): ?>
        <div class="product-card">
            <h3><?= h($p['nombre']) ?></h3>
            <p class="muted"><?= h($p['marca']) ?></p>
            <p class="price">$<?= number_format((float)$p['precio'], 2) ?></p>
            <div class="actions" style="margin-top:10px;">
                <form method="post" action="<?= url('/wishlist/remove.php') ?>">
                    <input type="hidden" name="id_producto" value="<?= (int)$p['id'] ?>">
                    <button class="btn btn-sm btn-danger" type="submit">Quitar</button>
                </form>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
