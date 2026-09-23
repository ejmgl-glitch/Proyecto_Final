<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$page_title = 'Cambios y Devoluciones | Paso Chilero';
require __DIR__ . '/../includes/header.php';
?>

<div class="container" style="max-width: 960px; margin: 35px auto; padding: 0 20px;">
    <div style="margin-bottom: 28px;">
        <span class="eyebrow">Garantía de Satisfacción</span>
        <h1 style="font-size: 2.2rem; font-weight: 800; color: #1e293b; margin: 6px 0 10px 0;">Políticas de Cambios y Devoluciones</h1>
        <p class="muted" style="font-size: 1rem; margin: 0;">Queremos que encuentres el calzado perfecto para ti. Conoce nuestras condiciones de cambio.</p>
    </div>

    <div class="card" style="padding: 28px; border-radius: 12px; margin-bottom: 24px;">
        <h2 style="font-size: 1.35rem; color: #1e293b; margin-top: 0;">Condiciones para solicitar un cambio</h2>
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 16px; margin: 18px 0;">
            <div style="background: var(--bg); padding: 16px; border-radius: 8px; border: 1px solid var(--border);">
                <strong style="color: var(--primary);">⏱ Plazo de 30 días</strong>
                <p class="muted" style="margin: 6px 0 0; font-size: 0.9rem;">Cuentas con hasta 30 días calendario a partir de la fecha de entrega de tu pedido.</p>
            </div>
            <div style="background: var(--bg); padding: 16px; border-radius: 8px; border: 1px solid var(--border);">
                <strong style="color: var(--primary);">🏷 Estado del calzado</strong>
                <p class="muted" style="margin: 6px 0 0; font-size: 0.9rem;">El producto no debe tener señales de uso en exteriores y debe conservar su empaque original.</p>
            </div>
            <div style="background: var(--bg); padding: 16px; border-radius: 8px; border: 1px solid var(--border);">
                <strong style="color: var(--primary);">🧾 Comprobante</strong>
                <p class="muted" style="margin: 6px 0 0; font-size: 0.9rem;">Presentar tu número de pedido o boleta de pago generada en el sitio web.</p>
            </div>
        </div>
    </div>

    <div class="card" style="padding: 28px; border-radius: 12px; margin-bottom: 24px;">
        <h2 style="font-size: 1.35rem; color: #1e293b; margin-top: 0;">¿Cómo tramitar un cambio de talla o estilo?</h2>
        <ol style="color: var(--text); line-height: 1.9; padding-left: 20px; margin-bottom: 18px;">
            <li><strong>Contáctanos:</strong> Escríbenos a nuestro canal de soporte o WhatsApp con tu número de pedido y la nueva talla que requieres.</li>
            <li><strong>Revisión de existencias:</strong> Confirmaremos la disponibilidad inmediata del nuevo par en nuestra bodega.</li>
            <li><strong>Coordinación del intercambio:</strong> Podemos realizar el cambio directamente en tienda física o solicitar una guía de recolección a domicilio.</li>
        </ol>
        <p class="muted" style="font-size: 0.92rem; margin: 0;">
            * El primer cambio de talla no tiene costo por servicio. Cambios sucesivos o fuera de la cobertura estándar pueden conllevar tarifa de paquetería.
        </p>
    </div>

    <div style="text-align: center; margin-top: 20px;">
        <a href="<?= url('/Pags_info/contacto.php') ?>" class="btn">Solicitar Asistencia de Cambio</a>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>