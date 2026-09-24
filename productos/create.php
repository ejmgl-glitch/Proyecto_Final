<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireRole(['admin', 'trabajador']);

$errors = [];
$old = ['nombre'=>'','marca'=>'','descripcion'=>'','precio'=>'','color'=>'','genero'=>'hombre','id_categoria'=>'','imagen'=>''];
$tallas = tallasDisponibles();
$stockPorTalla = $_POST['stock'] ?? [];

$categorias = $pdo->query('SELECT id, nombre FROM categoria ORDER BY nombre')->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $old['nombre']          = trim($_POST['nombre'] ?? '');
    $old['marca']           = trim($_POST['marca'] ?? '');
    $old['descripcion']     = trim($_POST['descripcion'] ?? '');
    $old['precio']          = trim($_POST['precio'] ?? '');
    $old['color']           = trim($_POST['color'] ?? '');
    $old['genero']          = $_POST['genero'] ?? 'hombre';
    $old['id_categoria']    = $_POST['id_categoria'] ?? '';
    $old['imagen'] = trim($_POST['imagen'] ?? '');

    if ($old['nombre'] === '') $errors[] = 'El nombre del producto es obligatorio.';
    if (!is_numeric($old['precio']) || (float)$old['precio'] < 0) $errors[] = 'El precio debe ser un número válido.';
    if (!in_array($old['genero'], ['hombre','mujer','ninos'], true)) $errors[] = 'Género inválido.';

    if (!$errors) {
        $idCategoria = $old['id_categoria'] !== '' ? (int)$old['id_categoria'] : null;

        $pdo->beginTransaction();

        $stmt = $pdo->prepare(
            'INSERT INTO producto (nombre, marca, descripcion, precio, color, genero, imagen, id_categoria)
             VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );
        $stmt->execute([
            $old['nombre'], $old['marca'], $old['descripcion'],
            $old['precio'], $old['color'], $old['genero'], $old['imagen'], $idCategoria
        ]);
        $idProducto = (int)$pdo->lastInsertId();

        // Crear las variantes (talla + stock) que el admin haya cargado
        $stmtVariante = $pdo->prepare(
            'INSERT INTO variante_producto (id_producto, talla, stock, codigo_unico) VALUES (?, ?, ?, ?)'
        );
        foreach ($stockPorTalla as $talla => $stock) {
            $stock = (int)$stock;
            if ($stock > 0) {
                $codigo = generarCodigoVariante($pdo, $old['nombre'], $idProducto, (string)$talla);
                $stmtVariante->execute([$idProducto, $talla, $stock, $codigo]);
            }
        }

        $pdo->commit();

        setFlash('ok', 'Producto creado correctamente.');
        redirect(url('/productos/index.php'));
    }
}

$pageTitle = 'Nuevo producto';
require __DIR__ . '/../includes/header.php';
?>
<div class="card" style="max-width:640px;">
    <h1>Nuevo producto</h1>

    <?php foreach ($errors as $e): ?>
        <div class="flash flash-error"><?= h($e) ?></div>
    <?php endforeach; ?>

    <form method="post" class="form-grid" style="max-width:none;">
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
            <label>Imagen</label>
            <input type="text" name="imagen" value="<?= h($old['imagen']) ?>">
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
            <label>Tallas y stock (US)</label>
            <p class="muted" style="margin:0 0 8px;">Deja en 0 las tallas que no vas a ofrecer. El código único de cada talla se genera automáticamente.</p>
            <div class="tallas-grid">
                <?php foreach ($tallas as $t): ?>
                    <div class="talla-box">
                        <span>US <?= h($t) ?></span>
                        <input type="number" min="0" name="stock[<?= h($t) ?>]" value="<?= h((string)($stockPorTalla[$t] ?? '0')) ?>">
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="actions">
            <button class="btn" type="submit">Crear</button>
            <a class="btn btn-secondary" href="<?= url('/productos/index.php') ?>">Cancelar</a>
        </div>
    </form>
</div>

<style>
.tallas-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(90px, 1fr)); gap: 8px; }
.talla-box { border: 1px solid var(--border); border-radius: 6px; padding: 6px 8px; background: #fff; }
.talla-box span { display: block; font-size: 0.78rem; color: var(--muted); margin-bottom: 3px; }
.talla-box input { padding: 5px 6px; font-size: 0.85rem; }
</style>

<?php require __DIR__ . '/../includes/footer.php'; ?>