<?php
require __DIR__ . '/config/db.php';
require __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Paso Chilero – Calzado que va con tu estilo';

/* ---------- Productos destacados ---------- */
$productosDestacados = [];
try {
    $stmt = $pdo->query(
        'SELECT p.*, c.nombre AS categoria_nombre 
         FROM producto p 
         LEFT JOIN categoria c ON c.id = p.id_categoria 
         ORDER BY p.id DESC 
         LIMIT 5'
    );
    $productosDestacados = $stmt->fetchAll();
} catch (Throwable $e) {
    $productosDestacados = [];
}

/* ---------- Reseñas ---------- */
$reseniasDestacadas = [];
try {
    $stmt = $pdo->query(
        'SELECT r.*, u.nombre AS usuario_nombre, p.nombre AS producto_nombre 
         FROM reviews r 
         JOIN usuario u ON u.id = r.id_usuario 
         JOIN producto p ON p.id = r.id_producto 
         ORDER BY r.calificacion DESC, r.fecha DESC 
         LIMIT 3'
    );
    $reseniasDestacadas = $stmt->fetchAll();
} catch (Throwable $e) {
    $reseniasDestacadas = [];
}

$reseniasRespaldo = [
    ['nombre' => 'Carlos M.', 'lugar' => 'Guatemala', 'calificacion' => 5, 
     'comentario' => 'Me gustó mucho la variedad de estilos y la atención. Encontré justamente el tipo de zapato que estaba buscando.'],
    ['nombre' => 'Andrea G.', 'lugar' => 'Mixco', 'calificacion' => 5, 
     'comentario' => 'El proceso de compra fue sencillo y el producto cumplió con lo que esperaba. Definitivamente volvería a comprar.'],
    ['nombre' => 'Luis R.', 'lugar' => 'Villa Nueva', 'calificacion' => 5, 
     'comentario' => 'Muy buena experiencia. Hay diferentes opciones y los precios son bastante competitivos.'],
];

require __DIR__ . '/includes/header.php';
?>

