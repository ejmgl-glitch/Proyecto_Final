<?php

ini_set('display_errors', '0');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$DB_HOST = 'localhost';
$DB_NAME = 'chilero';
$DB_USER = 'root';
$DB_PASS = '';
$DB_CHARSET = 'utf8mb4';

$dsn = "mysql:host={$DB_HOST};dbname={$DB_NAME};charset={$DB_CHARSET}";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (PDOException $e) {
    http_response_code(500);
    die('<h2>Error de conexión a la base de datos</h2><p>Verifica que MySQL esté iniciado y que exista la base de datos <strong>chilero</strong>.</p><p>Importa el archivo <code>sql/chilero.sql</code> desde phpMyAdmin.</p><p><small>Detalle técnico: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</small></p>');
}
