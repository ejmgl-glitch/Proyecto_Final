<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$user_session = null;
if (isset($_SESSION['user'])) {
    $user_session = $_SESSION['user'];
} elseif (isset($_SESSION['usuario'])) {
    $user_session = $_SESSION['usuario'];
} elseif (isset($_SESSION['user_id'])) {
    $user_session = [
        'id' => $_SESSION['user_id'],
        'nombre' => $_SESSION['nombre'] ?? $_SESSION['username'] ?? 'Usuario',
        'rol' => $_SESSION['rol'] ?? $_SESSION['role'] ?? 'cliente'
    ];
}

$base_url = defined('BASE_URL') ? BASE_URL : '/';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $page_title ?? 'Mi Tienda Online'; ?></title>
    <link rel="stylesheet" href="<?php echo rtrim($base_url, '/'); ?>/assets/style.css">
    <script src="https://unpkg.com/react@18/umd/react.development.js" crossorigin></script>
    <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js" crossorigin></script>
    <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
</head>
<body>
    <div 
        id="react-header" 
        data-user='<?php echo htmlspecialchars(json_encode($user_session), ENT_QUOTES, 'UTF-8'); ?>'
        data-baseurl="<?php echo htmlspecialchars($base_url, ENT_QUOTES, 'UTF-8'); ?>"
    ></div>

    <script 
        type="text/babel" 
        src="<?php echo rtrim($base_url, '/'); ?>/assets/js/components/Header.jsx">
    </script>

    <main class="main-content">