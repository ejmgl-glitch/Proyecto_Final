<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$puedeEditar = hasRole(['admin', 'trabajador']);

$stmt = $pdo->query(
    'SELECT p.*, c.nombre AS categoria_nombre
     FROM producto p
     LEFT JOIN categoria c ON c.id = p.id_categoria
     ORDER BY p.id DESC'
);
$productos = $stmt->fetchAll();

$pageTitle = 'Productos';
require __DIR__ . '/../includes/header.php';
?>
<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:16px;">
    <h1>Productos</h1>
    <?php if ($puedeEditar): ?>
        <a class="btn" href="<?= url('/productos/create.php') ?>">+ Nuevo producto</a>
    <?php endif; ?>
</div>

<?php if (!$productos): ?>
    <p class="empty-state">Aún no hay productos cargados.</p>
<?php else: ?>
<div class="grid-products">
    <?php foreach ($productos as $p): ?>
        <div class="product-card">
            <?php if (!empty($p['imagen'])): ?>
                <img src="<?= h($p['imagen']) ?>" alt="<?= h($p['nombre']) ?>" class="product-thumb">
            <?php endif; ?>
            <h3><?= h($p['nombre']) ?></h3>
            <p class="muted"><?= h($p['marca']) ?> · <?= h($p['categoria_nombre'] ?? 'Sin categoría') ?></p>
            <p><?= h($p['descripcion']) ?></p>
            <p class="price">Q <?= number_format((float)$p['precio'], 2) ?></p>
            <p class="muted">Color: <?= h($p['color']) ?> · Género: <?= h($p['genero']) ?></p>
            <div class="actions" style="margin-top:10px;">
                <a class="btn btn-sm btn-secondary" href="<?= url('/reviews/index.php?id_producto=' . (int)$p['id']) ?>">Reseñas</a>
                <?php if (isLoggedIn()): ?>
                    <a class="btn btn-sm" href="<?= url('/wishlist/toggle.php?id_producto=' . (int)$p['id']) ?>">♡ Wishlist</a>
                <?php endif; ?>
                <?php if ($puedeEditar): ?>
                    <a class="btn btn-sm" href="<?= url('/productos/edit.php?id=' . (int)$p['id']) ?>">Editar</a>
                    <form class="form-inline" method="post" action="<?= url('/productos/delete.php') ?>" onsubmit="return confirm('¿Eliminar este producto?');">
                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                        <button class="btn btn-sm btn-danger" type="submit">Eliminar</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>
<?php require __DIR__ . '/../includes/footer.php'; ?>
