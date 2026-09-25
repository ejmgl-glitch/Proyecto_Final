<?php
// wishlist/index.php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';
requireLogin();

$userId = (int)$_SESSION['user']['id'];

// Consultamos los productos guardados por el usuario
$stmt = $pdo->prepare(
    'SELECT w.id AS wishlist_id, p.*, c.nombre AS categoria_nombre 
     FROM wishlist w
     JOIN producto p ON p.id = w.id_producto
     LEFT JOIN categoria c ON c.id = p.id_categoria
     WHERE w.id_usuario = ?
     ORDER BY w.id DESC'
);
$stmt->execute([$userId]);
$items = $stmt->fetchAll();

$pageTitle = 'Mi Wishlist | Paso Chilero';
require __DIR__ . '/../includes/header.php';
?>

<div class="wishlist-container">
    <!-- Mensajes de sesión / alertas -->
    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash flash-<?= h($_SESSION['flash']['type']) ?>">
            <?= h($_SESSION['flash']['msg']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <!-- Encabezado de la página -->
    <div class="page-header-box">
        <div>
            <span class="eyebrow">Tu colección guardada</span>
            <h1>Mi Lista de Deseos (<?= count($items) ?>)</h1>
            <p class="muted" style="margin: 6px 0 0 0;">
                Tus pares favoritos guardados para cuando estés listo para dar el paso.
            </p>
        </div>
        <a href="<?= url('/productos/index.php') ?>" class="btn btn-secondary btn-sm">
            &larr; Explorar más productos
        </a>
    </div>

    <?php if (!$items): ?>
        <!-- Estado cuando no hay productos guardados -->
        <div class="empty-box-card">
            <div class="empty-box-icon">❤️</div>
            <h2 style="color: #1e293b; margin: 0 0 8px 0; font-size: 1.5rem;">Tu wishlist está vacía</h2>
            <p class="muted" style="max-width: 420px; margin: 0 auto 20px auto; line-height: 1.5;">
                No has guardado ningún producto todavía. Explora nuestro catálogo y presiona el botón de Wishlist en el calzado que más te guste.
            </p>
            <a href="<?= url('/productos/index.php') ?>" class="btn" style="padding: 11px 22px; font-weight: 600;">
                Ir a ver zapatos
            </a>
        </div>
    <?php else: ?>
        <!-- Grilla responsiva de productos favoritos -->
        <div class="grid-products">
            <?php foreach ($items as $p): ?>
                <div class="product-card">
                    <div>
                        <div class="product-image-wrap">
                            <?php if (!empty($p['imagen'])): ?>
                                <img src="<?= h($p['imagen']) ?>" alt="<?= h($p['nombre']) ?>" class="product-thumb">
                            <?php else: ?>
                                <div style="font-size: 2.5rem;">👟</div>
                            <?php endif; ?>
                        </div>

                        <div class="product-info">
                            <span class="product-meta-badge"><?= h($p['categoria_nombre'] ?? 'Calzado') ?></span>
                            <h3><?= h($p['nombre']) ?></h3>
                            <p class="muted"><?= h($p['marca']) ?> <?= !empty($p['color']) ? '• ' . h($p['color']) : '' ?></p>
                        </div>
                    </div>

                    <div>
                        <p class="price">Q <?= number_format((float)$p['precio'], 2) ?></p>

                        <div class="actions">
                            <!-- Lleva directamente a seleccionar talla en el catálogo -->
                            <a class="btn btn-sm" href="<?= url('/productos/index.php#producto-' . (int)$p['id']) ?>">
                                Elegir talla
                            </a>

                            <!-- Formulario para quitar de la wishlist -->
                            <form method="post" action="<?= url('/wishlist/remove.php') ?>" style="margin: 0; display: inline-flex;">
                                <input type="hidden" name="id_producto" value="<?= (int)$p['id'] ?>">
                                <button class="btn btn-sm btn-danger" type="submit" title="Quitar de mi lista">
                                    Quitar
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>