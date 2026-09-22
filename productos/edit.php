<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole(['admin', 'trabajador']);

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
$stmt = $pdo->prepare('SELECT * FROM producto WHERE id = ?');
$stmt->execute([$id]);
$producto = $stmt->fetch();

if (!$producto) {
    http_response_code(404);
    die('Producto no encontrado.');
}

$categorias = $pdo->query('SELECT id, nombre FROM categoria ORDER BY nombre')->fetchAll();
$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre      = trim($_POST['nombre'] ?? '');
    $marca       = trim($_POST['marca'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');
    $precio      = trim($_POST['precio'] ?? '');
    $color       = trim($_POST['color'] ?? '');
    $genero      = $_POST['genero'] ?? 'hombre';
    $imagen = trim($_POST['imagen'] ?? '');
    $idCategoria = $_POST['id_categoria'] !== '' ? (int)$_POST['id_categoria'] : null;

    if ($nombre === '') $errors[] = 'El nombre del producto es obligatorio.';
    if (!is_numeric($precio) || (float)$precio < 0) $errors[] = 'El precio debe ser un número válido.';
    if (!in_array($genero, ['hombre','mujer','ninos'], true)) $errors[] = 'Género inválido.';

    if (!$errors) {
        $stmt = $pdo->prepare(
            'UPDATE producto SET nombre=?, marca=?, descripcion=?, precio=?, color=?, genero=?, imagen=?, id_categoria=? WHERE id=?'
        );
        $stmt->execute([$nombre, $marca, $descripcion, $precio, $color, $genero, $imagen, $idCategoria, $id]);
        setFlash('ok', 'Producto actualizado.');
        redirect(url('/productos/index.php'));
    }

    $producto = array_merge($producto, compact('nombre','marca','descripcion','precio','color','genero', 'imagen'));
    $producto['id_categoria'] = $idCategoria;
}

$pageTitle = 'Editar producto';
require __DIR__ . '/../includes/header.php';
?>
<div class="card" style="max-width:520px;">
    <h1>Editar producto</h1>

    <?php foreach ($errors as $e): ?>
        <div class="flash flash-error"><?= h($e) ?></div>
    <?php endforeach; ?>

    <form method="post" class="form-grid">
        <input type="hidden" name="id" value="<?= (int)$producto['id'] ?>">
        <div>
            <label>Nombre</label>
            <input type="text" name="nombre" value="<?= h($producto['nombre']) ?>" required>
        </div>
        <div>
            <label>Marca</label>
            <input type="text" name="marca" value="<?= h($producto['marca']) ?>">
        </div>
        <div>
            <label>Descripción</label>
            <textarea name="descripcion"><?= h($producto['descripcion']) ?></textarea>
        </div>
        <div>
            <label>Precio</label>
            <input type="number" step="0.01" min="0" name="precio" value="<?= h((string)$producto['precio']) ?>" required>
        </div>
        <div>
            <label>Color</label>
            <input type="text" name="color" value="<?= h($producto['color']) ?>">
        </div>
        <div>
            <label>Imagen (URL)</label>
            <input type="text" name="imagen" value="<?= h($producto['imagen']) ?>" placeholder="https://... o /assets/img/producto.jpg">
        </div>
        <div>
            <label>Género</label>
            <select name="genero">
                <option value="hombre" <?= $producto['genero']==='hombre'?'selected':'' ?>>Hombre</option>
                <option value="mujer"  <?= $producto['genero']==='mujer'?'selected':'' ?>>Mujer</option>
                <option value="ninos"  <?= $producto['genero']==='ninos'?'selected':'' ?>>Niños</option>
            </select>
        </div>
        <div>
            <label>Categoría</label>
            <select name="id_categoria">
                <option value="">-- Sin categoría --</option>
                <?php foreach ($categorias as $c): ?>
                    <option value="<?= (int)$c['id'] ?>" <?= (string)$producto['id_categoria']===(string)$c['id']?'selected':'' ?>>
                        <?= h($c['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="actions">
            <button class="btn" type="submit">Guardar cambios</button>
            <a class="btn btn-secondary" href="<?= url('/productos/index.php') ?>">Cancelar</a>
        </div>
    </form>
</div>
<?php require __DIR__ . '/../includes/footer.php'; ?>
