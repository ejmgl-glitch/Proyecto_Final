<?php
// pedidos/cambiar_estado.php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Solo admin y trabajador tienen permiso para modificar estados
requireRole(['admin', 'trabajador']);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pedidoId = (int)($_POST['pedido_id'] ?? 0);
    $nuevoEstado = trim($_POST['estado'] ?? '');

    $estadosPermitidos = ['realizado', 'enviado', 'entregado'];

    if ($pedidoId > 0 && in_array($nuevoEstado, $estadosPermitidos, true)) {
        $stmt = $pdo->prepare('UPDATE pedido SET estado = ? WHERE id = ?');
        $stmt->execute([$nuevoEstado, $pedidoId]);
        setFlash('ok', "El estado del pedido #{$pedidoId} se actualizó a: {$nuevoEstado}.");
    } else {
        setFlash('error', 'Estado no válido o pedido inexistente.');
    }
}

redirect(url('/pedidos/index.php'));