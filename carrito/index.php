<?php
// carrito/index.php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$page_title = 'Mi Carrito de Compras';
require __DIR__ . '/../includes/header.php';
?>

<div class="container" style="max-width: 1000px; margin: 30px auto; padding: 0 15px;">
    <!-- Contenedor Carrito -->
    <div 
        id="react-carrito"
        data-user='<?= htmlspecialchars(json_encode($_SESSION['user'] ?? null), ENT_QUOTES, 'UTF-8') ?>'
        data-baseurl="<?= htmlspecialchars(BASE_URL, ENT_QUOTES, 'UTF-8') ?>"
    ></div>
</div>

<script 
    type="text/babel" 
    src="<?= url('/assets/js/components/Carrito.jsx') ?>">
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>