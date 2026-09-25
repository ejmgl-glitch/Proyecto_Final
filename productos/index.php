<?php
// productos/index.php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$puedeEditar = hasRole(['admin', 'trabajador']);$puedeComprar = !isLoggedIn() || currentRole() === 'cliente';

$buscar          = trim($_GET['buscar'] ?? '');
$filtroCategoria = trim($_GET['categoria'] ?? '');
$filtroGenero    = trim($_GET['genero'] ?? '');
$filtroColor     = trim($_GET['color'] ?? '');

$categorias =$pdo->query('SELECT id, nombre FROM categoria ORDER BY nombre')->fetchAll();
$colores    =$pdo->query("SELECT DISTINCT color FROM producto WHERE color IS NOT NULL AND color <> '' ORDER BY color")->fetchAll(PDO::FETCH_COLUMN);
$generos    = ['hombre' => 'Hombre', 'mujer' => 'Mujer', 'ninos' => 'Niños'];

$where  = [];$params = [];

if ($buscar !== '') {$where[] = '(p.nombre LIKE ? OR p.marca LIKE ? OR p.descripcion LIKE ?)';
    $param = '\%' .$buscar . '%';
    array_push($params,$param, $param,$param);
}
if ($filtroCategoria !== '') {$where[] = 'p.id_categoria = ?';
    $params[] = (int)$filtroCategoria;
}
if ($filtroGenero !== '' && isset($generos[$filtroGenero])) {$where[] = 'p.genero = ?';
    $params[] =$filtroGenero;
}
if ($filtroColor !== '') {$where[] = 'p.color = ?';
    $params[] =$filtroColor;
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
$productos =$stmt->fetchAll();

// Variantes (tallas + stock) de los productos listados
$variantesPorProducto = [];
if ($productos) {
    $ids = array_column($productos, 'id');
    $in = implode(',', array_fill(0, count($ids), '?'));
    $stmtVar =$pdo->prepare("SELECT * FROM variante_producto WHERE id_producto IN ($in) ORDER BY (talla + 0) ASC");
    $stmtVar->execute($ids);
    foreach ($stmtVar->fetchAll() as$v) {
        $variantesPorProducto[$v['id_producto']][] = [
            'id'     => (int)$v['id'],
            'talla'  => $v['talla'],
            'stock'  => (int)$v['stock'],
            'codigo' => $v['codigo_unico'],
        ];
    }
}

// Agrupar productos por categoría
$grupos = [];
foreach ($productos as$p) {
    $nombreGrupo =$p['categoria_nombre'] ?? 'Sin categoría';
    $grupos[$nombreGrupo][] =$p;
}

$pageTitle = 'Catálogo de Productos | Paso Chilero';
require __DIR__ . '/../includes/header.php';
?>

<div class="catalog-container">
    <!-- Notificaciones del sistema -->
    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash flash-<?= h($_SESSION['flash']['type']) ?>">
            <?= h($_SESSION['flash']['msg']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <!-- Encabezado con título centrado y acción para administradores -->
    <div class="page-header-box">
        <div>
            <span class="eyebrow">Catálogo Oficial</span>
            <h1>Calzado para cada estilo</h1>
            <p class="muted" style="margin: 6px 0 0 0;">Descubre nuestra variedad de marcas y tallas disponibles en Guatemala.</p>
        </div>
        <?php if ($puedeEditar): ?>
            <a class="btn" href="<?= url('/productos/create.php') ?>">+ Nuevo producto</a>
        <?php endif; ?>
    </div>

    <!-- Barra de Filtros responsiva -->
    <form method="get" class="filtros-bar">
        <input type="hidden" name="buscar" value="<?= h($buscar) ?>">
        <div class="filtro-campo">
            <label>Categoría</label>
            <select name="categoria" onchange="this.form.submit()">
                <option value="">Todas las categorías</option>
                <?php foreach ($categorias as$c): ?>
                    <option value="<?= (int)$c['id'] ?>" <?= (string)$filtroCategoria === (string)$c['id'] ? 'selected' : '' ?>>
                        <?= h($c['nombre']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filtro-campo">
            <label>Género</label>
            <select name="genero" onchange="this.form.submit()">
                <option value="">Todos los géneros</option>
                <?php foreach ($generos as $val =>$label): ?>
                    <option value="<?= $val ?>" <?= $filtroGenero === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filtro-campo">
            <label>Color</label>
            <select name="color" onchange="this.form.submit()">
                <option value="">Todos los colores</option>
                <?php foreach ($colores as$col): ?>
                    <option value="<?= h($col) ?>" <?= $filtroColor === $col ? 'selected' : '' ?>><?= h($col) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filtro-acciones">
            <button class="btn btn-sm" type="submit">Filtrar</button>
            <a class="btn btn-sm btn-secondary" href="<?= url('/productos/index.php') ?>">Limpiar</a>
        </div>
    </form>

    <?php if ($buscar !== ''): ?>
        <p class="muted" style="margin-bottom: 20px;">
            Resultados para "<strong><?= h($buscar) ?></strong>" • 
            <a href="<?= url('/productos/index.php') ?>" style="color:var(--primary); text-decoration:none; font-weight:600;">Quitar búsqueda</a>
        </p>
    <?php endif; ?>

    <?php if (!$productos): ?>
        <div class="empty-box-card">
            <div class="empty-box-icon">🔍</div>
            <h2 style="color: #1e293b; margin: 0 0 8px 0; font-size: 1.5rem;">No se encontraron productos</h2>
            <p class="muted" style="margin: 0 auto 16px auto;">Prueba ajustando los filtros de búsqueda o eliminando los criterios aplicados.</p>
            <a href="<?= url('/productos/index.php') ?>" class="btn btn-sm">Ver todo el catálogo</a>
        </div>
    <?php else: ?>
        <?php foreach ($grupos as $nombreCategoria =>$productosGrupo): ?>
            <section class="categoria-seccion" style="margin-bottom: 40px;">
                <div style="display: flex; align-items: baseline; gap: 12px; border-bottom: 2px solid var(--border); padding-bottom: 8px; margin-bottom: 20px;">
                    <h2 style="font-size: 1.45rem; font-weight: 800; color: #1e293b; margin: 0;"><?= h($nombreCategoria) ?></h2>
                    <span class="muted" style="font-size: 0.88rem; font-weight: 600;">(<?= count($productosGrupo) ?>)</span>
                </div>

                <div class="grid-products">
                    <?php foreach ($productosGrupo as$p):
                        $variantes =$variantesPorProducto[$p['id']] ?? [];$stockTotal = array_sum(array_column($variantes, 'stock'));$modalData = [
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
                            <div>
                                <div class="product-image-wrap">
                                    <?php if (!empty($p['imagen'])): ?>
                                        <img src="<?= h($p['imagen']) ?>" alt="<?= h($p['nombre']) ?>" class="product-thumb">
                                    <?php else: ?>
                                        <div style="font-size: 2.5rem;">👟</div>
                                    <?php endif; ?>

                                    <?php if ($stockTotal <= 0): ?>
                                        <span class="badge-agotado" style="position: absolute; top: 10px; right: 10px; background: var(--err); color: #fff; font-size: 0.72rem; font-weight: 700; padding: 4px 9px; border-radius: 999px;">
                                            Agotado
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="product-info">
                                    <span class="product-meta-badge"><?= h($p['marca']) ?> • <?= h($generos[$p['genero']] ?? $p['genero']) ?></span>
                                    <h3><?= h($p['nombre']) ?></h3>
                                    <p class="muted"><?= h($p['color'] ?: 'Color estándar') ?></p>
                                </div>
                            </div>

                            <div>
                                <p class="price">Q <?= number_format((float)$p['precio'], 2) ?></p>

                                <div class="actions">
                                    <button type="button" class="btn btn-sm" onclick="abrirModalProducto(this.closest('.product-card'))">
                                        Elegir talla
                                    </button>

                                    <a class="btn btn-sm btn-secondary" href="<?= url('/reviews/index.php?id_producto=' . (int)$p['id']) ?>" title="Ver opiniones">
                                        Reseñas
                                    </a>

                                    <?php if ($puedeComprar && isLoggedIn()): ?>
                                        <a class="btn btn-sm btn-secondary" href="<?= url('/wishlist/toggle.php?id_producto=' . (int)$p['id']) ?>" title="Guardar en favoritos">
                                            🤍
                                        </a>
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
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- Modal de Selección de Talla y Compra -->
<div id="modalProductoBackdrop" class="cart-modal-backdrop" style="display:none;" onclick="if(event.target===this) cerrarModalProducto()">
    <div class="cart-modal-content" style="max-width:540px; border-radius:14px; box-shadow:0 20px 40px rgba(0,0,0,0.2);">
        <div class="cart-modal-header" style="padding:16px 20px;">
            <h3 id="modalNombre" style="margin:0; font-size:1.25rem; font-weight:800; color:#1e293b;">Producto</h3>
            <button type="button" class="cart-modal-close" onclick="cerrarModalProducto()">✕</button>
        </div>
        <div class="cart-modal-body" style="padding:20px;">
            <div style="display:flex; gap:16px; flex-wrap:wrap; align-items:center;">
                <img id="modalImagen" src="" alt="" style="width:140px; height:140px; object-fit:cover; border-radius:10px; background:#f1ede7; flex-shrink:0; border:1px solid var(--border);">
                <div style="flex:1; min-width:180px;">
                    <p class="muted" id="modalMarcaCategoria" style="margin:0 0 4px; font-weight:600; text-transform:uppercase; font-size:0.8rem;"></p>
                    <p class="price" id="modalPrecio" style="font-size:1.4rem; margin:0 0 8px;"></p>
                    <p id="modalDescripcion" style="margin:0 0 8px; font-size:0.92rem; line-height:1.4; color:var(--text);"></p>
                    <p class="muted" id="modalColorGenero" style="margin:0; font-size:0.85rem;"></p>
                </div>
            </div>

            <div id="modalPurchaseBox" style="margin-top:20px; border-top:1px solid var(--border); padding-top:16px;">
                <label style="font-weight:700; color:#334155; margin-bottom:10px; display:block;">Elige tu talla (US)</label>
                <div id="modalTallas" class="modal-tallas-grid" style="display:flex; flex-wrap:wrap; gap:8px;"></div>

                <div id="modalCantidadBox" style="display:none; margin-top:16px; align-items:center; gap:14px;">
                    <span style="font-weight:600;">Cantidad:</span>
                    <div class="qty-control">
                        <button type="button" class="btn-qty" onclick="cambiarCantidadModal(-1)">-</button>
                        <span id="modalCantidad">1</span>
                        <button type="button" class="btn-qty" onclick="cambiarCantidadModal(1)">+</button>
                    </div>
                    <span class="muted" id="modalStockDisponible" style="font-size:0.88rem;"></span>
                </div>
            </div>
        </div>
        <div class="cart-modal-footer" style="padding:16px 20px;">
            <button type="button" class="btn" style="width:100%; padding:12px; font-weight:700; font-size:1rem;" id="modalBtnAgregar" onclick="confirmarAgregarModal()" disabled>
                Selecciona una talla
            </button>
        </div>
    </div>
</div>

<style>
.talla-pill {
    border: 1px solid var(--border);
    background: #fff;
    border-radius: 8px;
    padding: 8px 12px;
    font-size: 0.88rem;
    font-weight: 600;
    cursor: pointer;
    min-width: 48px;
    text-align: center;
    transition: all 0.2s ease;
}
.talla-pill:hover { border-color: var(--primary); color: var(--primary); }
.talla-pill.selected { background: var(--primary); border-color: var(--primary); color: #fff; }
.talla-pill.agotada { opacity: 0.35; cursor: not-allowed; text-decoration: line-through; }
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
    alert(`¡"${data.nombre}" talla ${modalState.talla} agregado al carrito!`);
    cerrarModalProducto();
}

function cerrarModalProducto() {
    document.getElementById('modalProductoBackdrop').style.display = 'none';
    history.replaceState(null, '', window.location.pathname + window.location.search);
}

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