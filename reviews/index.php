<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/functions.php';

$idProducto = (int)($_GET['id_producto'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM producto WHERE id = ?');
$stmt->execute([$idProducto]);
$producto = $stmt->fetch();

if (!$producto) {
    http_response_code(404);
    die('Producto no encontrado.');
}

$stmt = $pdo->prepare(
    'SELECT r.*, u.nombre AS usuario_nombre
     FROM reviews r
     JOIN usuario u ON u.id = r.id_usuario
     WHERE r.id_producto = ?
     ORDER BY r.fecha DESC, r.id DESC'
);
$stmt->execute([$idProducto]);
$reviews = $stmt->fetchAll();

$userId = $_SESSION['user']['id'] ?? null;
$miReview = null;
foreach ($reviews as $r) {
    if ($userId !== null && (int)$r['id_usuario'] === (int)$userId) {
        $miReview = $r;
        break;
    }
}

$pageTitle = 'Reseñas de ' . $producto['nombre'];
require __DIR__ . '/../includes/header.php';
?>
<div class="card">
    <h1>Reseñas: <?= h($producto['nombre']) ?></h1>
    <a href="<?= url('/productos/index.php') ?>">&larr; Volver a productos</a>
</div>

<?php if (isLoggedIn() && !$miReview): ?>
    <div class="card">
        <h2>Dejar una reseña</h2>
        <form method="post" action="<?= url('/reviews/create.php') ?>" class="form-grid">
            <input type="hidden" name="id_producto" value="<?= (int)$producto['id'] ?>">
            <div>
                <label>Calificación</label>
                <select name="calificacion" required>
                    <option value="1">⭐ (1)</option>
                    <option value="2">⭐⭐ (2)</option>
                    <option value="3">⭐⭐⭐ (3)</option>
                    <option value="4">⭐⭐⭐⭐ (4)</option>
                    <option value="5" selected>⭐⭐⭐⭐⭐ (5)</option>
                </select>
            </div>
            <div>
                <label>Comentario</label>
                <textarea name="comentario" maxlength="255"></textarea>
            </div>
            <div class="actions">
                <button class="btn" type="submit">Publicar reseña</button>
            </div>
        </form>
    </div>
<?php elseif (!isLoggedIn()): ?>
    <div class="card">
        <p><a href="<?= url('/auth/login.php') ?>">Inicia sesión</a> para dejar una reseña.</p>
    </div>
<?php endif; ?>

<div class="card">
    <h2>Todas las reseñas (<?= count($reviews) ?>)</h2>
    <?php if (!$reviews): ?>
        <p class="empty-state">Este producto todavía no tiene reseñas.</p>
    <?php endif; ?>
    <?php foreach ($reviews as $r): ?>
        <div style="border-bottom:1px solid var(--border); padding:12px 0;">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <strong><?= h($r['usuario_nombre']) ?></strong>
                <span class="muted"><?= h($r['fecha']) ?></span>
            </div>
            <div class="stars"><?= str_repeat('⭐', (int)$r['calificacion']) ?></div>
            <p><?= h($r['comentario']) ?></p>
            <?php if ($userId !== null && ((int)$r['id_usuario'] === (int)$userId || hasRole(['admin']))): ?>
                <div class="actions">
                    <a class="btn btn-sm" href="<?= url('/reviews/edit.php?id=' . (int)$r['id']) ?>">Editar</a>
                    <form class="form-inline" method="post" action="<?= url('/reviews/delete.php') ?>" onsubmit="return confirm('¿Eliminar esta reseña?');">
                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                        <input type="hidden" name="id_producto" value="<?= (int)$idProducto ?>">
                        <button class="btn btn-sm btn-danger" type="submit">Eliminar</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
