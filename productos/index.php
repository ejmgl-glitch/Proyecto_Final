<?php
// productos/index.php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$puedeEditar = hasRole(['admin', 'trabajador']);
// Solo los clientes (o visitantes sin sesión) ven el flujo de compra
$puedeComprar = !isLoggedIn() || currentRole() === 'cliente';

$buscar          = trim($_GET['buscar'] ?? '');
$filtroCategoria = trim($_GET['categoria'] ?? '');
$filtroGenero    = trim($_GET['genero'] ?? '');
$filtroColor     = trim($_GET['color'] ?? '');

$categorias = $pdo->query('SELECT id, nombre FROM categoria ORDER BY nombre')->fetchAll();
$colores    = $pdo->query("SELECT DISTINCT color FROM producto WHERE color IS NOT NULL AND color <> '' ORDER BY color")->fetchAll(PDO::FETCH_COLUMN);
$generos    = ['hombre' => 'Hombre', 'mujer' => 'Mujer', 'ninos' => 'Niños'];

$where  = [];
$params = [];

if ($buscar !== '') {
    $where[] = '(p.nombre LIKE ? OR p.marca LIKE ? OR p.descripcion LIKE ?)';
    $param = '%' . $buscar . '%';
    array_push($params, $param, $param, $param);
}
if ($filtroCategoria !== '') {
    $where[] = 'p.id_categoria = ?';
    $params[] = (int)$filtroCategoria;
}
if ($filtroGenero !== '' && isset($generos[$filtroGenero])) {
    $where[] = 'p.genero = ?';
    $params[] = $filtroGenero;
}
if ($filtroColor !== '') {
    $where[] = 'p.color = ?';
    $params[] = $filtroColor;
}

$sql = 'SELECT p.*, c.nombre AS categoria_nombre
        FROM producto p
        LEFT JOIN categoria c ON c.id = p.id_categoria';
if ($where) {
    $sql .= ' WHERE ' . implode(' AND ', $where);
}
$sql .= ' ORDER BY (c.nombre IS NULL), c.nombre, p.nombre';

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll();

// Variantes (tallas + stock) de todos los productos listados, en una sola consulta
$variantesPorProducto = [];
if ($productos) {
    $ids = array_column($productos, 'id');
    $in = implode(',', array_fill(0, count($ids), '?'));
    $stmtVar = $pdo->prepare("SELECT * FROM variante_producto WHERE id_producto IN ($in) ORDER BY (talla + 0) ASC");
    $stmtVar->execute($ids);
    foreach ($stmtVar->fetchAll() as $v) {
        $variantesPorProducto[$v['id_producto']][] = [
            'id'     => (int)$v['id'],
            'talla'  => $v['talla'],
            'stock'  => (int)$v['stock'],
            'codigo' => $v['codigo_unico'],
        ];
    }
}

// Agrupar productos por categoría, en el orden en que ya vinieron ordenados
$grupos = [];
foreach ($productos as $p) {
    $nombreGrupo = $p['categoria_nombre'] ?? 'Sin categoría';
    $grupos[$nombreGrupo][] = $p;
}

$pageTitle = 'Productos';
require __DIR__ . '/../includes/header.php';
?>

<div style="display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:10px;margin-bottom:16px;">
    <h1>Productos</h1>
    <?php if ($puedeEditar): ?>
        <a class="btn" href="<?= url('/productos/create.php') ?>">+ Nuevo producto</a>
    <?php endif; ?>
</div>

<!-- ===== FILTROS ===== -->
<?php if ($buscar !== ''): ?>
    <p class="muted" style="margin-bottom:14px;">
        Resultados para "<strong><?= h($buscar) ?></strong>" —
        <a href="<?= url('/productos/index.php') ?>">quitar búsqueda</a>
    </p>
<?php endif; ?>

