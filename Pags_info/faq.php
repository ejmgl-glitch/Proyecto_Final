<?php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

$page_title = 'Preguntas Frecuentes | Paso Chilero';
require __DIR__ . '/../includes/header.php';
?>

<div class="container" style="max-width: 900px; margin: 35px auto; padding: 0 20px;">
    <div style="margin-bottom: 28px; text-align: center;">
        <span class="eyebrow">Centro de Ayuda</span>
        <h1 style="font-size: 2.2rem; font-weight: 800; color: #1e293b; margin: 6px 0 10px 0;">Preguntas Frecuentes (FAQ)</h1>
        <p class="muted" style="font-size: 1rem; margin: 0;">Encuentra respuestas rápidas a las consultas más usuales sobre tus compras en Paso Chilero.</p>
    </div>

    <div style="display: flex; flex-direction: column; gap: 16px;">
        <div class="card" style="padding: 22px; border-radius: 12px; margin: 0;">
            <h3 style="margin: 0 0 8px 0; color: #1e293b; font-size: 1.15rem;">¿Cómo sé cuál es mi talla correcta de calzado?</h3>
            <p class="muted" style="margin: 0; line-height: 1.6;">
                Nuestros productos manejan tallaje estándar guatemalteco / internacional. En cada producto puedes verificar las características y tallas en existencia. Si estás entre dos tallas, te recomendamos seleccionar la superior para mayor comodidad.
            </p>
        </div>

        <div class="card" style="padding: 22px; border-radius: 12px; margin: 0;">
            <h3 style="margin: 0 0 8px 0; color: #1e293b; font-size: 1.15rem;">¿Cuáles son las formas de pago aceptadas?</h3>
            <p class="muted" style="margin: 0; line-height: 1.6;">
                Aceptamos <strong>tarjeta de crédito y débito</strong> (Visa y Mastercard), pagos mediante <strong>PayPal</strong> y <strong>transferencia bancaria</strong> directa a nuestras cuentas en Guatemala.
            </p>
        </div>

        <div class="card" style="padding: 22px; border-radius: 12px; margin: 0;">
            <h3 style="margin: 0 0 8px 0; color: #1e293b; font-size: 1.15rem;">¿Cuentan con tienda física para probarme los zapatos?</h3>
            <p class="muted" style="margin: 0; line-height: 1.6;">
                ¡Sí! Nuestra sede física se encuentra en el área de Miraflores, Ciudad de Guatemala. Atendemos de lunes a sábado de 9:00 a 18:00 horas, donde podrás ver todos los modelos exhibidos.
            </p>
        </div>

        <div class="card" style="padding: 22px; border-radius: 12px; margin: 0;">
            <h3 style="margin: 0 0 8px 0; color: #1e293b; font-size: 1.15rem;">¿Puedo cancelar o modificar un pedido ya realizado?</h3>
            <p class="muted" style="margin: 0; line-height: 1.6;">
                Si necesitas modificar la dirección o talla de un pedido con estado <em>"Realizado"</em>, comunícate con nosotros de inmediato con tu número de orden antes de que pase al estado <em>"Enviado"</em>.
            </p>
        </div>

        <div class="card" style="padding: 22px; border-radius: 12px; margin: 0;">
            <h3 style="margin: 0 0 8px 0; color: #1e293b; font-size: 1.15rem;">¿Los productos son 100% originales y nuevos?</h3>
            <p class="muted" style="margin: 0; line-height: 1.6;">
                Totalmente. En Paso Chilero seleccionamos calzado original de calidad garantizada para ofrecerte estilo, durabilidad y confort en cada pisada.
            </p>
        </div>
    </div>

    <div class="card" style="text-align: center; margin-top: 30px; padding: 30px; border-radius: 12px;">
        <h3 style="margin-top: 0; color: #1e293b;">¿No encontraste la respuesta que buscabas?</h3>
        <p class="muted" style="margin-bottom: 20px;">Nuestro equipo de atención al cliente está listo para resolver cualquier duda.</p>
        <a href="<?= url('/Pags_info/contacto.php') ?>" class="btn">Hablar con un asesor</a>
    </div>
</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>