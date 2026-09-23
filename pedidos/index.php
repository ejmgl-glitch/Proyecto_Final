<?php
// pedidos/index.php
require __DIR__ . '/../config/db.php';
require __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/functions.php';

requireLogin();

$role = currentRole();
$userId = (int)$_SESSION['user']['id'];
$esStaff = in_array($role, ['admin', 'trabajador'], true);

// Parámetros de navegación para el Staff
$verTodos = isset($_GET['ver']) && $_GET['ver'] === 'todos';
$idCliente = isset($_GET['id_cliente']) ? (int)$_GET['id_cliente'] : 0;

$pedidos = [];
$clientes = [];
$clienteSeleccionado = null;
$detallesPorPedido = [];

if ($esStaff) {
    if ($idCliente > 0) {
        // MODO 1: Pedidos filtrados de un cliente específico
        $stmtCli = $pdo->prepare('SELECT id, nombre, correo, telefono, direccion FROM usuario WHERE id = ?');
        $stmtCli->execute([$idCliente]);
        $clienteSeleccionado = $stmtCli->fetch();

        $stmt = $pdo->prepare(
            'SELECT p.*, u.nombre AS cliente_nombre, u.correo AS cliente_correo, u.telefono AS cliente_telefono
              FROM pedido p
              JOIN usuario u ON u.id = p.id_usuario
              WHERE p.id_usuario = ?
              ORDER BY p.fecha DESC, p.id DESC'
        );
        $stmt->execute([$idCliente]);
        $pedidos = $stmt->fetchAll();
        $pageTitle = 'Pedidos de ' . ($clienteSeleccionado ? $clienteSeleccionado['nombre'] : 'Cliente');

    } elseif ($verTodos) {
        // MODO 2: Ver todos los pedidos de todos los clientes
        $stmt = $pdo->query(
            'SELECT p.*, u.nombre AS cliente_nombre, u.correo AS cliente_correo, u.telefono AS cliente_telefono
              FROM pedido p
              JOIN usuario u ON u.id = p.id_usuario
              ORDER BY p.fecha DESC, p.id DESC'
        );
        $pedidos = $stmt->fetchAll();
        $pageTitle = 'Todos los Pedidos';

    } else {
        // MODO 3 (VISTA POR DEFECTO): Tarjetas con el resumen de cada cliente
        $stmt = $pdo->query(
            'SELECT 
                u.id AS cliente_id,
                u.nombre AS cliente_nombre,
                u.correo AS cliente_correo,
                u.telefono AS cliente_telefono,
                COUNT(p.id) AS total_pedidos,
                SUM(p.total) AS total_gastado,
                MAX(p.fecha) AS ultimo_pedido
             FROM usuario u
             INNER JOIN pedido p ON p.id_usuario = u.id
             GROUP BY u.id, u.nombre, u.correo, u.telefono
             ORDER BY total_pedidos DESC, u.nombre ASC'
        );
        $clientes = $stmt->fetchAll();
        $pageTitle = 'Gestión de Pedidos - Clientes';
    }
} else {
    // Vista de cliente común: solo sus propios pedidos
    $stmt = $pdo->prepare(
        'SELECT * FROM pedido
          WHERE id_usuario = ?
          ORDER BY fecha DESC, id DESC'
    );
    $stmt->execute([$userId]);
    $pedidos = $stmt->fetchAll();
    $pageTitle = 'Mis Pedidos';
}

// Calcular el número correlativo personal de cada cliente (#1 para su primera compra, #2 para la segunda, etc.)
if (!empty($pedidos)) {
    // 1. Contar cuántos pedidos totales tiene cada usuario dentro del conjunto
    $totalesPorUsuario = [];
    foreach ($pedidos as $p) {
        $uId = (int)$p['id_usuario'];
        $totalesPorUsuario[$uId] = ($totalesPorUsuario[$uId] ?? 0) + 1;
    }

    // 2. Al estar ordenados DESC por fecha/id, el primero es su compra N y desciende hasta 1
    $contadorPorUsuario = $totalesPorUsuario;
    foreach ($pedidos as &$ped) {
        $uId = (int)$ped['id_usuario'];
        $ped['numero_cliente'] = $contadorPorUsuario[$uId];
        $contadorPorUsuario[$uId]--;
    }
    unset($ped);
}

// Consultar detalles de los productos si hay pedidos cargados
if (!empty($pedidos)) {
    $pedidoIds = array_column($pedidos, 'id');
    $placeholders = implode(',', array_fill(0, count($pedidoIds), '?'));

    $stmtDetalle = $pdo->prepare(
        "SELECT dp.*, p.nombre, p.marca, p.imagen
          FROM detalle_pedido dp
          JOIN producto p ON p.id = dp.id_producto
          WHERE dp.id_pedido IN ($placeholders)"
    );
    $stmtDetalle->execute($pedidoIds);
    $detalles = $stmtDetalle->fetchAll();
    foreach ($detalles as $det) {
        $detallesPorPedido[$det['id_pedido']][] = $det;
    }
}