<form method="get" class="filtros-bar card">
    <input type="hidden" name="buscar" value="<?= h($buscar) ?>">
    <div class="filtro-campo">
        <label>Categoría</label>
        <select name="categoria" onchange="this.form.submit()">
            <option value="">Todas</option>
            <?php foreach ($categorias as $c): ?>
                <option value="<?= (int)$c['id'] ?>" <?= (string)$filtroCategoria === (string)$c['id'] ? 'selected' : '' ?>>
                    <?= h($c['nombre']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filtro-campo">
        <label>Género</label>
        <select name="genero" onchange="this.form.submit()">
            <option value="">Todos</option>
            <?php foreach ($generos as $val => $label): ?>
                <option value="<?= $val ?>" <?= $filtroGenero === $val ? 'selected' : '' ?>><?= $label ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filtro-campo">
        <label>Color</label>
        <select name="color" onchange="this.form.submit()">
            <option value="">Todos</option>
            <?php foreach ($colores as $col): ?>
                <option value="<?= h($col) ?>" <?= $filtroColor === $col ? 'selected' : '' ?>><?= h($col) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="filtro-acciones">
        <button class="btn btn-sm" type="submit">Filtrar</button>
        <a class="btn btn-sm btn-secondary" href="<?= url('/productos/index.php') ?>">Limpiar</a>
    </div>
</form>

<?php if (!$productos): ?>
    <p class="empty-state">No se encontraron productos con esos filtros.</p>
<?php else: ?>
    <?php foreach ($grupos as $nombreCategoria => $productosGrupo): ?>
        <section class="categoria-seccion">
            <h2 class="categoria-titulo"><?= h($nombreCategoria) ?></h2>
            <div class="grid-products">
                <?php foreach ($productosGrupo as $p):
                    $variantes = $variantesPorProducto[$p['id']] ?? [];
                    $stockTotal = array_sum(array_column($variantes, 'stock'));
                    $modalData = [
                        'id'          => (int)$p['id'],
                        'nombre'      => $p['nombre'],
                        'marca'       => $p['marca'],
                        'descripcion' => $p['descripcion'],
                        'precio'      => (float)$p['precio'],
                        'color'       => $p['color'],
                        'genero'      => $generos[$p['genero']] ?? $p['genero'],
                        'categoria'   => $nombreCategoria,
                        'imagen'      => $p['imagen'] ?? '',
                        'variantes'   => $variantes,
                    ];
                ?>
                    <div class="product-card" id="producto-<?= (int)$p['id'] ?>"
                         data-producto='<?= htmlspecialchars(json_encode($modalData, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8') ?>'>
                        <div style="position:relative;">
                            <?php if (!empty($p['imagen'])): ?>
                                <img src="<?= h($p['imagen']) ?>" alt="<?= h($p['nombre']) ?>" class="product-thumb">
                            <?php else: ?>
                                <div class="product-thumb" style="display:flex;align-items:center;justify-content:center;font-size:2.2rem;">👟</div>
                            <?php endif; ?>
                            <?php if ($stockTotal <= 0): ?>
                                <span class="badge-agotado">Agotado</span>
                            <?php endif; ?>
                        </div>
                        <h3><?= h($p['nombre']) ?></h3>
                        <p class="muted"><?= h($p['marca']) ?> • <?= h($generos[$p['genero']] ?? $p['genero']) ?></p>
                        <p class="price">Q <?= number_format((float)$p['precio'], 2) ?></p>

                        <div class="actions" style="margin-top:10px;">
                            <button type="button" class="btn btn-sm" onclick="abrirModalProducto(this.closest('.product-card'))">
                                Ver detalles
                            </button>
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
        </section>
    <?php endforeach; ?>
<?php endif; ?>

