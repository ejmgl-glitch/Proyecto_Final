<?php


$basePath = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? '/')), '/');
$projectRoot = '/chileroPasos';


const BASE_URL = '/chileroPasos';

function url(string $path = '/'): string {
    return BASE_URL . '/' . ltrim($path, '/');
}

function setFlash(string $type, string $msg): void {
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function redirect(string $url): void {
    header('Location: ' . $url);
    exit;
}

function isValidEmail(string $email): bool {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

/**
 * Tallas US que maneja la tienda (5 a 13, en medias tallas).
 */
function tallasDisponibles(): array {
    $tallas = [];
    for ($t = 5; $t <= 13; $t += 0.5) {
        $tallas[] = (fmod($t, 1) === 0.0) ? (string)(int)$t : (string)$t;
    }
    return $tallas;
}

/**
 * Genera un código único para una variante (producto + talla), tipo AIR1-T85-8F2K.
 */
function generarCodigoVariante(PDO $pdo, string $nombreProducto, int $idProducto, string $talla): string {
    $prefijo = strtoupper(preg_replace('/[^A-Za-z]/', '', $nombreProducto));
    $prefijo = substr($prefijo, 0, 3) ?: 'PRD';
    $tallaCod = str_pad(str_replace('.', '', $talla), 2, '0', STR_PAD_LEFT);

    do {
        $rand = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
        $codigo = "{$prefijo}{$idProducto}-T{$tallaCod}-{$rand}";
        $stmt = $pdo->prepare('SELECT 1 FROM variante_producto WHERE codigo_unico = ?');
        $stmt->execute([$codigo]);
    } while ($stmt->fetch());

    return $codigo;
}