require __DIR__ . '/../includes/header.php';
?>

<div class="container" style="max-width: 950px; margin: 30px auto; padding: 0 15px;">

    <?php if (isset($_SESSION['flash'])): ?>
        <div class="flash flash-<?= h($_SESSION['flash']['type']) ?>">
            <?= h($_SESSION['flash']['msg']) ?>
        </div>
        <?php unset($_SESSION['flash']); ?>
    <?php endif; ?>

    <!-- ENCABEZADO Y ACCIONES -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; flex-wrap: wrap; gap: 12px;">
        <div>
            <h1 style="margin: 0; font-size: 1.8rem; font-weight: 800; color: #1e293b;">
                <?php if (!$esStaff): ?>
                    Mis Pedidos Realizados
                <?php elseif ($idCliente > 0): ?>
                    Pedidos de <?= h($clienteSeleccionado['nombre'] ?? 'Cliente') ?>
                <?php elseif ($verTodos): ?>
                    Todos los Pedidos de la Tienda
                <?php else: ?>
                    Gestión de Pedidos por Cliente
                <?php endif; ?>
            </h1>
            <?php if ($esStaff && !$verTodos && $idCliente === 0): ?>
                <p class="muted" style="margin: 5px 0 0 0; font-size: 0.95rem;">
                    Selecciona un cliente para ver sus compras o presiona el botón para listar todos los pedidos juntos.
                </p>
            <?php endif; ?>
        </div>

        <div style="display: flex; gap: 8px; flex-wrap: wrap; align-items: center;">
            <?php if ($esStaff): ?>
                <?php if ($idCliente > 0 || $verTodos): ?>
                    <a href="<?= url('/pedidos/index.php') ?>" class="btn btn-secondary btn-sm">
                        &larr; Ver Clientes (Tarjetas)
                    </a>
                <?php endif; ?>

                <?php if (!$verTodos): ?>
                    <a href="<?= url('/pedidos/index.php?ver=todos') ?>" class="btn btn-sm">
                        📦 Ver todos los pedidos
                    </a>
                <?php endif; ?>
            <?php else: ?>
                <a href="<?= url('/productos/index.php') ?>" class="btn btn-secondary btn-sm">&larr; Seguir comprando</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- ============================================================== -->
    <!-- VISTA 1: TARJETAS DE CLIENTES (VISTA PRINCIPAL STAFF)          -->
    <!-- ============================================================== -->
    <?php if ($esStaff && !$verTodos && $idCliente === 0): ?>
        <?php if (empty($clientes)): ?>
            <div class="card empty-state" style="text-align: center; padding: 50px 20px; background: #fff; border: 1px solid var(--border); border-radius: 10px;">
                <div style="font-size: 3rem; margin-bottom: 10px;">👥</div>
                <h2>No hay pedidos registrados</h2>
                <p class="muted">Aún ningún cliente ha realizado compras en la tienda.</p>
            </div>
        <?php else: ?>
            <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 18px;">
                <?php foreach ($clientes as $c): ?>
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.04); display: flex; flex-direction: column; justify-content: space-between;">
                        <div>
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px;">
                                <div style="width: 44px; height: 44px; border-radius: 50%; background: #fcefe7; color: var(--primary, #b3401f); display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.15rem; border: 1px solid #fed7aa;">
                                    <?= h(strtoupper(substr($c['cliente_nombre'] ?? 'C', 0, 1))) ?>
                                </div>
                                <span style="background: #f1f5f9; color: #1e293b; font-size: 0.85rem; font-weight: 700; padding: 4px 10px; border-radius: 999px; border: 1px solid #cbd5e1;">
                                    <?= (int)$c['total_pedidos'] ?> <?= (int)$c['total_pedidos'] === 1 ? 'pedido' : 'pedidos' ?>
                                </span>
                            </div>

                            <h3 style="margin: 0 0 6px 0; font-size: 1.15rem; color: #1e293b; font-weight: 700;">
                                <?= h($c['cliente_nombre']) ?>
                            </h3>

                            <div style="font-size: 0.88rem; color: #64748b; margin-bottom: 4px; word-break: break-all;">
                                ✉ <?= h($c['cliente_correo']) ?>
                            </div>

                            <?php if (!empty($c['cliente_telefono'])): ?>
                                <div style="font-size: 0.85rem; color: #64748b; margin-bottom: 6px;">
                                    📞 <?= h($c['cliente_telefono']) ?>
                                </div>
                            <?php endif; ?>

                            <div style="margin-top: 14px; padding-top: 10px; border-top: 1px dashed #e2e8f0; display: flex; justify-content: space-between; font-size: 0.88rem;">
                                <span class="muted">Total invertido:</span>
                                <strong style="color: var(--primary, #b3401f);">Q <?= number_format((float)$c['total_gastado'], 2) ?></strong>
                            </div>

                            <?php if (!empty($c['ultimo_pedido'])): ?>
                                <div style="font-size: 0.78rem; color: #94a3b8; margin-top: 4px;">
                                    Última compra: <?= date('d/m/Y H:i', strtotime($c['ultimo_pedido'])) ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div style="margin-top: 18px;">
                            <a href="<?= url('/pedidos/index.php?id_cliente=' . (int)$c['cliente_id']) ?>" 
                               class="btn" 
                               style="display: block; text-align: center; width: 100%; box-sizing: border-box; text-decoration: none;">
                                Ver pedidos (<?= (int)$c['total_pedidos'] ?>) &rarr;
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    <!-- ============================================================== -->
    <!-- VISTA 2: LISTADO DE PEDIDOS (CON AMBOS NÚMEROS DE PEDIDO)     -->
    <!-- ============================================================== -->
    <?php else: ?>
        <?php if ($esStaff && $clienteSeleccionado): ?>
            <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px 18px; margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                <div>
                    <strong style="font-size: 1rem; color: #1e293b;">Datos del cliente:</strong>
                    <div class="muted" style="font-size: 0.88rem; margin-top: 2px;">
                        <?= h($clienteSeleccionado['correo']) ?>
                        <?php if (!empty($clienteSeleccionado['telefono'])): ?> | Tel: <?= h($clienteSeleccionado['telefono']) ?><?php endif; ?>
                        <?php if (!empty($clienteSeleccionado['direccion'])): ?> | Dir: <?= h($clienteSeleccionado['direccion']) ?><?php endif; ?>
                    </div>
                </div>
                <span style="background: #f8fafc; border: 1px solid #cbd5e1; padding: 4px 10px; border-radius: 6px; font-size: 0.85rem; font-weight: 700; color: #334155;">
                    Total: <?= count($pedidos) ?> <?= count($pedidos) === 1 ? 'pedido' : 'pedidos' ?>
                </span>
            </div>
        <?php endif; ?>

        <?php if (empty($pedidos)): ?>
            <div class="card empty-state" style="text-align: center; padding: 50px 20px; background: #fff; border: 1px solid var(--border); border-radius: 10px;">
                <div style="font-size: 3rem; margin-bottom: 10px;">📦</div>
                <h2>No se encontraron pedidos</h2>
                <p class="muted">No hay pedidos disponibles para mostrar en este momento.</p>
            </div>
        <?php else: ?>
            <div style="display: flex; flex-direction: column; gap: 24px;">
                <?php foreach ($pedidos as $pedido): ?>
                    <div style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 24px; box-shadow: 0 2px 4px rgba(0,0,0,0.04);">
                        
                        <!-- ENCABEZADO DEL PEDIDO: NÚMERO GENERAL Y NÚMERO PERSONAL -->
                        <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 10px;">
                            <div>
                                <div style="display: flex; align-items: center; gap: 10px; flex-wrap: wrap;">
                                    <!-- Número General del Pedido -->
                                    <h2 style="margin: 0; font-size: 1.3rem; font-weight: 800; color: #1e293b;">
                                        <?= $esStaff ? 'Pedido #' . (int)$pedido['id'] : 'Pedido #' . (int)$pedido['numero_cliente'] ?>
                                    </h2>

                                    <!-- Número de Pedido Personal del Cliente (Visible para Admin y Empleados) -->
                                    <?php if ($esStaff && isset($pedido['numero_cliente'])): ?>
                                        <span style="background: #e0f2fe; color: #0284c7; font-weight: 700; font-size: 0.82rem; padding: 3px 10px; border-radius: 999px; border: 1px solid #bae6fd; display: inline-flex; align-items: center; gap: 4px;" title="Indica qué número de compra representa este pedido para el cliente">
                                            👤 Pedido personal del cliente: <strong>#<?= (int)$pedido['numero_cliente'] ?></strong>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <?php if (!$esStaff): ?>
                                    <div style="font-size: 0.8rem; color: #94a3b8; margin-top: 2px;">
                                        Identificador de orden: #<?= (int)$pedido['id'] ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($esStaff): ?>
                                <form method="post" action="<?= url('/pedidos/cambiar_estado.php') ?>" style="display: flex; align-items: center; gap: 8px; margin: 0;">
                                    <input type="hidden" name="pedido_id" value="<?= (int)$pedido['id'] ?>">
                                    <select name="estado" style="width: auto; padding: 5px 8px; font-size: 0.85rem; border: 1px solid #cbd5e1; border-radius: 6px;">
                                        <option value="realizado" <?= $pedido['estado'] === 'realizado' ? 'selected' : '' ?>>Realizado</option>
                                        <option value="enviado" <?= $pedido['estado'] === 'enviado' ? 'selected' : '' ?>>Enviado</option>
                                        <option value="entregado" <?= $pedido['estado'] === 'entregado' ? 'selected' : '' ?>>Entregado</option>
                                    </select>
                                    <button type="submit" class="btn btn-sm">Actualizar</button>
                                </form>
                            <?php else: ?>
                                <span style="background: #f1f5f9; color: #475569; font-weight: 700; text-transform: uppercase; font-size: 0.75rem; letter-spacing: 0.05em; padding: 4px 10px; border-radius: 999px; border: 1px solid #e2e8f0;">
                                    <?= h($pedido['estado']) ?>
                                </span>
                            <?php endif; ?>
                        </div>

                        <?php if ($esStaff): ?>
                            <div style="margin-top: 6px; font-size: 0.88rem; color: #64748b;">
                                <strong>Cliente:</strong> <?= h($pedido['cliente_nombre']) ?> (<?= h($pedido['cliente_correo']) ?>)
                                <?php if (!empty($pedido['cliente_telefono'])): ?>
                                    | Tel: <?= h($pedido['cliente_telefono']) ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <div style="font-size: 0.88rem; color: #64748b; margin-top: 4px; margin-bottom: 16px;">
                            Fecha: <?= h(date('d/m/Y H:i', strtotime($pedido['fecha']))) ?>
                        </div>

                        <!-- PRODUCTOS COMPRADOS -->
                        <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 14px; margin-bottom: 16px;">
                            <?php 
                            $items = $detallesPorPedido[$pedido['id']] ?? [];
                            foreach ($items as $idxItem => $item): 
                                $cant = max(1, (int)$item['cantidad']);
                                $precioUnitario = (float)($item['subtotal'] / $cant);
                            ?>
                                <div style="display: flex; justify-content: space-between; align-items: center; gap: 12px; <?= $idxItem > 0 ? 'margin-top: 12px; padding-top: 12px; border-top: 1px dashed #cbd5e1;' : '' ?>">
                                    <div style="display: flex; align-items: center; gap: 14px;">
                                        <?php if (!empty($item['imagen'])): ?>
                                            <img src="<?= h($item['imagen']) ?>" alt="<?= h($item['nombre']) ?>" style="width: 55px; height: 55px; object-fit: cover; border-radius: 8px; border: 1px solid #e2e8f0;">
                                        <?php else: ?>
                                            <div style="width: 55px; height: 55px; background: #e2e8f0; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem;">👟</div>
                                        <?php endif; ?>
                                        <div>
                                            <div style="font-weight: 700; font-size: 0.98rem; color: #0f172a;"><?= h($item['nombre']) ?></div>
                                            <div style="font-size: 0.85rem; color: #64748b; margin-top: 2px;">
                                                <?= h($item['marca']) ?> &bull; Cantidad: <strong><?= $cant ?></strong>
                                            </div>
                                        </div>
                                    </div>
                                    <div style="text-align: right;">
                                        <div style="font-size: 0.8rem; color: #64748b;">Precio unitario:</div>
                                        <div style="font-weight: 700; font-size: 1rem; color: #0f172a;">
                                            Q <?= number_format($precioUnitario, 2) ?>
                                        </div>
                                        <div style="font-size: 0.78rem; color: #94a3b8; margin-top: 2px;">
                                            Subtotal: Q <?= number_format((float)$item['subtotal'], 2) ?>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <!-- TOTAL Y MÉTODO DE PAGO -->
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                            <span style="font-size: 1rem; font-weight: 600; color: #334155;">Total del pedido:</span>
                            <span style="font-size: 1.35rem; font-weight: 800; color: var(--primary, #b3401f);">
                                Q <?= number_format((float)$pedido['total'], 2) ?>
                            </span>
                        </div>

                        <div style="font-size: 0.9rem; color: #64748b;">
                            <strong>Método de pago:</strong> 
                            <span style="text-transform: capitalize; color: #1e293b; font-weight: 600;">
                                <?= h($pedido['metodo_pago']) ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>

</div>

<?php require __DIR__ . '/../includes/footer.php'; ?>