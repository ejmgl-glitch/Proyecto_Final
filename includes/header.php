<?php
$user = currentUser();
$role = currentRole();
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? h($pageTitle) . ' · ' : '' ?>Chilero</title>
<link rel="stylesheet" href="<?= url('/assets/style.css') ?>">
</head>
<body>
<header class="topbar">
    <a href="<?= url('/index.php') ?>" class="brand">Chilero</a>
    <nav>
        <a href="<?= url('/productos/index.php') ?>">Productos</a>
        <?php if (isLoggedIn()): ?>
            <a href="<?= url('/wishlist/index.php') ?>">Mi wishlist</a>
            <?php if (hasRole(['admin','trabajador'])): ?>
                <a href="<?= url('/usuarios/index.php') ?>">Usuarios</a>
            <?php endif; ?>
            <a href="<?= url('/usuarios/edit.php?id=' . (int)$user['id']) ?>">Mi perfil</a>
            <span class="role-badge role-<?= h($role) ?>"><?= h($role) ?></span>
            <a href="<?= url('/auth/logout.php') ?>">Salir</a>
        <?php else: ?>
            <a href="<?= url('/auth/login.php') ?>">Ingresar</a>
            <a href="<?= url('/auth/register.php') ?>">Crear cuenta</a>
        <?php endif; ?>
    </nav>
</header>
<main class="container">
<?php if (!empty($_SESSION['flash'])): ?>
    <div class="flash flash-<?= h($_SESSION['flash']['type']) ?>"><?= h($_SESSION['flash']['msg']) ?></div>
    <?php unset($_SESSION['flash']); ?>
<?php endif; ?>
