const { useState, useEffect } = React;

const Carrito = ({ user, baseUrl = '/' }) => {
    const [items, setItems] = useState([]);
    const [metodoPago, setMetodoPago] = useState('tarjeta');
    const [cargando, setCargando] = useState(false);
    const [mensaje, setMensaje] = useState(null); // { tipo: 'ok' | 'error', texto: '' }
    const [compraExitosa, setCompraExitosa] = useState(null);

    const cleanBase = baseUrl.endsWith('/') ? baseUrl : baseUrl + '/';

    useEffect(() => {
        try {
            const guardado = localStorage.getItem('chilero_carrito');
            if (guardado) {
                setItems(JSON.parse(guardado));
            }
        } catch (e) {
            console.error("Error al cargar carrito:", e);
        }
    }, []);

    // Guardar cambios en localStorage
    const actualizarStorage = (nuevosItems) => {
        setItems(nuevosItems);
        localStorage.setItem('chilero_carrito', JSON.stringify(nuevosItems));
        window.dispatchEvent(new Event('carrito_actualizado'));
    };

    const cambiarCantidad = (id, delta) => {
        const actualizados = items.map(item => {
            if (item.id === id) {
                const nuevaCant = item.cantidad + delta;
                return nuevaCant > 0 ? { ...item, cantidad: nuevaCant } : null;
            }
            return item;
        }).filter(Boolean);
        actualizarStorage(actualizados);
    };

    const eliminarItem = (id) => {
        const actualizados = items.filter(item => item.id !== id);
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
                    items: items,
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
                total: data.total
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
                <div style={{ fontSize: '3rem', color: 'var(--ok, #1f7a3d)' }}>✓</div>
                <h1 style={{ color: 'var(--ok, #1f7a3d)', marginTop: '10px' }}>¡Gracias por tu compra!</h1>
                <p>Tu orden ha sido registrada con el número de pedido <strong>#{compraExitosa.pedidoId}</strong>.</p>
                <p className="price" style={{ fontSize: '1.4rem' }}>Total pagado: Q {compraExitosa.total}</p>
                <p className="muted">Nos pondremos en contacto contigo para coordinar el envío de tus zapatos.</p>
                <div style={{ marginTop: '25px' }}>
                    <a href={`${cleanBase}productos/index.php`} className="btn">Seguir explorando productos</a>
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
                                    <th>Precio</th>
                                    <th>Cantidad</th>
                                    <th>Subtotal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                {items.map((item) => (
                                    <tr key={item.id}>
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
                                        <td>Q {parseFloat(item.precio).toFixed(2)}</td>
                                        <td>
                                            <div className="qty-control">
                                                <button onClick={() => cambiarCantidad(item.id, -1)} className="btn-qty">-</button>
                                                <span>{item.cantidad}</span>
                                                <button onClick={() => cambiarCantidad(item.id, 1)} className="btn-qty">+</button>
                                            </div>
                                        </td>
                                        <td style={{ fontWeight: 'bold' }}>
                                            Q {(parseFloat(item.precio) * item.cantidad).toFixed(2)}
                                        </td>
                                        <td>
                                            <button 
                                                onClick={() => eliminarItem(item.id)} 
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

                        {/* Selección de método de pago (según el ENUM de BD) */}
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
                                    {cargando ? 'Procesando...' : 'Confirmar y Pagar'}
                                </button>
                            )}
                        </div>
                    </div>
                </div>
            )}
        </div>
    );
};

// DOM
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