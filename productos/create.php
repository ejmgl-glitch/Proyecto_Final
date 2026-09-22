<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require __DIR__ . '/../includes/functions.php';

requireRole(['admin', 'trabajador']);

$errors = [];
$old = ['nombre'=>'','marca'=>'','descripcion'=>'','precio'=>'','color'=>'','genero'=>'hombre','id_categoria'=>'','categoria_nueva'=>''];

$categorias = $pdo->query('SELECT id, nombre FROM categoria ORDER BY nombre')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['nombre']          = trim($_POST['nombre'] ?? '');
    $old['marca']           = trim($_POST['marca'] ?? '');
    $old['descripcion']     = trim($_POST['descripcion'] ?? '');
    $old['precio']          = trim($_POST['precio'] ?? '');
    $old['color']           = trim($_POST['color'] ?? '');
    $old['genero']          = $_POST['genero'] ?? 'hombre';
    $old['id_categoria']    = $_POST['id_categoria'] ?? '';
    $old['categoria_nueva'] = trim($_POST['categoria_nueva'] ?? '');

    if ($old['nombre'] === '') $errors[] = 'El nombre del producto es obligatorio.';
    if (!is_numeric($old['precio']) || (float)$old['precio'] < 0) $errors[] = 'El precio debe ser un número válido.';
    if (!in_array($old['genero'], ['hombre','mujer','ninos'], true)) $errors[] = 'Género inválido.';

    $idCategoria = null;
    if (!$errors) {
        if ($old['categoria_nueva'] !== '') {
            $stmt = $pdo->prepare('INSERT INTO categoria (nombre) VALUES (?)');
            $stmt->execute([$old['categoria_nueva']]);
            $idCategoria = (int)$pdo->lastInsertId();
        } elseif ($old['id_categoria'] !== '') {
            $idCategoria = (int)$old['id_categoria'];
        }
    }

    if (!$errors) {
        $stmt = $pdo->prepare(
            'INSERT INTO producto (nombre, marca, descripcion, precio, color, genero, id_categoria)
             VALUES (?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $old['nombre'], $old['marca'], $old['descripcion'],
            $old['precio'], $old['color'], $old['genero'], $idCategoria,
        ]);
        setFlash('ok', 'Producto creado correctamente.');
        redirect(url('/productos/index.php'));
    }
}

$pageTitle = 'Nuevo producto';
require __DIR__ . '/../includes/header.php';
?>
<div class="card" style="max-width:520px;">
    <h1>Nuevo producto</h1>

    <?php foreach ($errors as $e): ?>
        <div class="flash flash-error"><?= h($e) ?></div>
    <?php endforeach; ?>

    <form method="post" class="form-grid">
        <div>
            <label>Nombre</label>
            <input type="text" name="nombre" value="<?= h($old['nombre']) ?>" required>
        </div>
        <div>
            <label>Marca</label>
            <input type="text" name="marca" value="<?= h($old['marca']) ?>">
        </div>
        <div>
            <label>Descripción</label>
            <textarea name="descripcion"><?= h($old['descripcion']) ?></textarea>
        </div>
        <div>
            <label>Precio</label>
            <input type="number" step="0.01" min="0" name="precio" value="<?= h($old['precio']) ?>" required>
        </div>
        <div>
            <label>Color</label>
            <input type="text" name="color" value="<?= h($old['color']) ?>">
        </div>
        <div>
            <label>Género</label>
            <select name="genero">
                <option value="hombre" <?= $old['genero']==='hombre'?'selected':'' ?>>Hombre</option>
                <option value="mujer"  <?= $old['genero']==='mujer'?'selected':'' ?>>Mujer</option>
                <option value="ninos"  <?= $old['genero']==='ninos'?'selected':'' ?>>Niños</option>
            </select>
        </div>
        <div>
            <label>Categoría existente</label>
            <select name="id_categoria">
                <option value="">-- Sin categoría --</option>
                <?php foreach ($categorias as $c): ?>
                    <option value="<?= (int)$c['id'] ?>" <?= (string)$old['id_categoria']===(string)$c['id']?'selected':'' ?>>
                        <?= h($c['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label>...o crear una categoría nueva</label>
            <input type="text" name="categoria_nueva" value="<?= h($old['categoria_nueva']) ?>" placeholder="Ej: Zapatillas">
        </div>
        <div class="actions">
            <button class="btn" type="submit">Crear</button>
            <a class="btn btn-secondary" href="<?= url('/productos/index.php') ?>">Cancelar</a>
        </div>
    </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
