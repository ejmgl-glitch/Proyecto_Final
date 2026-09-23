<?php
// reviews/index.php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$idProducto = (int)($_GET['id_producto'] ?? 0);
$producto = null;

if ($idProducto > 0) {
    $stmt = $pdo->prepare('SELECT * FROM producto WHERE id = ?');
    $stmt->execute([$idProducto]);
    $producto = $stmt->fetch();
}

$userId = $_SESSION['user']['id'] ?? null;

// ========================================================
// CASO A: Reseñas de un producto específico (?id_producto=X)
// ========================================================
if ($producto) {
    $stmt = $pdo->prepare(
        'SELECT r.*, u.nombre AS usuario_nombre 
         FROM reviews r 
         JOIN usuario u ON u.id = r.id_usuario 
         WHERE r.id_producto = ? 
         ORDER BY r.fecha DESC, r.id DESC'
    );
    $stmt->execute([$idProducto]);
    $reviews = $stmt->fetchAll();

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
    <div class="container" style="max-width: 900px; margin: 30px auto; padding: 0 15px;">
        <div class="card" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
            <div>
                <h1 style="margin:0 0 6px 0;">Reseñas: <?= h($producto['nombre']) ?></h1>
                <p class="muted" style="margin:0;"><?= h($producto['marca']) ?></p>
            </div>
            <a class="btn btn-secondary btn-sm" href="<?= url('/productos/index.php') ?>">&larr; Volver a productos</a>
        </div>

        <?php if (isLoggedIn() && !$miReview): ?>
            <div class="card">
                <h2>Dejar una reseña</h2>
                <form method="post" action="<?= url('/reviews/create.php') ?>" class="form-grid">
                    <input type="hidden" name="id_producto" value="<?= (int)$producto['id'] ?>">
                    <div>
                        <label>Calificación</label>
                        <select name="calificacion" required>
                            <option value="5" selected>★★★★★ (5)</option>
                            <option value="4">★★★★☆ (4)</option>
                            <option value="3">★★★☆☆ (3)</option>
                            <option value="2">★★☆☆☆ (2)</option>
                            <option value="1">★☆☆☆☆ (1)</option>
                        </select>
                    </div>
                    <div>
                        <label>Comentario</label>
                        <textarea name="comentario" maxlength="255" placeholder="¿Qué te pareció este calzado?"></textarea>
                    </div>
                    <div class="actions">
                        <button class="btn" type="submit">Publicar reseña</button>
                    </div>
                </form>
            </div>
        <?php elseif (!isLoggedIn()): ?>
            <div class="card">
                <p><a href="<?= url('/auth/login.php') ?>">Inicia sesión</a> para dejar tu reseña.</p>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2>Todas las opiniones sobre este calzado (<?= count($reviews) ?>)</h2>
            <?php if (!$reviews): ?>
                <p class="empty-state">Este producto todavía no tiene reseñas. ¡Sé el primero!</p>
            <?php else: ?>
                <?php foreach ($reviews as $r): ?>
                    <div style="border-bottom:1px solid var(--border); padding:14px 0;">
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <strong><?= h($r['usuario_nombre']) ?></strong>
                            <span class="muted" style="font-size:0.85rem;"><?= h($r['fecha']) ?></span>
                        </div>
                        <div class="stars"><?= str_repeat('★', (int)$r['calificacion']) ?><span style="color:#cbd5e1;"><?= str_repeat('★', 5 - (int)$r['calificacion']) ?></span> (<?= (int)$r['calificacion'] ?>/5)</div>
                        <p style="margin:8px 0; color:var(--text);"><?= h($r['comentario']) ?></p>
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
            <?php endif; ?>
        </div>
    </div>

<?php
// ========================================================
// CASO B: Reseñas Generales del Header (/reviews/index.php)
// ========================================================
} else {
    $stmt = $pdo->query(
        'SELECT r.*, u.nombre AS usuario_nombre, p.nombre AS producto_nombre, p.id AS producto_id, p.marca AS producto_marca, p.imagen AS producto_imagen
         FROM reviews r
         JOIN usuario u ON u.id = r.id_usuario
         JOIN producto p ON p.id = r.id_producto
         ORDER BY r.fecha DESC, r.id DESC'
    );
    $reviews = $stmt->fetchAll();

    $pageTitle = 'Reseñas de Clientes | Paso Chilero';
    require __DIR__ . '/../includes/header.php';
    ?>
    <div class="container" style="max-width: 960px; margin: 30px auto; padding: 0 15px;">
        <div class="card" style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; gap:10px;">
            <div>
                <h1 style="margin: 0 0 6px 0;">Reseñas de la Comunidad</h1>
                <p class="muted" style="margin:0;">Comentarios y experiencias verificadas de clientes en Guatemala.</p>
            </div>
            <a href="<?= url('/productos/index.php') ?>" class="btn btn-secondary btn-sm">&larr; Explorar Productos</a>
        </div>

        <div class="card">
            <h2>Todas las reseñas publicadas (<?= count($reviews) ?>)</h2>
            <?php if (empty($reviews)): ?>
                <div class="empty-state">
                    <p>Aún no hay reseñas registradas en la tienda.</p>
                    <a href="<?= url('/productos/index.php') ?>" class="btn">Comprar y valorar</a>
                </div>
            <?php else: ?>
                <div style="display:flex; flex-direction:column; gap:16px;">
                    <?php foreach ($reviews as $r): ?>
                        <div style="border-bottom:1px solid var(--border); padding-bottom:16px;">
                            <div style="display:flex; justify-content:space-between; align-items:flex-start; flex-wrap:wrap; gap:10px;">
                                <div>
                                    <strong style="font-size:1rem;"><?= h($r['usuario_nombre']) ?></strong>
                                    <span class="muted"> opinó sobre </span>
                                    <a href="<?= url('/reviews/index.php?id_producto=' . (int)$r['producto_id']) ?>" style="color:var(--primary); font-weight:600; text-decoration:none;">
                                        <?= h($r['producto_nombre']) ?> (<?= h($r['producto_marca']) ?>)
                                    </a>
                                </div>
                                <span class="muted" style="font-size:0.85rem;"><?= h($r['fecha']) ?></span>
                            </div>
                            <div class="stars" style="margin:6px 0;">
                                <?= str_repeat('★', (int)$r['calificacion']) ?><span style="color:#cbd5e1;"><?= str_repeat('★', 5 - (int)$r['calificacion']) ?></span>
                                <span style="font-size:0.85rem; color:var(--muted); font-weight:600;">(<?= (int)$r['calificacion'] ?>/5)</span>
                            </div>
                            <p style="margin:6px 0; color:var(--text); line-height:1.5;"><?= h($r['comentario']) ?></p>

                            <?php if ($userId !== null && ((int)$r['id_usuario'] === (int)$userId || hasRole(['admin']))): ?>
                                <div class="actions" style="margin-top:10px;">
                                    <a class="btn btn-sm" href="<?= url('/reviews/edit.php?id=' . (int)$r['id']) ?>">Editar</a>
                                    <form class="form-inline" method="post" action="<?= url('/reviews/delete.php') ?>" onsubmit="return confirm('¿Eliminar esta reseña?');">
                                        <input type="hidden" name="id" value="<?= (int)$r['id'] ?>">
                                        <input type="hidden" name="id_producto" value="<?= (int)$r['producto_id'] ?>">
                                        <button class="btn btn-sm btn-danger" type="submit">Eliminar</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php
}
require __DIR__ . '/../includes/footer.php';
?>