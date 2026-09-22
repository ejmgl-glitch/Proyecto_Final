<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole(['admin']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = (int)($_POST['id'] ?? 0);
    $sessionId = (int)($_SESSION['user']['id'] ?? 0);

    if ($id === $sessionId) {
        setFlash('error', 'No puedes eliminar tu propia cuenta mientras la usas.');
    } else {
        $stmt = $pdo->prepare('DELETE FROM usuario WHERE id = ?');
        $stmt->execute([$id]);
        setFlash('ok', 'Usuario eliminado.');
    }
}

redirect(url('/usuarios/index.php'));
