<?php
// productos/index.php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$puedeEditar = hasRole(['admin', 'trabajador']);
// Solo los clientes (o visitantes sin sesión) ven el botón de agregar al carrito
$puedeComprar = !isLoggedIn() || currentRole() === 'cliente';

$buscar = trim($_GET['buscar'] ?? '');
if ($buscar !== '') {
    $stmt = $pdo->prepare(
        'SELECT p.*, c.nombre AS categoria_nombre 
         FROM producto p 
         LEFT JOIN categoria c ON c.id = p.id_categoria 
         WHERE p.nombre LIKE ? OR p.marca LIKE ? OR p.descripcion LIKE ?
         ORDER BY p.id DESC'
    );
    $param = '%' . $buscar . '%';
    $stmt->execute([$param, $param, $param]);
} else {
    $stmt = $pdo->query(
        'SELECT p.*, c.nombre AS categoria_nombre 
         FROM producto p 
         LEFT JOIN categoria c ON c.id = p.id_categoria 
         ORDER BY p.id DESC'
    );
}
$productos = $stmt->fetchAll();

$pageTitle = 'Productos';
require __DIR__ . '/../includes/header.php';
?>

<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:16px;">
    <h1>Productos</h1>
    <?php if ($puedeEditar): ?>
        <a class="btn" href="<?= url('/productos/create.php') ?>">+ Nuevo producto</a>
    <?php endif; ?>
</div>

<?php if (!$productos): ?>
    <p class="empty-state">Aún no hay productos cargados.</p>
<?php else: ?>
<div class="grid-products">
    <?php foreach ($productos as $p): ?>
        <div class="product-card">
            <?php if (!empty($p['imagen'])): ?>
                <img src="<?= h($p['imagen']) ?>" alt="<?= h($p['nombre']) ?>" class="product-thumb">
            <?php endif; ?>
            <h3><?= h($p['nombre']) ?></h3>
            <p class="muted"><?= h($p['marca']) ?> • <?= h($p['categoria_nombre'] ?? 'Sin categoría') ?></p>
            <p><?= h($p['descripcion']) ?></p>
            <p class="price">Q <?= number_format((float)$p['precio'], 2) ?></p>
            <p class="muted">Color: <?= h($p['color']) ?> | Género: <?= h($p['genero']) ?></p>
            
            <div class="actions" style="margin-top:10px;">
                <?php if ($puedeComprar): ?>
                    <!-- Botón Agregar al Carrito (Solo Clientes / Visitantes) -->
                    <button 
                        type="button" 
                        class="btn btn-sm"
                        onclick='agregarAlCarrito(<?= json_encode([
                            "id"     => (int)$p["id"],
                            "nombre" => $p["nombre"],
                            "marca"  => $p["marca"],
                            "precio" => (float)$p["precio"],
                            "imagen" => $p["imagen"] ?? ""
                        ]) ?>)'
                    >
                        + Carrito
                    </button>
                <?php endif; ?>

                <a class="btn btn-sm btn-secondary" href="<?= url('/reviews/index.php?id_producto=' . (int)$p['id']) ?>">Reseñas</a>
                
                <?php if ($puedeComprar && isLoggedIn()): ?>
                    <a class="btn btn-sm" href="<?= url('/wishlist/toggle.php?id_producto=' . (int)$p['id']) ?>">♥ Wishlist</a>
                <?php endif; ?>

                <?php if ($puedeEditar): ?>
                    <a class="btn btn-sm" href="<?= url('/productos/edit.php?id=' . (int)$p['id']) ?>">Editar</a>
                    <form class="form-inline" method="post" action="<?= url('/productos/delete.php') ?>" onsubmit="return confirm('¿Eliminar este producto?');">
                        <input type="hidden" name="id" value="<?= (int)$p['id'] ?>">
                        <button class="btn btn-sm btn-danger" type="submit">Eliminar</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ($puedeComprar): ?>
<script>
function agregarAlCarrito(producto) {
    let carrito = [];
    try {
        carrito = JSON.parse(localStorage.getItem('chilero_carrito')) || [];
    } catch(e) {
        carrito = [];
    }
    const index = carrito.findIndex(item => item.id === producto.id);
    if (index !== -1) {
        carrito[index].cantidad += 1;
    } else {
        carrito.push({
            id: producto.id,
            nombre: producto.nombre,
            marca: producto.marca,
            precio: producto.precio,
            imagen: producto.imagen,
            cantidad: 1
        });
    }
    localStorage.setItem('chilero_carrito', JSON.stringify(carrito));
    window.dispatchEvent(new Event('carrito_actualizado'));
    alert('¡"' + producto.nombre + '" agregado al carrito!');
}
</script>
<?php endif; ?>

<?php require __DIR__ . '/../includes/footer.php'; ?>