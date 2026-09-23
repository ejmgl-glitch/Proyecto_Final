<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$mensajeEnviado = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Proceso informativo de recepción de consulta
    $mensajeEnviado = true;
}

$page_title = 'Contacto y Soporte | Paso Chilero';
require __DIR__ . '/../includes/header.php';
?>

<div class="container" style="max-width: 960px; margin: 35px auto; padding: 0 20px;">
    <div style="margin-bottom: 28px;">
        <span class="eyebrow">Estamos para servirte</span>
        <h1 style="font-size: 2.2rem; font-weight: 800; color: #1e293b; margin: 6px 0 10px 0;">Contáctanos</h1>
        <p class="muted" style="font-size: 1rem; margin: 0;">Escríbenos o visítanos. Te ayudaremos a resolver cualquier duda sobre tus compras o pedidos.</p>
    </div>

    <?php if ($mensajeEnviado): ?>
        <div class="flash flash-ok" style="margin-bottom: 24px;">
            ✓ ¡Gracias por tu mensaje! Nuestro equipo de atención se pondrá en contacto contigo a la brevedad.
        </div>
    <?php endif; ?>

    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); gap: 24px; align-items: start;">
        <!-- Tarjeta de Información de Contacto -->
        <div class="card" style="padding: 26px; border-radius: 12px; margin: 0;">
            <h2 style="font-size: 1.35rem; color: #1e293b; margin-top: 0; margin-bottom: 18px;">Información de Atención</h2>
            
            <div style="margin-bottom: 18px;">
                <strong style="display: block; color: #1e293b; font-size: 0.95rem;">📍 Ubicación Central</strong>
                <p class="muted" style="margin: 4px 0 0 0; line-height: 1.5;">21 Avenida 4-32 Zona 11, Miraflores, Ciudad de Guatemala</p>
            </div>

            <div style="margin-bottom: 18px;">
                <strong style="display: block; color: #1e293b; font-size: 0.95rem;">📞 Teléfono & WhatsApp</strong>
                <p class="muted" style="margin: 4px 0 0 0;">+502 5551-2345 / +502 5559-8765</p>
            </div>

            <div style="margin-bottom: 18px;">
                <strong style="display: block; color: #1e293b; font-size: 0.95rem;">✉️ Correo Electrónico</strong>
                <p class="muted" style="margin: 4px 0 0 0;">soporte@pasochilero.com.gt</p>
            </div>

            <div style="margin-bottom: 0;">
                <strong style="display: block; color: #1e293b; font-size: 0.95rem;">⏰ Horario de Atención</strong>
                <p class="muted" style="margin: 4px 0 0 0;">Lunes a Sábado: 9:00 AM – 6:00 PM</p>
            </div>
        </div>

        <!-- Formulario de Mensaje -->
        <div class="card" style="padding: 26px; border-radius: 12px; margin: 0;">
            <h2 style="font-size: 1.35rem; color: #1e293b; margin-top: 0; margin-bottom: 18px;">Envíanos un mensaje</h2>
            <form method="post" class="form-grid" style="max-width: 100%;">
                <div>
                    <label>Nombre Completo</label>
                    <input type="text" name="nombre" required placeholder="Tu nombre" value="<?= h($_SESSION['user']['nombre'] ?? '') ?>">
                </div>
                <div>
                    <label>Correo Electrónico</label>
                    <input type="email" name="correo" required placeholder="tu@correo.com" value="<?= h($_SESSION['user']['correo'] ?? '') ?>">
                </div>
                <div>
                    <label>Asunto o Motivo</label>
                    <select name="asunto" required>
                        <option value="duda_pedido">Consulta sobre un pedido</option>
                        <option value="cambio_talla">Cambio de talla o producto</option>
                        <option value="informacion">Información sobre calzado</option>
                        <option value="otro">Otro asunto</option>
                    </select>
                </div>
                <div>
                    <label>Mensaje</label>
                    <textarea name="mensaje" required placeholder="¿En qué podemos ayudarte?" style="min-height: 90px;"></textarea>
                </div>
                <div>
                    <button type="submit" class="btn" style="width: 100%;">Enviar Consulta</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>