<!-- ===== MODAL DE PRODUCTO ===== -->
<div id="modalProductoBackdrop" class="cart-modal-backdrop" style="display:none;" onclick="if(event.target===this) cerrarModalProducto()">
    <div class="cart-modal-content" style="max-width:560px;">
        <div class="cart-modal-header">
            <h3 id="modalNombre" style="margin:0;">Producto</h3>
            <button type="button" class="cart-modal-close" onclick="cerrarModalProducto()">✕</button>
        </div>
        <div class="cart-modal-body">
            <div style="display:flex;gap:16px;flex-wrap:wrap;">
                <img id="modalImagen" src="" alt="" style="width:180px;height:180px;object-fit:cover;border-radius:10px;background:#eee;flex-shrink:0;">
                <div style="flex:1;min-width:200px;">
                    <p class="muted" id="modalMarcaCategoria" style="margin:0 0 6px;"></p>
                    <p class="price" id="modalPrecio" style="font-size:1.3rem;margin:0 0 10px;"></p>
                    <p id="modalDescripcion" style="margin:0 0 10px;"></p>
                    <p class="muted" id="modalColorGenero" style="margin:0;"></p>
                </div>
            </div>

            <div id="modalPurchaseBox" style="margin-top:20px;border-top:1px solid var(--border);padding-top:16px;">
                <label style="margin-bottom:8px;">Elige tu talla (US)</label>
                <div id="modalTallas" class="modal-tallas-grid"></div>

                <div id="modalCantidadBox" style="display:none;margin-top:16px;align-items:center;gap:14px;">
                    <span>Cantidad:</span>
                    <div class="qty-control">
                        <button type="button" class="btn-qty" onclick="cambiarCantidadModal(-1)">-</button>
                        <span id="modalCantidad">1</span>
                        <button type="button" class="btn-qty" onclick="cambiarCantidadModal(1)">+</button>
                    </div>
                    <span class="muted" id="modalStockDisponible"></span>
                </div>
            </div>
        </div>
        <div class="cart-modal-footer">
            <button type="button" class="btn" style="width:100%;" id="modalBtnAgregar" onclick="confirmarAgregarModal()" disabled>
                Selecciona una talla
            </button>
        </div>
    </div>
</div>

