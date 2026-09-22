<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/functions.php';

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
        // El registro público SIEMPRE crea un usuario tipo "cliente".
        // Crear trabajador/admin sólo lo puede hacer un admin desde /usuarios/create.php
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

$pageTitle = 'Crear cuenta';
require __DIR__ . '/../includes/header.php';
?>
<div class="card" style="max-width:480px;margin:0 auto;">
    <h1>Crear cuenta</h1>
    <p class="muted">El registro público crea cuentas de tipo <strong>cliente</strong>.</p>

    <?php foreach ($errors as $e): ?>
        <div class="flash flash-error"><?= h($e) ?></div>
    <?php endforeach; ?>

    <form method="post" class="form-grid">
        <div>
            <label>Nombre completo</label>
            <input type="text" name="nombre" value="<?= h($old['nombre']) ?>" required>
        </div>
        <div>
            <label>Correo</label>
            <input type="email" name="correo" value="<?= h($old['correo']) ?>" required>
        </div>
        <div>
            <label>Teléfono</label>
            <input type="text" name="telefono" value="<?= h($old['telefono']) ?>">
        </div>
        <div>
            <label>Dirección</label>
            <input type="text" name="direccion" value="<?= h($old['direccion']) ?>">
        </div>
        <div>
            <label>Contraseña</label>
            <input type="password" name="password" required minlength="6">
        </div>
        <div>
            <label>Confirmar contraseña</label>
            <input type="password" name="password2" required minlength="6">
        </div>
        <div class="actions">
            <button class="btn" type="submit">Crear cuenta</button>
            <a class="btn btn-secondary" href="<?= url('/auth/login.php') ?>">Ya tengo cuenta</a>
        </div>
    </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