<div class="landing">
    <!-- ===== HERO ===== -->
    <section class="hero" style="border-bottom:none;">
        <div>
            <span class="eyebrow">Guatemala</span>
            <h1 class="hero-title">Paso Chilero</h1>
            <p class="hero-desc">
                Calzado que va con tu estilo. Una tienda guatemalteca dedicada a la venta y
                distribución de calzado para quienes buscan comodidad, calidad y estilo en cada paso.
            </p>
            <div class="hero-actions">
                <a class="btn-dark" href="<?= url('/productos/index.php') ?>">Comprar colección</a>
                <a class="btn-outline-dark" href="<?= url('/productos/index.php') ?>">Explorar productos</a>
            </div>
        </div>
        <div class="hero-visual">
            <span class="hero-chip">Guatemala</span>
            <img src="<?= url('/assets/img/imgPrincipal.jpg') ?>" alt="Zapatos">
        </div>
    </section>

    <!-- ===== FEATURES ===== -->
    <section class="features-bar">
        <div class="features-grid">
            <div class="feature-item">
                <strong><span class="dot">•</span>100% Guatemala</strong>
                <span>Tienda y distribución nacional</span>
            </div>
            <div class="feature-item">
                <strong><span class="dot">•</span>Calidad seleccionada</strong>
                <span>Productos elegidos para nuestros clientes</span>
            </div>
            <div class="feature-item">
                <strong><span class="dot">•</span>Atención cercana</strong>
                <span>Servicio pensado para cada cliente</span>
            </div>
        </div>
    </section>

    <!-- ===== DESTACADOS ===== -->
    <section>
        <div class="products-head">
            <div>
                <span class="eyebrow">Lo más vendido</span>
                <h2 class="section-title">Nuestros productos destacados</h2>
                <p class="section-sub" style="margin-bottom:0;">Descubre algunos de los estilos favoritos de nuestros clientes.</p>
            </div>
            <a class="see-all" href="<?= url('/productos/index.php') ?>">Ver colección completa →</a>
        </div>

        <?php if (!$productosDestacados): ?>
            <p class="empty-state">Muy pronto verás aquí nuestros productos. Vuelve pronto.</p>
        <?php else: ?>
            <div class="grid-products">
                <?php foreach ($productosDestacados as $p): ?>
                    <div class="product-card">
                        <?php if (!empty($p['imagen'])): ?>
                            <img src="<?= h($p['imagen']) ?>" alt="<?= h($p['nombre']) ?>" class="product-thumb">
                        <?php else: ?>
                            <div class="product-thumb" style="display:flex;align-items:center;justify-content:center;font-size:2.2rem;">👟</div>
                        <?php endif; ?>
                        <h3><?= h($p['nombre']) ?></h3>
                        <p class="muted"><?= h($p['marca']) ?> • <?= h($p['categoria_nombre'] ?? 'Sin categoría') ?></p>
                        <p class="price">Q <?= number_format((float)$p['precio'], 2) ?></p>
                        <div class="actions" style="margin-top:10px;">
                            <button type="button" class="btn btn-sm" onclick='agregarAlCarrito(<?= json_encode([
                                "id"     => (int)$p["id"],
                                "nombre" => $p["nombre"],
                                "marca"  => $p["marca"],
                                "precio" => (float)$p["precio"],
                                "imagen" => $p["imagen"] ?? ""
                            ]) ?>)'>+ Carrito</button>
                            <a class="btn btn-sm btn-secondary" href="<?= url('/reviews/index.php?id_producto=' . (int)$p['id']) ?>">Reseñas</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </section>

    <!-- ===== QUIÉNES SOMOS ===== -->
    <section>
        <div class="about-grid">
            <div class="about-visual">
                <img src="<?= url('/assets/img/imagenQuienessomos.webp') ?>" alt="Bodega Paso Chilero">
            </div>
            <div class="about-text">
                <span class="eyebrow">Conócenos</span>
                <h2 class="section-title">Quiénes somos</h2>
                <p>
                    Paso Chilero es una tienda guatemalteca dedicada a la venta y distribución de calzado,
                    creada para ofrecer a nuestros clientes diferentes opciones que combinen estilo,
                    comodidad y calidad.
                </p>
                <p>
                    Seleccionamos cuidadosamente nuestros productos para ofrecer zapatos que se adapten
                    a diferentes estilos de vida y necesidades, buscando siempre brindar una experiencia
                    de compra sencilla, confiable y accesible.
                </p>
                <p>
                    Como tienda guatemalteca, queremos construir una relación cercana con nuestros
                    clientes y convertirnos en una opción de confianza para encontrar el par ideal
                    para cada ocasión.
                </p>
                <div class="about-stats">
                    <div><strong>100%</strong><span>Guatemala</span></div>
                    <div><strong>Nacional</strong><span>Tienda y distribución</span></div>
                </div>
            </div>
        </div>
    </section>

    <!-- ===== MISIÓN / VISIÓN ===== -->
    <section>
        <span class="eyebrow">Proyección</span>
        <div class="mv-grid" style="margin-top:16px;">
            <div class="mv-card light">
                <span class="eyebrow">Misión</span>
                <h3>Misión</h3>
                <p>
                    Ofrecer a nuestros clientes en Guatemala una variedad de calzado que combine calidad,
                    comodidad y estilo, mediante una selección de productos que responda a diferentes
                    gustos y necesidades. Brindamos una experiencia de compra confiable y cercana, buscando
                    que cada cliente encuentre el calzado adecuado para acompañarlo en su día a día.
                </p>
            </div>
            <div class="mv-card dark">
                <span class="eyebrow on-dark">Visión</span>
                <h3>Visión</h3>
                <p>
                    Ser una tienda de calzado reconocida en Guatemala por la calidad de nuestros productos,
                    la variedad de nuestras opciones y la confianza de nuestros clientes. Buscamos crecer
                    como una distribuidora de referencia, ampliando nuestra presencia y ofreciendo cada vez
                    más alternativas de calzado para nuestros clientes.
                </p>
            </div>
        </div>
    </section>

    <!-- ===== RESEÑAS ===== -->
    <section>
        <span class="eyebrow">Testimonios</span>
        <h2 class="section-title">Reseñas</h2>
        <p class="section-sub">Lo que dicen nuestros clientes en Guatemala.</p>
        <div class="reviews-grid">
            <?php if ($reseniasDestacadas): ?>
                <?php foreach ($reseniasDestacadas as $r): ?>
                    <div class="review-card">
                        <span class="stars"><?= str_repeat('★', (int)$r['calificacion']) . str_repeat('☆', 5 - (int)$r['calificacion']) ?></span>
                        <p class="comment">“<?= h($r['comentario']) ?>”</p>
                        <div class="who"><?= h($r['usuario_nombre']) ?> • <?= h($r['producto_nombre']) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <?php foreach ($reseniasRespaldo as $r): ?>
                    <div class="review-card">
                        <span class="stars"><?= str_repeat('★', $r['calificacion']) ?></span>
                        <p class="comment">“<?= h($r['comentario']) ?>”</p>
                        <div class="who"><?= h($r['nombre']) ?> • <?= h($r['lugar']) ?></div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- ===== TIENDA / UBICACIÓN ===== -->
    <section>
        <span class="eyebrow">Tienda física</span>
        <h2 class="section-title" style="margin-bottom:24px;">Visítanos en Guatemala</h2>
        <div class="store-grid">
            <div class="store-card">
                <div>
                    <h3>Paso Chilero – Guatemala</h3>
                    <p class="muted">Guatemala, Guatemala</p>
                    <p class="muted">Lun – Sáb: 9:00 – 18:00</p>
                    <p class="muted" style="margin-top:10px;">
                        Visítanos para conocer nuestros productos, encontrar el estilo que buscas y recibir
                        asesoramiento para elegir el calzado adecuado.
                    </p>
                </div>
                <a class="btn-dark" href="<?= url('/productos/index.php') ?>">Ver productos</a>
            </div>
            <div class="map-embed">
                <iframe
                    src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3860.587226264456!2d-90.5518585!3d14.622574599999997!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x8589a1a52630905d%3A0xdb6544c413fe09bb!2sMeat%20Pack%20Miraflores!5e0!3m2!1ses-419!2sus!4v1790128598507!5m2!1ses-419!2sus"
                    width="100%"
                    height="100%"
                    style="border:0;"
                    allowfullscreen=""
                    loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    title="Ubicación Paso Chilero">
                </iframe>
            </div>
        </div>
    </section>

    <!-- ===== FOOTER DE CONTENIDO ===== -->
    <section class="content-footer" style="border-bottom:none;">
        <div class="cf-grid">
            <div class="cf-brand">
                <h4 style="text-transform:uppercase;letter-spacing:0.04em;">Paso Chilero</h4>
                <p>Tienda guatemalteca especializada en la venta y distribución de calzado. Encuentra el estilo que va contigo.</p>
            </div>
            <div class="cf-col">
                <h4>Tienda Paso Chilero</h4>
                <ul>
                    <li><a href="<?= url('/productos/index.php') ?>">Ver productos</a></li>
                    <li><a href="<?= url('/productos/index.php') ?>">Más vendidos</a></li>
                    <li><a href="<?= url('/productos/index.php') ?>">Nuevos productos</a></li>
                    <li><a href="<?= url('/productos/index.php') ?>">Categorías</a></li>
                </ul>
            </div>
            <div class="cf-col">
                <h4>Ayuda</h4>
                <ul>
                    <li><a href="<?= url('/Pags_info/envios.php') ?>">Envíos</a></li>
                    <li><a href="<?= url('/Pags_info/devoluciones.php') ?>">Cambios y devoluciones</a></li>
                    <li><a href="<?= url('/Pags_info/faq.php') ?>">Preguntas frecuentes</a></li>
                    <li><a href="<?= url('/Pags_info/contacto.php') ?>">Contacto</a></li>
                </ul>
            </div>
        </div>
    </section>
</div>

<script>
function getCartKey() {
    const user = <?= json_encode($_SESSION['user'] ?? null) ?>;
    return user && user.id ? ('chilero_carrito_' + user.id) : 'chilero_carrito_guest';
}

function agregarAlCarrito(producto) {
    const cartKey = getCartKey();
    let carrito = [];
    try {
        carrito = JSON.parse(localStorage.getItem(cartKey)) || [];
    } catch (e) {
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
    localStorage.setItem(cartKey, JSON.stringify(carrito));
    window.dispatchEvent(new Event('carrito_actualizado'));
    alert('🛒 "' + producto.nombre + '" agregado al carrito!');
}
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>