<style>
.filtros-bar { display: flex; flex-wrap: wrap; gap: 14px; align-items: flex-end; margin-bottom: 24px; }
.filtro-campo { min-width: 150px; flex: 1; }
.filtro-campo label { margin-bottom: 4px; }
.filtro-acciones { display: flex; gap: 8px; }
.categoria-seccion { margin-bottom: 34px; }
.categoria-titulo { font-size: 1.3rem; border-bottom: 2px solid var(--border); padding-bottom: 8px; margin-bottom: 16px; }
.badge-agotado {
    position: absolute; top: 8px; right: 8px; background: var(--err); color: #fff;
    font-size: 0.7rem; font-weight: 700; padding: 3px 8px; border-radius: 999px; text-transform: uppercase;
}
.modal-tallas-grid { display: flex; flex-wrap: wrap; gap: 8px; }
.talla-pill {
    border: 1px solid var(--border); background: #fff; border-radius: 6px; padding: 7px 12px;
    font-size: 0.85rem; cursor: pointer; min-width: 46px; text-align: center;
}
.talla-pill:hover { border-color: var(--primary); }
.talla-pill.selected { background: var(--primary); border-color: var(--primary); color: #fff; }
.talla-pill.agotada { opacity: 0.4; cursor: not-allowed; text-decoration: line-through; }
</style>

<script>
function getCartKey() {
    const user = <?= json_encode($_SESSION['user'] ?? null) ?>;
    return user && user.id ? ('chilero_carrito_' + user.id) : 'chilero_carrito_guest';
}

const PUEDE_COMPRAR = <?= $puedeComprar ? 'true' : 'false' ?>;

let modalState = { producto: null, talla: null, stock: 0, cantidad: 1 };

function abrirModalProducto(cardEl) {
    const data = JSON.parse(cardEl.getAttribute('data-producto'));
    modalState = { producto: data, talla: null, stock: 0, cantidad: 1 };

    document.getElementById('modalNombre').textContent = data.nombre;
    document.getElementById('modalImagen').src = data.imagen || '';
    document.getElementById('modalImagen').alt = data.nombre;
    document.getElementById('modalMarcaCategoria').textContent = `${data.marca || ''} • ${data.categoria || 'Sin categoría'}`;
    document.getElementById('modalPrecio').textContent = 'Q ' + Number(data.precio).toFixed(2);
    document.getElementById('modalDescripcion').textContent = data.descripcion || 'Sin descripción disponible.';
    document.getElementById('modalColorGenero').textContent = `Color: ${data.color || '—'} | Género: ${data.genero || '—'}`;

    const purchaseBox = document.getElementById('modalPurchaseBox');
    const tallasWrap = document.getElementById('modalTallas');
    tallasWrap.innerHTML = '';

    if (!PUEDE_COMPRAR) {
        purchaseBox.style.display = 'none';
    } else {
        purchaseBox.style.display = 'block';
        if (!data.variantes || data.variantes.length === 0) {
            tallasWrap.innerHTML = '<span class="muted">Este producto todavía no tiene tallas cargadas.</span>';
        } else {
            data.variantes.forEach(v => {
                const pill = document.createElement('button');
                pill.type = 'button';
                pill.className = 'talla-pill' + (v.stock <= 0 ? ' agotada' : '');
                pill.textContent = 'US ' + v.talla;
                pill.disabled = v.stock <= 0;
                pill.onclick = () => seleccionarTalla(v);
                tallasWrap.appendChild(pill);
            });
        }
    }

    document.getElementById('modalCantidadBox').style.display = 'none';
    document.getElementById('modalBtnAgregar').disabled = true;
    document.getElementById('modalBtnAgregar').textContent = PUEDE_COMPRAR ? 'Selecciona una talla' : 'Cerrar';
    if (!PUEDE_COMPRAR) {
        document.getElementById('modalBtnAgregar').disabled = false;
        document.getElementById('modalBtnAgregar').onclick = cerrarModalProducto;
    } else {
        document.getElementById('modalBtnAgregar').onclick = confirmarAgregarModal;
    }

    document.getElementById('modalProductoBackdrop').style.display = 'flex';
    history.replaceState(null, '', '#producto-' + data.id);
}

function seleccionarTalla(variante) {
    modalState.talla = variante.talla;
    modalState.idVariante = variante.id;
    modalState.stock = variante.stock;
    modalState.cantidad = 1;

    document.querySelectorAll('.talla-pill').forEach(p => p.classList.remove('selected'));
    event.currentTarget.classList.add('selected');

    document.getElementById('modalCantidadBox').style.display = 'flex';
    document.getElementById('modalCantidad').textContent = modalState.cantidad;
    document.getElementById('modalStockDisponible').textContent = `(${variante.stock} disponibles)`;
    document.getElementById('modalBtnAgregar').disabled = false;
    document.getElementById('modalBtnAgregar').textContent = 'Agregar al carrito';
}

function cambiarCantidadModal(delta) {
    const nueva = modalState.cantidad + delta;
    if (nueva >= 1 && nueva <= modalState.stock) {
        modalState.cantidad = nueva;
        document.getElementById('modalCantidad').textContent = nueva;
    }
}

function confirmarAgregarModal() {
    if (!modalState.talla) return;
    const data = modalState.producto;
    const cartKey = getCartKey();
    let carrito = [];
    try { carrito = JSON.parse(localStorage.getItem(cartKey)) || []; } catch (e) { carrito = []; }

    const idx = carrito.findIndex(item => item.id_variante === modalState.idVariante);
    if (idx !== -1) {
        carrito[idx].cantidad = Math.min(carrito[idx].cantidad + modalState.cantidad, modalState.stock);
    } else {
        carrito.push({
            id: data.id,
            id_variante: modalState.idVariante,
            talla: modalState.talla,
            nombre: data.nombre,
            marca: data.marca,
            precio: data.precio,
            imagen: data.imagen,
            cantidad: modalState.cantidad,
            stockMax: modalState.stock
        });
    }
    localStorage.setItem(cartKey, JSON.stringify(carrito));
    window.dispatchEvent(new Event('carrito_actualizado'));
    alert(`🛒 "${data.nombre}" talla ${modalState.talla} agregado al carrito!`);
    cerrarModalProducto();
}

function cerrarModalProducto() {
    document.getElementById('modalProductoBackdrop').style.display = 'none';
    history.replaceState(null, '', window.location.pathname + window.location.search);
}

// Si llegamos con #producto-ID en la URL (ej. desde el inicio), abrir el modal automáticamente
document.addEventListener('DOMContentLoaded', () => {
    if (window.location.hash.startsWith('#producto-')) {
        const card = document.querySelector(window.location.hash);
        if (card) {
            card.scrollIntoView({ behavior: 'smooth', block: 'center' });
            abrirModalProducto(card);
        }
    }
});
</script>

<?php require __DIR__ . '/../includes/footer.php'; ?>