<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/functions.php';

requireLogin();
$userId = (int)$_SESSION['user']['id'];
$idProducto = (int)($_GET['id_producto'] ?? 0);

if ($idProducto > 0) {
    $stmt = $pdo->prepare('SELECT id FROM wishlist WHERE id_usuario = ? AND id_producto = ?');
    $stmt->execute([$userId, $idProducto]);
    $existing = $stmt->fetch();

    if ($existing) {
        $stmt = $pdo->prepare('DELETE FROM wishlist WHERE id = ?');
        $stmt->execute([$existing['id']]);
        setFlash('ok', 'Producto quitado de tu wishlist.');
    } else {
        $stmt = $pdo->prepare('INSERT INTO wishlist (id_usuario, id_producto) VALUES (?, ?)');
        $stmt->execute([$userId, $idProducto]);
        setFlash('ok', 'Producto agregado a tu wishlist.');
    }
}

redirect($_SERVER['HTTP_REFERER'] ?? url('/productos/index.php'));
