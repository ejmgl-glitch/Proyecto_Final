<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/auth.php';
require __DIR__ . '/includes/functions.php';

$pageTitle = 'Inicio';
require __DIR__ . '/includes/header.php';
?>
<div class="card">
    <?php if (isLoggedIn()): ?>
        <h1>Hola, <?= h($_SESSION['user']['nombre']) ?> 👋</h1>
        <p class="muted">Rol: <span class="badge"><?= h(currentRole()) ?></span></p>
    <?php else: ?>
        <h1>Bienvenido a Chilero</h1>
        <p class="muted">Inicia sesión o crea una cuenta para comprar, guardar favoritos y dejar reseñas.</p>
    <?php endif; ?>
</div>

<div class="card">
    <h2>Accesos rápidos</h2>
    <div class="actions">
        <a class="btn" href="<?= url('/productos/index.php') ?>">Ver productos</a>
        <?php if (isLoggedIn()): ?>
            <a class="btn btn-secondary" href="<?= url('/wishlist/index.php') ?>">Mi wishlist</a>
            <?php if (hasRole(['admin','trabajador'])): ?>
                <a class="btn btn-secondary" href="<?= url('/usuarios/index.php') ?>">Gestión de usuarios</a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <h2>Próximamente</h2>
    <p class="muted">Página pública tipo catálogo (index) y carrito de compras funcional.</p>
</div>
<?php require __DIR__ . '/includes/footer.php'; ?>
