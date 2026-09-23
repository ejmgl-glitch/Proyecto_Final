<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

if (isLoggedIn()) {
    redirect(url('/index.php'));
}

$errors = [];
$old = ['nombre' => '', 'correo' => '', 'telefono' => '', 'direccion' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['nombre']    = trim($_POST['nombre'] ?? '');
    $old['correo']    = trim($_POST['correo'] ?? '');
    $old['telefono']  = trim($_POST['telefono'] ?? '');
    $old['direccion'] = trim($_POST['direccion'] ?? '');
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if ($old['nombre'] === '') $errors[] = 'El nombre es obligatorio.';
    if (!isValidEmail($old['correo'])) $errors[] = 'Correo inválido.';
    if (strlen($password) < 6) $errors[] = 'La contraseña debe tener al menos 6 caracteres.';
    if ($password !== $password2) $errors[] = 'Las contraseñas no coinciden.';

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM usuario WHERE correo = ?');
        $stmt->execute([$old['correo']]);
        if ($stmt->fetch()) {
            $errors[] = 'Ya existe una cuenta con ese correo.';
        }
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            'INSERT INTO usuario (nombre, correo, password, telefono, direccion, tipo_usuario)
             VALUES (?, ?, ?, ?, ?, "cliente")'
        );
        $stmt->execute([$old['nombre'], $old['correo'], $hash, $old['telefono'], $old['direccion']]);
        setFlash('ok', 'Cuenta creada correctamente. Ya puedes iniciar sesión.');
        redirect(url('/auth/login.php'));
    }
}

$pageTitle = 'Crear cuenta | Paso Chilero';
require __DIR__ . '/../includes/header.php';
?>

<!-- Contenedor centrado vertical y horizontalmente en pantalla -->
<div style="min-height: calc(100vh - 170px); display: flex; align-items: center; justify-content: center; padding: 40px 15px; box-sizing: border-box;">
    <div class="card" style="width: 100%; max-width: 500px; margin: 0; padding: 36px 30px; border-radius: 14px; box-shadow: 0 4px 20px rgba(0,0,0,0.06); background: #ffffff; border: 1px solid var(--border);">

        <div style="text-align: center; margin-bottom: 22px;">
            <h1 style="margin: 0 0 8px 0; font-size: 1.85rem; font-weight: 800; color: #1e293b;">Crear cuenta</h1>
            <p class="muted" style="margin: 0; font-size: 0.92rem;">Regístrate como cliente para realizar pedidos en Paso Chilero</p>
        </div>

        <?php if (!empty($errors)): ?>
            <?php foreach ($errors as $e): ?>
                <div class="flash flash-error" style="margin-bottom: 16px;"><?= h($e) ?></div>
            <?php endforeach; ?>
        <?php endif; ?>

        <form method="post" class="form-grid" style="max-width: 100%; gap: 14px;">
            <div>
                <label style="font-weight: 600; color: #334155; margin-bottom: 4px; display: block;">Nombre completo</label>
                <input 
                    type="text" 
                    name="nombre" 
                    value="<?= h($old['nombre']) ?>" 
                    required 
                    placeholder="Tu nombre y apellido"
                    style="width: 100%; box-sizing: border-box; padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border);"
                >
            </div>

            <div>
                <label style="font-weight: 600; color: #334155; margin-bottom: 4px; display: block;">Correo electrónico</label>
                <input 
                    type="email" 
                    name="correo" 
                    value="<?= h($old['correo']) ?>" 
                    required 
                    placeholder="correo@ejemplo.com"
                    style="width: 100%; box-sizing: border-box; padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border);"
                >
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div>
                    <label style="font-weight: 600; color: #334155; margin-bottom: 4px; display: block;">Teléfono</label>
                    <input 
                        type="text" 
                        name="telefono" 
                        value="<?= h($old['telefono']) ?>" 
                        placeholder="55551234"
                        style="width: 100%; box-sizing: border-box; padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border);"
                    >
                </div>
                <div>
                    <label style="font-weight: 600; color: #334155; margin-bottom: 4px; display: block;">Dirección</label>
                    <input 
                        type="text" 
                        name="direccion" 
                        value="<?= h($old['direccion']) ?>" 
                        placeholder="Ciudad o Zona"
                        style="width: 100%; box-sizing: border-box; padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border);"
                    >
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                <div>
                    <label style="font-weight: 600; color: #334155; margin-bottom: 4px; display: block;">Contraseña</label>
                    <input 
                        type="password" 
                        name="password" 
                        required 
                        minlength="6" 
                        placeholder="Mínimo 6 caracteres"
                        style="width: 100%; box-sizing: border-box; padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border);"
                    >
                </div>
                <div>
                    <label style="font-weight: 600; color: #334155; margin-bottom: 4px; display: block;">Confirmar contraseña</label>
                    <input 
                        type="password" 
                        name="password2" 
                        required 
                        minlength="6" 
                        placeholder="Repite tu contraseña"
                        style="width: 100%; box-sizing: border-box; padding: 10px 12px; border-radius: 8px; border: 1px solid var(--border);"
                    >
                </div>
            </div>

            <div style="margin-top: 10px;">
                <button class="btn" type="submit" style="width: 100%; padding: 12px; font-size: 1rem; font-weight: 700; border-radius: 8px;">
                    Registrarme
                </button>
            </div>
        </form>

        <div style="margin-top: 22px; padding-top: 16px; border-top: 1px solid var(--border); text-align: center;">
            <p class="muted" style="margin: 0; font-size: 0.92rem;">
                ¿Ya tienes una cuenta? 
                <a href="<?= url('/auth/login.php') ?>" style="color: var(--primary); font-weight: 700; text-decoration: none;">
                    Iniciar sesión
                </a>
            </p>
        </div>

    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>