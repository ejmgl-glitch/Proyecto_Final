<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole(['admin', 'trabajador']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $stmt = $pdo->prepare('DELETE FROM producto WHERE id = ?');
    $stmt->execute([$id]);
    setFlash('ok', 'Producto eliminado.');
}

redirect(url('/productos/index.php'));
