const { useState, useEffect } = React;

const Carrito = ({ user, baseUrl = '/' }) => {
    const [items, setItems] = useState([]);
    const [metodoPago, setMetodoPago] = useState('tarjeta');
    const [cargando, setCargando] = useState(false);
    const [mensaje, setMensaje] = useState(null); // { tipo: 'ok' | 'error', texto: '' }
    const [compraExitosa, setCompraExitosa] = useState(null);

    const cleanBase = baseUrl.endsWith('/') ? baseUrl : baseUrl + '/';
    const cartKey = user && user.id ? `chilero_carrito_${user.id}` : 'chilero_carrito_guest';

    useEffect(() => {
        try {
            const guardado = localStorage.getItem(cartKey);
            if (guardado) {
                setItems(JSON.parse(guardado));
            } else {
                setItems([]);
            }
        } catch (e) {
            console.error("Error al cargar carrito:", e);
        }
    }, [user]);

    // Guardar cambios en localStorage
    const actualizarStorage = (nuevosItems) => {
        setItems(nuevosItems);
        localStorage.setItem(cartKey, JSON.stringify(nuevosItems));
        window.dispatchEvent(new Event('carrito_actualizado'));
    };

    const cambiarCantidad = (idVariante, delta) => {
        const actualizados = items.map(item => {
            if (item.id_variante === idVariante) {
                const tope = item.stockMax ?? Infinity;
                const nuevaCant = Math.min(item.cantidad + delta, tope);
                return nuevaCant > 0 ? { ...item, cantidad: nuevaCant } : null;
            }
            return item;
        }).filter(Boolean);
        actualizarStorage(actualizados);
    };

    const eliminarItem = (idVariante) => {
        const actualizados = items.filter(item => item.id_variante !== idVariante);
        actualizarStorage(actualizados);
    };

    const vaciarCarrito = () => {
        if (confirm('¿Deseas vaciar todo el carrito?')) {
            actualizarStorage([]);
        }
    };

    const total = items.reduce((acc, i) => acc + (parseFloat(i.precio) * i.cantidad), 0);

    // Enviar pedido al backend
    const realizarPedido = async () => {
        setMensaje(null);

        // Verificaciones
        if (!user) {
            setMensaje({ tipo: 'error', texto: 'Debes iniciar sesión para realizar la compra.' });
            return;
        }

        const rolUsuario = user.tipo_usuario || user.rol || user.role;
        if (rolUsuario !== 'cliente') {
            setMensaje({ 
                tipo: 'error', 
                texto: `Solo los usuarios con rol 'cliente' pueden comprar. Tu rol actual es '${rolUsuario}'.` 
            });
            return;
        }

        if (items.length === 0) {
            setMensaje({ tipo: 'error', texto: 'Tu carrito está vacío.' });
            return;
        }

        setCargando(true);

        try {
            const resp = await fetch(`${cleanBase}carrito/procesar_pedido.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    items: items.map(i => ({
                        id_variante: i.id_variante,
                        cantidad: i.cantidad
                    })),
                    metodo_pago: metodoPago
                })
            });

            const data = await resp.json();

            if (!resp.ok || !data.success) {
                throw new Error(data.error || 'Error al procesar la compra.');
            }

            actualizarStorage([]);
            setCompraExitosa({
                pedidoId: data.pedido_id,
                total: data.total,
                correo: data.correo || user.correo,
                correoEnviado: data.correo_enviado
            });

        } catch (err) {
            setMensaje({ tipo: 'error', texto: err.message });
        } finally {
            setCargando(false);
        }
    };

    // Vista si ya completó el pedido
    if (compraExitosa) {
        return (
            <div className="card text-center" style={{ padding: '40px 20px', textAlign: 'center' }}>
                <div style={{ fontSize: '3.5rem', color: 'var(--ok, #1f7a3d)', marginBottom: '10px' }}>✓</div>
                <h1 style={{ color: 'var(--ok, #1f7a3d)', marginTop: '0', fontSize: '1.9rem' }}>¡Gracias por tu compra!</h1>
                <p style={{ fontSize: '1.1rem', margin: '10px 0' }}>
                    Tu orden ha sido registrada con el número de pedido <strong>#{compraExitosa.pedidoId}</strong>.
                </p>
                <p className="price" style={{ fontSize: '1.45rem', margin: '14px 0' }}>Total pagado: Q {compraExitosa.total}</p>

                {compraExitosa.correoEnviado ? (
                    <div style={{ 
                        background: '#e2f3e6', 
                        color: 'var(--ok, #1f7a3d)', 
                        border: '1px solid #b9dfc2', 
                        borderRadius: '8px', 
                        padding: '14px 18px', 
                        maxWidth: '520px', 
                        margin: '20px auto', 
                        fontSize: '0.95rem' 
                    }}>
                        ✉ Hemos enviado un correo de confirmación con el detalle de tus productos a: <strong>{compraExitosa.correo}</strong>
                    </div>
                ) : (
                    <p className="muted" style={{ maxWidth: '480px', margin: '15px auto' }}>
                        Tu pedido fue guardado exitosamente. Nos pondremos en contacto contigo para coordinar la entrega.
                    </p>
                )}

                <div style={{ marginTop: '25px', display: 'flex', gap: '10px', justifyContent: 'center', flexWrap: 'wrap' }}>
                    <a href={`${cleanBase}pedidos/index.php`} className="btn">Ver Mis Pedidos</a>
                    <a href={`${cleanBase}productos/index.php`} className="btn btn-secondary">Seguir explorando productos</a>
                </div>
            </div>
        );
    }

    const rol = user ? (user.tipo_usuario || user.rol || user.role) : null;
    const esCliente = rol === 'cliente';

    return (
        <div className="carrito-wrapper">
            <h1>Tu Carrito de Compras</h1>

            {mensaje && (
                <div className={`flash flash-${mensaje.tipo}`}>
                    {mensaje.texto}
                </div>
            )}

            {items.length === 0 ? (
                <div className="card empty-state">
                    <p style={{ fontSize: '1.2rem', marginBottom: '15px' }}>Tu carrito está vacío actualmente.</p>
                    <a href={`${cleanBase}productos/index.php`} className="btn">Ir a ver zapatos</a>
                </div>
            ) : (
                <div className="carrito-grid">
                    {/* Lista de productos */}
                    <div className="carrito-items-card card">
                        <table className="carrito-table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Talla</th>
                                    <th>Precio</th>
                                    <th>Cantidad</th>
                                    <th>Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                {items.map((item) => (
                                    <tr key={item.id_variante}>
                                        <td style={{ display: 'flex', alignItems: 'center', gap: '12px' }}>
                                            {item.imagen && (
                                                <img 
                                                    src={item.imagen} 
                                                    alt={item.nombre} 
                                                    style={{ width: '50px', height: '50px', objectFit: 'cover', borderRadius: '6px' }} 
                                                />
                                            )}
                                            <div>
                                                <strong>{item.nombre}</strong>
                                                {item.marca && <div className="muted">{item.marca}</div>}
                                            </div>
                                        </td>
                                        <td>US {item.talla}</td>
                                        <td>Q {parseFloat(item.precio).toFixed(2)}</td>
                                        <td>
                                            <div className="qty-control">
                                                <button onClick={() => cambiarCantidad(item.id_variante, -1)} className="btn-qty">-</button>
                                                <span>{item.cantidad}</span>
                                                <button 
                                                    onClick={() => cambiarCantidad(item.id_variante, 1)} 
                                                    className="btn-qty"
                                                    disabled={item.stockMax != null && item.cantidad >= item.stockMax}
                                                >+</button>
                                            </div>
                                        </td>
                                        <td style={{ fontWeight: 'bold' }}>
                                            Q {(parseFloat(item.precio) * item.cantidad).toFixed(2)}
                                        </td>
                                        <td>
                                            <button 
                                                onClick={() => eliminarItem(item.id_variante)} 
                                                className="btn-remove"
                                                title="Eliminar producto"
                                            >
                                                ✕
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>

                        <div style={{ marginTop: '16px', display: 'flex', justifyContent: 'space-between', alignItems: 'center' }}>
                            <button onClick={vaciarCarrito} className="btn btn-secondary btn-sm">Vaciar Carrito</button>
                            <a href={`${cleanBase}productos/index.php`} className="btn btn-secondary btn-sm">← Continuar Comprando</a>
                        </div>
                    </div>

                    {/* Resumen de orden y checkout */}
                    <div className="carrito-summary card">
                        <h2>Resumen del Pedido</h2>
                        
                        <div className="summary-row">
                            <span>Artículos:</span>
                            <span>{items.reduce((acc, i) => acc + i.cantidad, 0)}</span>
                        </div>
                        
                        <div className="summary-row summary-total">
                            <span>Total a pagar:</span>
                            <span className="price">Q {total.toFixed(2)}</span>
                        </div>

                        {/* Selección de método de pago */}
                        <div style={{ marginTop: '20px' }}>
                            <label><strong>Método de Pago:</strong></label>
                            <select 
                                value={metodoPago} 
                                onChange={(e) => setMetodoPago(e.target.value)}
                                style={{ marginTop: '6px' }}
                            >
                                <option value="tarjeta">Tarjeta de Crédito / Débito</option>
                                <option value="pay pal">PayPal</option>
                                <option value="transferencia">Transferencia Bancaria</option>
                            </select>
                        </div>

                        {/* Control de roles de usuario */}
                        <div style={{ marginTop: '25px' }}>
                            {!user ? (
                                <div>
                                    <div className="flash flash-error" style={{ fontSize: '0.85rem' }}>
                                        Inicia sesión para poder tramitar la compra.
                                    </div>
                                    <a href={`${cleanBase}auth/login.php`} className="btn" style={{ width: '100%', textAlign: 'center' }}>
                                        Iniciar Sesión
                                    </a>
                                </div>
                            ) : !esCliente ? (
                                <div>
                                    <div className="flash flash-error" style={{ fontSize: '0.85rem' }}>
                                        Tu usuario tiene rol <strong>{rol}</strong>. Solo los usuarios con rol <strong>cliente</strong> pueden procesar pedidos.
                                    </div>
                                    <button className="btn" style={{ width: '100%', opacity: 0.5, cursor: 'not-allowed' }} disabled>
                                        Compra no permitida
                                    </button>
                                </div>
                            ) : (
                                <button 
                                    onClick={realizarPedido} 
                                    className="btn" 
                                    style={{ width: '100%', padding: '12px', fontSize: '1rem' }}
                                    disabled={cargando}
                                >
                                    {cargando ? 'Procesando pedido y enviando correo...' : 'Confirmar y Pagar'}
                                </button>
                            )}
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

// Renderizado en el DOM
const carritoRoot = document.getElementById('react-carrito');
if (carritoRoot) {
    const rawUserData = carritoRoot.getAttribute('data-user');
    const baseUrlData = carritoRoot.getAttribute('data-baseurl') || '/';
    let parsedUser = null;
    try {
        parsedUser = rawUserData ? JSON.parse(rawUserData) : null;
    } catch (e) {
        console.error("Error parseando user:", e);
    }
    const root = ReactDOM.createRoot(carritoRoot);
    root.render(<Carrito user={parsedUser} baseUrl={baseUrlData} />);
}