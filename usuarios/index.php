<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/functions.php';

// Solo admin y trabajador pueden ver listados de usuarios.
// El cliente NO tiene acceso a este listado (solo a su propio perfil).
requireRole(['admin', 'trabajador']);

$role = currentRole();

if ($role === 'admin') {
    // El admin ve a todos los usuarios
    $stmt = $pdo->query('SELECT id, nombre, correo, telefono, tipo_usuario FROM usuario ORDER BY tipo_usuario, nombre');
} else {
    // El trabajador solo puede VER usuarios tipo cliente (no editarlos)
    $stmt = $pdo->prepare('SELECT id, nombre, correo, telefono, tipo_usuario FROM usuario WHERE tipo_usuario = "cliente" ORDER BY nombre');
    $stmt->execute();
}
$usuarios = $stmt->fetchAll();

$pageTitle = 'Usuarios';
require __DIR__ . '/../includes/header.php';
?>
<div class="card">
    <div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;">
        <h1><?= $role === 'admin' ? 'Todos los usuarios' : 'Usuarios clientes' ?></h1>
        <?php if ($role === 'admin'): ?>
            <a class="btn" href="<?= url('/usuarios/create.php') ?>">+ Nuevo usuario</a>
        <?php endif; ?>
    </div>

    <?php if (!$usuarios): ?>
        <p class="empty-state">No hay usuarios para mostrar.</p>
    <?php else: ?>
    <table>
        <thead>
            <tr><th>Nombre</th><th>Correo</th><th>Teléfono</th><th>Rol</th><th>Acciones</th></tr>
        </thead>
        <tbody>
        <?php foreach ($usuarios as $u): ?>
            <tr>
                <td><?= h($u['nombre']) ?></td>
                <td><?= h($u['correo']) ?></td>
                <td><?= h($u['telefono']) ?></td>
                <td><span class="badge"><?= h($u['tipo_usuario']) ?></span></td>
                <td class="actions">
                    <?php if ($role === 'admin'): ?>
                        <a class="btn btn-sm" href="<?= url('/usuarios/edit.php?id=' . (int)$u['id']) ?>">Editar</a>
                        <form class="form-inline" method="post" action="<?= url('/usuarios/delete.php') ?>" onsubmit="return confirm('¿Eliminar este usuario?');">
                            <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
                            <button class="btn btn-sm btn-danger" type="submit">Eliminar</button>
                        </form>
                    <?php else: ?>
                        <span class="muted">Solo lectura</span>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
