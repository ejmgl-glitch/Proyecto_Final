<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $idProducto = (int)($_POST['id_producto'] ?? 0);
    $userId = (int)$_SESSION['user']['id'];

    $stmt = $pdo->prepare('SELECT * FROM reviews WHERE id = ?');
    $stmt->execute([$id]);
    $review = $stmt->fetch();

    if ($review && ((int)$review['id_usuario'] === $userId || hasRole(['admin']))) {
        $stmt = $pdo->prepare('DELETE FROM reviews WHERE id = ?');
        $stmt->execute([$id]);
        setFlash('ok', 'Reseña eliminada.');
    } else {
        setFlash('error', 'No tienes permiso para eliminar esta reseña.');
    }

    redirect(url('/reviews/index.php?id_producto=' . $idProducto));
}

redirect(url('/productos/index.php'));
