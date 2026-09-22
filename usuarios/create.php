<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole(['admin']);

$errors = [];
$old = ['nombre' => '', 'correo' => '', 'telefono' => '', 'direccion' => '', 'tipo_usuario' => 'cliente'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['nombre']       = trim($_POST['nombre'] ?? '');
    $old['correo']       = trim($_POST['correo'] ?? '');
    $old['telefono']     = trim($_POST['telefono'] ?? '');
    $old['direccion']    = trim($_POST['direccion'] ?? '');
    $old['tipo_usuario'] = $_POST['tipo_usuario'] ?? 'cliente';
    $password = $_POST['password'] ?? '';

    $rolesValidos = ['cliente', 'trabajador', 'admin'];

    if ($old['nombre'] === '') $errors[] = 'El nombre es obligatorio.';
    if (!isValidEmail($old['correo'])) $errors[] = 'Correo inválido.';
    if (strlen($password) < 6) $errors[] = 'La contraseña debe tener al menos 6 caracteres.';
    if (!in_array($old['tipo_usuario'], $rolesValidos, true)) $errors[] = 'Rol inválido.';

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM usuario WHERE correo = ?');
        $stmt->execute([$old['correo']]);
        if ($stmt->fetch()) $errors[] = 'Ya existe un usuario con ese correo.';
    }

    if (!$errors) {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare(
            'INSERT INTO usuario (nombre, correo, password, telefono, direccion, tipo_usuario)
             VALUES (?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([$old['nombre'], $old['correo'], $hash, $old['telefono'], $old['direccion'], $old['tipo_usuario']]);
        setFlash('ok', 'Usuario creado correctamente.');
        redirect(url('/usuarios/index.php'));
    }
}

$pageTitle = 'Nuevo usuario';
require __DIR__ . '/../includes/header.php';
?>
<div class="card" style="max-width:480px;">
    <h1>Nuevo usuario</h1>

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
            <label>Rol</label>
            <select name="tipo_usuario">
                <option value="cliente"    <?= $old['tipo_usuario']==='cliente'?'selected':'' ?>>Cliente</option>
                <option value="trabajador" <?= $old['tipo_usuario']==='trabajador'?'selected':'' ?>>Trabajador</option>
                <option value="admin"      <?= $old['tipo_usuario']==='admin'?'selected':'' ?>>Admin</option>
            </select>
        </div>
        <div class="actions">
            <button class="btn" type="submit">Crear</button>
            <a class="btn btn-secondary" href="<?= url('/usuarios/index.php') ?>">Cancelar</a>
        </div>
    </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
