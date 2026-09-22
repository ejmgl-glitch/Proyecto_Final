<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/functions.php';

requireLogin();

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM reviews WHERE id = ?');
$stmt->execute([$id]);
$review = $stmt->fetch();

if (!$review) {
    http_response_code(404);
    die('Reseña no encontrada.');
}

$userId = (int)$_SESSION['user']['id'];
$esDueño = ((int)$review['id_usuario'] === $userId);

if (!$esDueño && !hasRole(['admin'])) {
    http_response_code(403);
    die('No tienes permiso para editar esta reseña.');
}

$validas = [1, 2, 3, 4, 5];
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $calificacion = $_POST['calificacion'] ?? '';
    $comentario   = trim($_POST['comentario'] ?? '');

    if (!in_array($calificacion, $validas, true)) $errors[] = 'Calificación inválida.';

    if (!$errors) {
        $stmt = $pdo->prepare('UPDATE reviews SET calificacion = ?, comentario = ? WHERE id = ?');
        $stmt->execute([$calificacion, $comentario, $id]);
        setFlash('ok', 'Reseña actualizada.');
        redirect(url('/reviews/index.php?id_producto=' . (int)$review['id_producto']));
    }

    $review['calificacion'] = $calificacion;
    $review['comentario']   = $comentario;
}

$pageTitle = 'Editar reseña';
require __DIR__ . '/../includes/header.php';
?>
<div class="card" style="max-width:480px;">
    <h1>Editar reseña</h1>

    <?php foreach ($errors as $e): ?>
        <div class="flash flash-error"><?= h($e) ?></div>
    <?php endforeach; ?>

    <form method="post" class="form-grid">
        <input type="hidden" name="id" value="<?= (int)$review['id'] ?>">
        <div>
            <label>Calificación</label>
            <select name="calificacion">
                <?php foreach ($validas as $v): ?>
                    <option value="<?= str_repeat('⭐', (int)$v) ?>" <?= $review['calificacion']===$v?'selected':'' ?>><?= str_repeat('⭐', (int)$v) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>Comentario</label>
            <textarea name="comentario" maxlength="255"><?= h($review['comentario']) ?></textarea>
        </div>
        <div class="actions">
            <button class="btn" type="submit">Guardar cambios</button>
            <a class="btn btn-secondary" href="<?= url('/reviews/index.php?id_producto=' . (int)$review['id_producto']) ?>">Cancelar</a>
        </div>
    </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
