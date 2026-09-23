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
        $errors[] = 'Completa tu correo y contraseña.';
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

$pageTitle = 'Iniciar sesión | Paso Chilero';
require __DIR__ . '/../includes/header.php';
?>

<!-- Contenedor centrado vertical y horizontalmente en pantalla -->
<div style="min-height: calc(100vh - 170px); display: flex; align-items: center; justify-content: center; padding: 40px 15px; box-sizing: border-box;">
    <div class="card" style="width: 100%; max-width: 440px; margin: 0; padding: 36px 30px; border-radius: 14px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); background: #ffffff; border: 1px solid var(--border);">
        
        <div style="text-align: center; margin-bottom: 24px;">
            <h1 style="margin: 0 0 8px 0; font-size: 1.85rem; font-weight: 800; color: #1e293b;">Iniciar sesión</h1>
            <p class="muted" style="margin: 0; font-size: 0.92rem;">Ingresa a tu cuenta para continuar en Paso Chilero</p>
        </div>

        <?php if (isset($_SESSION['flash'])): ?>
            <div class="flash flash-<?= h($_SESSION['flash']['type']) ?>" style="margin-bottom: 18px;">
                <?= h($_SESSION['flash']['msg']) ?>
            </div>
            <?php unset($_SESSION['flash']); ?>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $e): ?>
                <div class="flash flash-error" style="margin-bottom: 18px;"><?= h($e) ?></div>
            <?php endforeach; ?>
        <?php endif; ?>

        <form method="post" class="form-grid" style="max-width: 100%; gap: 18px;">
            <div>
                <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">Correo electrónico</label>
                <input 
                    type="email" 
                    name="correo" 
                    value="<?= h($correo) ?>" 
                    required 
                    autofocus 
                    placeholder="ejemplo@correo.com"
                    style="width: 100%; box-sizing: border-box; padding: 11px 12px; border-radius: 8px; border: 1px solid var(--border);"
                >
            </div>

            <div>
                <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">Contraseña</label>
                <input 
                    type="password" 
                    name="password" 
                    required 
                    placeholder="••••••••"
                    style="width: 100%; box-sizing: border-box; padding: 11px 12px; border-radius: 8px; border: 1px solid var(--border);"
                >
            </div>

            <div style="margin-top: 6px;">
                <button class="btn" type="submit" style="width: 100%; padding: 12px; font-size: 1rem; font-weight: 700; border-radius: 8px;">
                    Ingresar
                </button>
            </div>
        </form>

        <div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--border); text-align: center; display: flex; flex-direction: column; gap: 10px;">
            <p class="muted" style="margin: 0; font-size: 0.92rem;">
                ¿Aún no tienes cuenta? 
                <a href="<?= url('/auth/register.php') ?>" style="color: var(--primary); font-weight: 700; text-decoration: none;">
                    Crear cuenta
                </a>
            </p>
            <p style="margin: 0; font-size: 0.92rem;">
                <a href="<?= url('/auth/recuperar.php') ?>" style="color: var(--primary); font-weight: 700; text-decoration: none;">
                    Recuperar contraseña
                </a>
            </p>
        </div>

    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>