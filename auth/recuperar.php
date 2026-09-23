<?php
// auth/recuperar.php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) {
    redirect(url('/index.php'));
}

// Opción de reiniciar el proceso
if (isset($_GET['reiniciar'])) {
    unset($_SESSION['recuperar_user_id'], $_SESSION['recuperar_user_nombre'], $_SESSION['recuperar_user_correo']);
    redirect(url('/auth/recuperar.php'));
}

$errors = [];
$correo = '';

// Determinar en qué paso se encuentra el usuario
$enPaso2 = isset($_SESSION['recuperar_user_id']);

// ========================================================
// PROCESAR FORMULARIOS
// ========================================================
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // PASO 1: Validar el correo electrónico ingresado
    if (!$enPaso2) {
        $correo = trim($_POST['correo'] ?? '');

        if ($correo === '') {
            $errors[] = 'Por favor ingresa tu correo electrónico.';
        } elseif (!isValidEmail($correo)) {
            $errors[] = 'El formato del correo ingresado no es válido.';
        } else {
            $stmt = $pdo->prepare('SELECT id, nombre, correo, tipo_usuario FROM usuario WHERE correo = ?');
            $stmt->execute([$correo]);
            $user = $stmt->fetch();

            if (!$user) {
                $errors[] = 'No existe ninguna cuenta asociada a este correo electrónico.';
            } elseif ($user['tipo_usuario'] !== 'cliente') {
                // Validación estricta: Solo para cuentas tipo "cliente"
                $errors[] = 'La recuperación en línea está reservada exclusivamente para cuentas de Clientes. Si eres Empleado o Administrador, solicita el cambio directamente a la administración.';
            } else {
                // Correo válido y es cliente: pasar al Paso 2
                $_SESSION['recuperar_user_id']     = (int)$user['id'];
                $_SESSION['recuperar_user_nombre'] = $user['nombre'];
                $_SESSION['recuperar_user_correo'] = $user['correo'];
                redirect(url('/auth/recuperar.php'));
            }
        }

    // PASO 2: Guardar la nueva contraseña
    } else {
        $password  = $_POST['password'] ?? '';
        $password2 = $_POST['password2'] ?? '';

        if (strlen($password) < 6) {
            $errors[] = 'La nueva contraseña debe tener al menos 6 caracteres.';
        } elseif ($password !== $password2) {
            $errors[] = 'Las contraseñas no coinciden.';
        } else {
            $targetId = (int)$_SESSION['recuperar_user_id'];
            $hash = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $pdo->prepare('UPDATE usuario SET password = ? WHERE id = ? AND tipo_usuario = "cliente"');
            $stmt->execute([$hash, $targetId]);

            // Limpiar variables temporales de recuperación
            unset($_SESSION['recuperar_user_id'], $_SESSION['recuperar_user_nombre'], $_SESSION['recuperar_user_correo']);

            setFlash('ok', '¡Contraseña actualizada con éxito! Ya puedes iniciar sesión con tu nueva contraseña.');
            redirect(url('/auth/login.php'));
        }
    }
}

$pageTitle = 'Recuperar contraseña | Paso Chilero';
require __DIR__ . '/../includes/header.php';
?>

<!-- Contenedor centrado vertical y horizontalmente en pantalla -->
<div style="min-height: calc(100vh - 170px); display: flex; align-items: center; justify-content: center; padding: 40px 15px; box-sizing: border-box;">
    <div class="card" style="width: 100%; max-width: 450px; margin: 0; padding: 36px 30px; border-radius: 14px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); background: #ffffff; border: 1px solid var(--border);">
        
        <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $e): ?>
                <div class="flash flash-error" style="margin-bottom: 18px; font-size: 0.9rem; line-height: 1.45;">
                    <?= h($e) ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>

        <!-- ========================================== -->
        <!-- PASO 1: FORMULARIO PARA INGRESAR CORREO    -->
        <!-- ========================================== -->
        <?php if (!$enPaso2): ?>
            <div style="text-align: center; margin-bottom: 24px;">
                <h1 style="margin: 0 0 8px 0; font-size: 1.85rem; font-weight: 800; color: #1e293b;">Recuperar contraseña</h1>
                <p class="muted" style="margin: 0; font-size: 0.92rem;">Ingresa el correo electrónico de tu cuenta de cliente</p>
            </div>

            <form method="post" class="form-grid" style="max-width: 100%; gap: 18px;">
                <div>
                    <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">Correo electrónico</label>
                    <input 
                        type="email" 
                        name="correo" 
                        value="<?= h($correo) ?>" 
                        required 
                        autofocus 
                        placeholder="tu@correo.com"
                        style="width: 100%; box-sizing: border-box; padding: 11px 12px; border-radius: 8px; border: 1px solid var(--border);"
                    >
                </div>

                <div style="margin-top: 4px;">
                    <button class="btn" type="submit" style="width: 100%; padding: 12px; font-size: 1rem; font-weight: 700; border-radius: 8px;">
                        Recuperar contraseña
                    </button>
                </div>
            </form>

            <div style="margin-top: 24px; padding-top: 18px; border-top: 1px solid var(--border); text-align: center;">
                <a href="<?= url('/auth/login.php') ?>" style="color: var(--muted); font-size: 0.92rem; text-decoration: none; font-weight: 600;">
                    &larr; Volver a Iniciar sesión
                </a>
            </div>

        <!-- ========================================== -->
        <!-- PASO 2: FORMULARIO PARA NUEVA CONTRASEÑA  -->
        <!-- ========================================== -->
        <?php else: ?>
            <div style="text-align: center; margin-bottom: 22px;">
                <h1 style="margin: 0 0 8px 0; font-size: 1.85rem; font-weight: 800; color: #1e293b;">Crear nueva contraseña</h1>
                <p class="muted" style="margin: 0; font-size: 0.92rem;">
                    Hola <strong><?= h($_SESSION['recuperar_user_nombre'] ?? 'Cliente') ?></strong>, ingresa tu nueva contraseña para la cuenta <em><?= h($_SESSION['recuperar_user_correo'] ?? '') ?></em>.
                </p>
            </div>

            <form method="post" class="form-grid" style="max-width: 100%; gap: 16px;">
                <div>
                    <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">Nueva contraseña</label>
                    <input 
                        type="password" 
                        name="password" 
                        required 
                        minlength="6" 
                        autofocus 
                        placeholder="Mínimo 6 caracteres"
                        style="width: 100%; box-sizing: border-box; padding: 11px 12px; border-radius: 8px; border: 1px solid var(--border);"
                    >
                </div>

                <div>
                    <label style="font-weight: 600; color: #334155; margin-bottom: 6px; display: block;">Confirmar nueva contraseña</label>
                    <input 
                        type="password" 
                        name="password2" 
                        required 
                        minlength="6" 
                        placeholder="Repite la nueva contraseña"
                        style="width: 100%; box-sizing: border-box; padding: 11px 12px; border-radius: 8px; border: 1px solid var(--border);"
                    >
                </div>

                <div style="margin-top: 6px;">
                    <button class="btn" type="submit" style="width: 100%; padding: 12px; font-size: 1rem; font-weight: 700; border-radius: 8px;">
                        Guardar nueva contraseña
                    </button>
                </div>
            </form>

            <div style="margin-top: 22px; padding-top: 16px; border-top: 1px solid var(--border); text-align: center;">
                <a href="<?= url('/auth/recuperar.php?reiniciar=1') ?>" style="color: var(--muted); font-size: 0.9rem; text-decoration: none;">
                    &larr; Usar otro correo electrónico
                </a>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>