<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();
$userId = (int)$_SESSION['user']['id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idProducto = (int)($_POST['id_producto'] ?? 0);
    $stmt = $pdo->prepare('DELETE FROM wishlist WHERE id_usuario = ? AND id_producto = ?');
    $stmt->execute([$userId, $idProducto]);
    setFlash('ok', 'Producto quitado de tu wishlist.');
}

redirect(url('/wishlist/index.php'));
