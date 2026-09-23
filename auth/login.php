<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) {
    redirect(url('/index.php'));
}

$errors = [];
$correo = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo   = trim($_POST['correo'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($correo === '' || $password === '') {
        $errors[] = 'Completa correo y contraseña.';
    } else {
        $stmt = $pdo->prepare('SELECT * FROM usuario WHERE correo = ?');
        $stmt->execute([$correo]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'Correo o contraseña incorrectos.';
        } else {
            session_regenerate_id(true);
            $_SESSION['user'] = [
                'id'           => $user['id'],
                'nombre'       => $user['nombre'],
                'correo'       => $user['correo'],
                'tipo_usuario' => $user['tipo_usuario'],
            ];
            redirect(url('/index.php'));
        }
    }
}

$pageTitle = 'Iniciar sesión';

// --- AQUÍ ESTÁ EL CAMBIO ---
// Esta variable le indica a header.php que no dibuje la barra de navegación
$hideHeader = true; 

require __DIR__ . '/../includes/header.php';
?>

<div class="card" style="max-width:420px;margin:40px auto;">
    <h1>Iniciar sesión</h1>
    <?php foreach ($errors as $e): ?>
        <div class="flash flash-error"><?= h($e) ?></div>
    <?php endforeach; ?>
    <form method="post" class="form-grid">
        <div>
            <label>Correo</label>
            <input type="email" name="correo" value="<?= h($correo) ?>" required autofocus>
        </div>
        <div>
            <label>Contraseña</label>
            <input type="password" name="password" required>
        </div>
        <div class="actions">
            <button class="btn" type="submit">Ingresar</button>
            <a class="btn btn-secondary" href="<?= url('/auth/register.php') ?>">Crear cuenta</a>
        </div>
    </form>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>