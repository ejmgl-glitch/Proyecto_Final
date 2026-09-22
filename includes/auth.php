<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/functions.php';


function isLoggedIn(): bool {
    return isset($_SESSION['user']) && isset($_SESSION['user']['id']);
}

function currentUser(): ?array {
    return $_SESSION['user'] ?? null;
}


function currentRole(): ?string {
    return $_SESSION['user']['tipo_usuario'] ?? null;
}


function hasRole(array $roles): bool {
    $role = currentRole();
    return $role !== null && in_array($role, $roles, true);
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        redirect(url('/auth/login.php'));
    }
}

function requireRole(array $roles): void {
    requireLogin();
    if (!hasRole($roles)) {
        http_response_code(403);
        echo '<div style="max-width:600px;margin:60px auto;font-family:sans-serif;text-align:center">';
        echo '<h2>Acceso denegado</h2><p>No tienes permisos para ver esta sección.</p>';
        echo '<a href="' . url('/index.php') . '">Volver al inicio</a></div>';
        exit;
    }
}

function h(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
