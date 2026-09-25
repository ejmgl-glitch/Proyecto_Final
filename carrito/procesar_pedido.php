<?php
// carrito/procesar_pedido.php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

// Cargar autoload de Composer para PHPMailer
if (file_exists(__DIR__ . '/../vendor/autoload.php')) {
    require_once __DIR__ . '/../vendor/autoload.php';
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 1. Validar que el usuario esté autenticado y sea cliente
if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'Debes iniciar sesión para comprar.']);
    exit;
}

if (currentRole() !== 'cliente') {
    http_response_code(403);
    echo json_encode([
        'success' => false,
        'error'   => 'Solo los usuarios con rol "cliente" pueden realizar compras.'
    ]);
    exit;
}

// 2. Leer payload JSON enviado por React
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || empty($input['items'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'El carrito está vacío o la solicitud es inválida.']);
    exit;
}

$items = $input['items'];
$metodosValidos = ['tarjeta', 'pay pal', 'transferencia'];
$metodoPago = in_array($input['metodo_pago'] ?? '', $metodosValidos, true) ? $input['metodo_pago'] : 'tarjeta';
$userId = (int)$_SESSION['user']['id'];

try {
    $pdo->beginTransaction();

    $totalCalculado = 0.00;
    $detallesAInsertar = [];
    $itemsParaCorreo = [];

    // Bloqueamos cada variante para evitar sobreventas concurrentes
    $stmtVariante = $pdo->prepare(
        'SELECT vp.id, vp.talla, vp.stock, vp.id_producto, p.nombre, p.marca, p.precio
         FROM variante_producto vp
         JOIN producto p ON p.id = vp.id_producto
         WHERE vp.id = ?
         FOR UPDATE'
    );

    foreach ($items as $item) {
        $idVariante = (int)($item['id_variante'] ?? 0);
        $cantidad = max(1, (int)($item['cantidad'] ?? 1));

        if (!$idVariante) {
            throw new Exception('Falta indicar la talla de uno de los productos.');
        }

        $stmtVariante->execute([$idVariante]);
        $variante = $stmtVariante->fetch();

        if (!$variante) {
            throw new Exception("La talla seleccionada ya no está disponible.");
        }

        if ((int)$variante['stock'] < $cantidad) {
            throw new Exception(
                "Ya no hay stock suficiente de \"{$variante['nombre']}\" talla {$variante['talla']}. " .
                "Disponible: {$variante['stock']}."
            );
        }

        $precioDb = (float)$variante['precio'];
        $subtotal = $precioDb * $cantidad;
        $totalCalculado += $subtotal;

        $detallesAInsertar[] = [
            'id_producto' => (int)$variante['id_producto'],
            'id_variante' => $idVariante,
            'talla'       => $variante['talla'],
            'cantidad'    => $cantidad,
            'subtotal'    => $subtotal,
        ];

        $itemsParaCorreo[] = [
            'nombre'   => $variante['nombre'],
            'marca'    => $variante['marca'] ?? '',
            'talla'    => $variante['talla'],
            'cantidad' => $cantidad,
            'precio'   => $precioDb,
            'subtotal' => $subtotal,
        ];
    }

    // 3. Crear el registro en `pedido`
    $stmtPedido = $pdo->prepare(
        'INSERT INTO pedido (id_usuario, fecha, total, estado, metodo_pago)
         VALUES (?, NOW(), ?, "realizado", ?)'
    );
    $stmtPedido->execute([$userId, $totalCalculado, $metodoPago]);
    $idPedido = (int)$pdo->lastInsertId();

    // 4. Crear los registros en `detalle_pedido` y descontar el stock
    $stmtDetalle =$pdo->prepare(
        'INSERT INTO detalle_pedido (id_pedido, id_producto, id_variante, talla, cantidad, subtotal)
         VALUES (?, ?, ?, ?, ?, ?)'
    );

    $stmtDescontar =$pdo->prepare(
        'UPDATE variante_producto SET stock = stock - ? WHERE id = ?'
    );

    foreach ($detallesAInsertar as $det) {$stmtDetalle->execute([
            $idPedido,$det['id_producto'], $det['id_variante'],$det['talla'],
            $det['cantidad'],$det['subtotal']
        ]);
        $stmtDescontar->execute([$det['cantidad'],$det['id_variante']]);
    }

    // Confirmamos la transacción en base de datos
    $pdo->commit();

    // 5. Obtener información actualizada del cliente para el correo
    $stmtUser =$pdo->prepare('SELECT nombre, correo FROM usuario WHERE id = ?');
    $stmtUser->execute([$userId]);
    $clienteInfo =$stmtUser->fetch();

    $clienteNombre =$clienteInfo['nombre'] ?? $_SESSION['user']['nombre'] ?? 'Cliente';$clienteCorreo = $clienteInfo['correo'] ?? $_SESSION['user']['correo'] ?? '';

    // 6. Envío de correo mediante PHPMailer
    $correoEnviado = false;
    $correoError = null;

    if (filter_var($clienteCorreo, FILTER_VALIDATE_EMAIL) && class_exists('PHPMailer\PHPMailer\PHPMailer')) {
        try {
            $mailConfig = require __DIR__ . '/../config/mail.php';

            $mail = new PHPMailer(true);$mail->isSMTP();
            $mail->CharSet    = 'UTF-8';$mail->Host       = $mailConfig['host'];$mail->SMTPAuth   = $mailConfig['smtp_auth'];$mail->Username   = $mailConfig['username'];$mail->Password   = $mailConfig['password'];$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = (int)$mailConfig['port'];

            $mail->setFrom($mailConfig['from_email'], $mailConfig['from_name']);$mail->addAddress($clienteCorreo,$clienteNombre);

            $mail->isHTML(true);$mail->Subject = "Confirmación de Pedido #{$idPedido} - Paso Chilero";

            // Construir tabla HTML de productos
            $filasHtml = '';$textoPlano = '';
            foreach ($itemsParaCorreo as$p) {
                $nombreEsc   = htmlspecialchars($p['nombre'], ENT_QUOTES, 'UTF-8');
                $marcaEsc    = htmlspecialchars($p['marca'], ENT_QUOTES, 'UTF-8');
                $tallaEsc    = htmlspecialchars($p['talla'], ENT_QUOTES, 'UTF-8');
                $cant        = (int)$p['cantidad'];
                $precioUnit  = number_format($p['precio'], 2);
                $subtotalFmt = number_format($p['subtotal'], 2);

                $filasHtml .= "
                <tr>
                    <td style='padding: 10px; border-bottom: 1px solid #e5e1db; color: #26221f;'>
                        <strong>{$nombreEsc}</strong><br>
                        <span style='color: #78716c; font-size: 13px;'>Marca: {$marcaEsc} | Talla: US {$tallaEsc}</span>
                    </td>
                    <td style='padding: 10px; border-bottom: 1px solid #e5e1db; text-align: center; color: #26221f;'>{$cant}</td>
                    <td style='padding: 10px; border-bottom: 1px solid #e5e1db; text-align: right; color: #26221f;'>Q {$precioUnit}</td>
                    <td style='padding: 10px; border-bottom: 1px solid #e5e1db; text-align: right; font-weight: bold; color: #26221f;'>Q {$subtotalFmt}</td>
                </tr>";

                $textoPlano .= "- {$p['nombre']} (Talla: {$p['talla']}) x{$cant} = Q {$subtotalFmt}\n";
            }

            $totalFormateado = number_format($totalCalculado, 2);$metodoPagoTexto = ucfirst($metodoPago);$fechaActual     = date('d/m/Y H:i');

            // Plantilla HTML del correo
            $mail->Body = "
            <div style='background-color: #f7f5f2; font-family: -apple-system, BlinkMacSystemFont, Segoe UI, Roboto, sans-serif; padding: 30px 15px;'>
                <div style='max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 12px; border: 1px solid #e5e1db; overflow: hidden; box-shadow: 0 4px 12px rgba(0,0,0,0.05);'>
                    <div style='background-color: #b3401f; padding: 24px; text-align: center;'>
                        <h1 style='color: #ffffff; margin: 0; font-size: 24px; font-weight: 800;'>Paso Chilero</h1>
                        <p style='color: #fcefe7; margin: 6px 0 0 0; font-size: 14px;'>¡Confirmación de Pedido Recibido!</p>
                    </div>
                    <div style='padding: 24px 28px;'>
                        <p style='font-size: 16px; color: #26221f;'>Hola <strong>" . htmlspecialchars($clienteNombre, ENT_QUOTES, 'UTF-8') . "</strong>,</p>
                        <p style='color: #4b5563; font-size: 14px; line-height: 1.5;'>
                            Hemos recibido con éxito tu orden. A continuación tienes el comprobante con los detalles de tu compra:
                        </p>
                        
                        <div style='background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 14px; margin: 18px 0;'>
                            <p style='margin: 3px 0; font-size: 14px;'><strong>Número de Pedido:</strong> #{$idPedido}</p>
                            <p style='margin: 3px 0; font-size: 14px;'><strong>Fecha:</strong> {$fechaActual}</p>
                            <p style='margin: 3px 0; font-size: 14px;'><strong>Método de Pago:</strong> {$metodoPagoTexto}</p>
                            <p style='margin: 3px 0; font-size: 14px;'><strong>Estado:</strong> Realizado (En preparación)</p>
                        </div>

                        <table style='width: 100%; border-collapse: collapse; margin-top: 15px; font-size: 14px;'>
                            <thead>
                                <tr style='background-color: #f1ede7; text-align: left; color: #4b5563;'>
                                    <th style='padding: 10px;'>Producto</th>
                                    <th style='padding: 10px; text-align: center;'>Cant.</th>
                                    <th style='padding: 10px; text-align: right;'>Precio</th>
                                    <th style='padding: 10px; text-align: right;'>Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                {$filasHtml}
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colspan='3' style='padding: 14px 10px; text-align: right; font-weight: bold; font-size: 15px;'>Total Pagado:</td>
                                    <td style='padding: 14px 10px; text-align: right; font-weight: 800; font-size: 17px; color: #b3401f;'>Q {$totalFormateado}</td>
                                </tr>
                            </tfoot>
                        </table>

                        <p style='color: #78716c; font-size: 13px; margin-top: 25px; line-height: 1.5;'>
                            Nos pondremos en contacto contigo para coordinar el despacho. Si tienes alguna duda, puedes responder a este correo o escribir a nuestro WhatsApp de atención.
                        </p>
                    </div>
                    <div style='background-color: #f7f5f2; border-top: 1px solid #e5e1db; padding: 16px; text-align: center; font-size: 12px; color: #78716c;'>
                        Paso Chilero &bull; Calzado que va con tu estilo &bull; Guatemala
                    </div>
                </div>
            </div>";

            $mail->AltBody = "¡Gracias por tu compra en Paso Chilero, {$clienteNombre}!\n\n" .
                             "Tu pedido #{$idPedido} ha sido registrado exitosamente.\n" .
                             "Fecha: {$fechaActual}\n" .
                             "Método de pago: {$metodoPagoTexto}\n\n" .
                             "Detalle de productos:\n{$textoPlano}\n" .
                             "Total: Q {$totalFormateado}\n\n" .
                             "Nos comunicaremos pronto para coordinar el envío.";

            $mail->send();$correoEnviado = true;
        } catch (Exception $e) {$correoEnviado = false;
            $correoError =$e->getMessage();
        }
    }

    echo json_encode([
        'success'        => true,
        'mensaje'        => '¡Tu pedido fue realizado con éxito!',
        'pedido_id'      => $idPedido,
        'total'          => number_format($totalCalculado, 2),         'correo'         =>$clienteCorreo,
        'correo_enviado' => $correoEnviado,
        'correo_error'   => $correoError
    ]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {$pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
}