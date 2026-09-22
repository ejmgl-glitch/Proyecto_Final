<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$role      = currentRole();
$sessionId = (int)($_SESSION['user']['id'] ?? 0);
$targetId  = (int)($_GET['id'] ?? $_POST['id'] ?? 0);

$isSelf = ($targetId === $sessionId);

if ($role !== 'admin' && !$isSelf) {
    http_response_code(403);
    die('No tienes permiso para editar este usuario.');
}

$stmt = $pdo->prepare('SELECT * FROM usuario WHERE id = ?');
$stmt->execute([$targetId]);
$usuario = $stmt->fetch();

if (!$usuario) {
    http_response_code(404);
    die('Usuario no encontrado.');
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre    = trim($_POST['nombre'] ?? '');
    $correo    = trim($_POST['correo'] ?? '');
    $telefono  = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');
    $password  = $_POST['password'] ?? '';
    $tipoUsuario = ($role === 'admin') ? ($_POST['tipo_usuario'] ?? $usuario['tipo_usuario']) : $usuario['tipo_usuario'];

    if ($nombre === '') $errors[] = 'El nombre es obligatorio.';
    if (!isValidEmail($correo)) $errors[] = 'Correo inválido.';
    if ($password !== '' && strlen($password) < 6) $errors[] = 'La nueva contraseña debe tener al menos 6 caracteres.';

    if (!$errors) {
        $stmt = $pdo->prepare('SELECT id FROM usuario WHERE correo = ? AND id <> ?');
        $stmt->execute([$correo, $targetId]);
        if ($stmt->fetch()) $errors[] = 'Ese correo ya lo usa otro usuario.';
    }

    if (!$errors) {
        if ($password !== '') {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare(
                'UPDATE usuario SET nombre=?, correo=?, telefono=?, direccion=?, tipo_usuario=?, password=? WHERE id=?'
            );
            $stmt->execute([$nombre, $correo, $telefono, $direccion, $tipoUsuario, $hash, $targetId]);
        } else {
            $stmt = $pdo->prepare(
                'UPDATE usuario SET nombre=?, correo=?, telefono=?, direccion=?, tipo_usuario=? WHERE id=?'
            );
            $stmt->execute([$nombre, $correo, $telefono, $direccion, $tipoUsuario, $targetId]);
        }

        if ($isSelf) {
            $_SESSION['user']['nombre']       = $nombre;
            $_SESSION['user']['correo']       = $correo;
            $_SESSION['user']['tipo_usuario'] = $tipoUsuario;
        }

        setFlash('ok', 'Usuario actualizado correctamente.');
        redirect(url($role === 'admin' && !$isSelf ? '/usuarios/index.php' : '/usuarios/edit.php?id=' . $targetId));
    }

    $usuario = array_merge($usuario, [
        'nombre' => $nombre, 'correo' => $correo, 'telefono' => $telefono,
        'direccion' => $direccion, 'tipo_usuario' => $tipoUsuario,
    ]);
}

$pageTitle = $isSelf ? 'Mi perfil' : 'Editar usuario';
require __DIR__ . '/../includes/header.php';
?>
<div class="card" style="max-width:480px;">
    <h1><?= $isSelf ? 'Mi perfil' : 'Editar usuario' ?></h1>

    <?php foreach ($errors as $e): ?>
        <div class="flash flash-error"><?= h($e) ?></div>
    <?php endforeach; ?>

    <form method="post" class="form-grid">
        <input type="hidden" name="id" value="<?= (int)$usuario['id'] ?>">
        <div>
            <label>Nombre completo</label>
            <input type="text" name="nombre" value="<?= h($usuario['nombre']) ?>" required>
        </div>
        <div>
            <label>Correo</label>
            <input type="email" name="correo" value="<?= h($usuario['correo']) ?>" required>
        </div>
        <div>
            <label>Teléfono</label>
            <input type="text" name="telefono" value="<?= h($usuario['telefono']) ?>">
        </div>
        <div>
            <label>Dirección</label>
            <input type="text" name="direccion" value="<?= h($usuario['direccion']) ?>">
        </div>
        <div>
            <label>Nueva contraseña (dejar vacío para no cambiarla)</label>
            <input type="password" name="password" minlength="6">
        </div>
        <div>
            <label>Rol</label>
            <?php if ($role === 'admin'): ?>
                <select name="tipo_usuario">
                    <option value="cliente"    <?= $usuario['tipo_usuario']==='cliente'?'selected':'' ?>>Cliente</option>
                    <option value="trabajador" <?= $usuario['tipo_usuario']==='trabajador'?'selected':'' ?>>Trabajador</option>
                    <option value="admin"      <?= $usuario['tipo_usuario']==='admin'?'selected':'' ?>>Admin</option>
                </select>
            <?php else: ?>
                <input type="text" value="<?= h($usuario['tipo_usuario']) ?>" disabled>
                <p class="muted">Solo un administrador puede cambiar tu rol.</p>
            <?php endif; ?>
        </div>
        <div class="actions">
            <button class="btn" type="submit">Guardar cambios</button>
            <?php if ($role === 'admin' && !$isSelf): ?>
                <a class="btn btn-secondary" href="<?= url('/usuarios/index.php') ?>">Cancelar</a>
            <?php else: ?>
                <a class="btn btn-secondary" href="<?= url('/index.php') ?>">Cancelar</a>
            <?php endif; ?>
        </div>
    </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
