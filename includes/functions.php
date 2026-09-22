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
