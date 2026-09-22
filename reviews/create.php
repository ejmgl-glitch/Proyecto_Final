<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId       = (int)$_SESSION['user']['id'];
    $idProducto   = (int)($_POST['id_producto'] ?? 0);
    $calificacion = (int)($_POST['calificacion'] ?? 0);
    $comentario   = trim($_POST['comentario'] ?? '');

    $validas = [1, 2, 3, 4, 5];

    if ($idProducto > 0 && in_array($calificacion, $validas, true)) {
        // Evitar reseñas duplicadas del mismo usuario para el mismo producto
        $stmt = $pdo->prepare('SELECT id FROM reviews WHERE id_usuario = ? AND id_producto = ?');
        $stmt->execute([$userId, $idProducto]);
        if (!$stmt->fetch()) {
            $stmt = $pdo->prepare(
                'INSERT INTO reviews (id_usuario, id_producto, calificacion, comentario, fecha)
                 VALUES (?, ?, ?, ?, CURDATE())'
            );
            $stmt->execute([$userId, $idProducto, $calificacion, $comentario]);
            setFlash('ok', 'Reseña publicada. ¡Gracias!');
        } else {
            setFlash('error', 'Ya dejaste una reseña para este producto.');
        }
    }

    redirect(url('/reviews/index.php?id_producto=' . $idProducto));
}

redirect(url('/productos/index.php'));
