<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$page_title = 'Información de Envíos | Paso Chilero';
require __DIR__ . '/../includes/header.php';
?>

<div class="container" style="max-width: 960px; margin: 35px auto; padding: 0 20px;">
    <div style="margin-bottom: 28px;">
        <span class="eyebrow">Cobertura Nacional</span>
        <h1 style="font-size: 2.2rem; font-weight: 800; color: #1e293b; margin: 6px 0 10px 0;">Políticas de Envío y Entrega</h1>
        <p class="muted" style="font-size: 1rem; margin: 0;">Llevamos tu calzado preferido a cualquier rincón de Guatemala de forma rápida y segura.</p>
    </div>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 20px; margin-bottom: 30px;">
        <div class="card" style="padding: 24px; border-radius: 12px; margin: 0;">
            <div style="font-size: 2rem; margin-bottom: 10px;">🚚</div>
            <h3 style="margin: 0 0 8px 0; color: #1e293b;">Ciudad de Guatemala</h3>
            <p class="muted" style="margin: 0; line-height: 1.6;">
                Entrega estimada de <strong>24 a 48 horas hábiles</strong>. Envío estándar por tarifa plana de Q 25.00 o <strong>gratis</strong> en compras mayores a Q 400.00.
            </p>
        </div>

        <div class="card" style="padding: 24px; border-radius: 12px; margin: 0;">
            <div style="font-size: 2rem; margin-bottom: 10px;">📦</div>
            <h3 style="margin: 0 0 8px 0; color: #1e293b;">Departamentos y Municipios</h3>
            <p class="muted" style="margin: 0; line-height: 1.6;">
                Entrega de <strong>48 a 72 horas hábiles</strong> a través de paqueterías aliadas (Cargo Expreso / Guatex) con número de guía para rastreo en tiempo real.
            </p>
        </div>

        <div class="card" style="padding: 24px; border-radius: 12px; margin: 0;">
            <div style="font-size: 2rem; margin-bottom: 10px;">🏬</div>
            <h3 style="margin: 0 0 8px 0; color: #1e293b;">Recogida en Tienda</h3>
            <p class="muted" style="margin: 0; line-height: 1.6;">
                Puedes recoger tu pedido sin costo en nuestra tienda física de Miraflores, Ciudad de Guatemala, presentando tu documento y comprobante de compra.
            </p>
        </div>
    </div>

    <div class="card" style="padding: 28px; border-radius: 12px;">
        <h2 style="font-size: 1.4rem; color: #1e293b; margin-top: 0;">Puntos importantes a tener en cuenta</h2>
        <ul style="color: var(--text); line-height: 1.8; padding-left: 20px; margin-bottom: 0;">
            <li>Los pedidos realizados después de las 14:00 horas se procesan al siguiente día hábil.</li>
            <li>Al despachar tu pedido recibirás una notificación de confirmación para coordinar la entrega.</li>
            <li>Si requieres entrega en fin de semana o en un horario especial, infórmanos al realizar tu compra o contáctanos por WhatsApp.</li>
        </ul>
        <div style="margin-top: 24px;">
            <a href="<?= url('/productos/index.php') ?>" class="btn">Explorar Catálogo</a>
            <a href="<?= url('/Pags_info/contacto.php') ?>" class="btn btn-secondary" style="margin-left: 10px;">¿Tienes dudas? Contáctanos</a>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>