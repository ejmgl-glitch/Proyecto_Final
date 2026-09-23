const { useState, useEffect } = React;

const Header = ({ user, baseUrl = '/' }) => {
    const [isMenuOpen, setIsMenuOpen] = useState(false);
    const [cartItems, setCartItems] = useState([]);
    const [isCartModalOpen, setIsCartModalOpen] = useState(false);
    const [busqueda, setBusqueda] = useState('');

    const cleanBase = baseUrl.endsWith('/') ? baseUrl : baseUrl + '/';
    
    // Identificación de roles
    const userRole = user ? (user.tipo_usuario || user.rol || user.role) : null;
    const esAdmin = userRole === 'admin';
    const esTrabajador = userRole === 'trabajador' || userRole === 'empleado';
    const puedeUsarCarrito = !userRole || userRole === 'cliente';

    // Clave de carrito única por usuario
    const cartKey = user && user.id ? `chilero_carrito_${user.id}` : 'chilero_carrito_guest';

    // Detección de ruta activa y páginas de autenticación
    const path = (typeof window !== 'undefined' ? window.location.pathname : '').toLowerCase();
    const isAuth = path.includes('/auth') || path.includes('login.php') || path.includes('register.php');
    const isProductos = path.includes('/productos');
    const isReviews = path.includes('/reviews');
    const isFavoritos = path.includes('/wishlist');
    const isInicio = !isProductos && !isReviews && !isFavoritos && 
                     !path.includes('/pedidos') && 
                     !path.includes('/carrito') && 
                     !path.includes('/usuarios') && 
                     !isAuth;

    const activeStyle = {
        color: 'var(--primary)',
        fontWeight: '700',
        borderBottom: '3px solid var(--primary)',
        paddingBottom: '4px'
    };

    const inactiveStyle = {
        paddingBottom: '4px'
    };

    const sincronizarCarrito = () => {
        try {
            const raw = localStorage.getItem(cartKey);
            setCartItems(raw ? JSON.parse(raw) : []);
        } catch (e) {
            setCartItems([]);
        }
    };

    useEffect(() => {
        if (puedeUsarCarrito && !isAuth) {
            sincronizarCarrito();
            window.addEventListener('carrito_actualizado', sincronizarCarrito);
            window.addEventListener('storage', sincronizarCarrito);
            return () => {
                window.removeEventListener('carrito_actualizado', sincronizarCarrito);
                window.removeEventListener('storage', sincronizarCarrito);
            };
        }
    }, [puedeUsarCarrito, isAuth, user]);

    const actualizarStorage = (nuevosItems) => {
        setCartItems(nuevosItems);
        localStorage.setItem(cartKey, JSON.stringify(nuevosItems));
        window.dispatchEvent(new Event('carrito_actualizado'));
    };

    const cambiarCantidad = (id, delta) => {
        const actualizados = cartItems.map(item => {
            if (item.id === id) {
                const nuevaCant = item.cantidad + delta;
                return nuevaCant > 0 ? { ...item, cantidad: nuevaCant } : null;
            }
            return item;
        }).filter(Boolean);
        actualizarStorage(actualizados);
    };

    const eliminarItem = (id) => {
        const actualizados = cartItems.filter(item => item.id !== id);
        actualizarStorage(actualizados);
    };

    const totalCantidad = cartItems.reduce((acc, i) => acc + (i.cantidad || 1), 0);
    const totalMonto = cartItems.reduce((acc, i) => acc + (parseFloat(i.precio) * (i.cantidad || 1)), 0);

    const handleSearchSubmit = (e) => {
        e.preventDefault();
        if (busqueda.trim() !== '') {
            window.location.href = `${cleanBase}productos/index.php?buscar=${encodeURIComponent(busqueda.trim())}`;
        }
    };

    return (
        <>
            <header className="main-navbar">
                <div className="navbar-container">
                    <div className="navbar-brand-wrapper">
                        <a href={`${cleanBase}index.php`} className="navbar-brand">
                            <span className="brand-highlight">Paso </span>Chilero
                        </a>
                    </div>

                    <button 
                        className="navbar-toggler" 
                        onClick={() => setIsMenuOpen(!isMenuOpen)}
                        aria-label="Alternar navegación"
                    >
                        <span className="toggler-icon">{isMenuOpen ? '✕' : '☰'}</span>
                    </button>

                    {/* MENÚ CENTRAL */}
                    <nav className={`nav-menu ${isMenuOpen ? 'active' : ''}`}>
                        {isAuth ? (
                            /* En páginas Login y Registro: solo botón Regresar a Página Principal con color --muted */
                            <ul className="nav-list nav-list-buttons">
                                <li className="nav-item">
                                    <a 
                                        href={`${cleanBase}index.php`} 
                                        className="header-nav-btn btn-muted"
                                        style={{ backgroundColor: 'var(--muted)', color: '#ffffff' }}
                                    >
                                        &larr; Regresar a la Página Principal
                                    </a>
                                </li>
                            </ul>
                        ) : esAdmin ? (
                            /* Administrador */
                            <ul className="nav-list nav-list-buttons">
                                <li className="nav-item">
                                    <a href={`${cleanBase}index.php`} className="header-nav-btn btn-muted">
                                        Página de Inicio
                                    </a>
                                </li>
                                <li className="nav-item">
                                    <a href={`${cleanBase}productos/index.php`} className="header-nav-btn btn-primary-dark">
                                        Gestionar Productos
                                    </a>
                                </li>
                                <li className="nav-item">
                                    <a href={`${cleanBase}reviews/index.php`} className="header-nav-btn btn-primary">
                                        Ver Reseñas
                                    </a>
                                </li>
                            </ul>
                        ) : esTrabajador ? (
                            /* Empleado / Trabajador */
                            <ul className="nav-list nav-list-buttons">
                                <li className="nav-item">
                                    <a href={`${cleanBase}index.php`} className="header-nav-btn btn-muted">
                                        Página de Inicio
                                    </a>
                                </li>
                                <li className="nav-item">
                                    <a href={`${cleanBase}productos/index.php`} className="header-nav-btn btn-ok">
                                        Ver Productos
                                    </a>
                                </li>
                                <li className="nav-item">
                                    <a href={`${cleanBase}reviews/index.php`} className="header-nav-btn btn-ok">
                                        Ver Reseñas
                                    </a>
                                </li>
                            </ul>
                        ) : (
                            /* Cliente / Visitante */
                            <ul className="nav-list">
                                <li className="nav-item">
                                    <a 
                                        href={`${cleanBase}index.php`} 
                                        className={`nav-link ${isInicio ? 'active' : ''}`}
                                        style={isInicio ? activeStyle : inactiveStyle}
                                    >
                                        Inicio
                                    </a>
                                </li>
                                <li className="nav-item">
                                    <a 
                                        href={`${cleanBase}productos/index.php`} 
                                        className={`nav-link ${isProductos ? 'active' : ''}`}
                                        style={isProductos ? activeStyle : inactiveStyle}
                                    >
                                        Productos
                                    </a>
                                </li>
                                <li className="nav-item">
                                    <a 
                                        href={`${cleanBase}reviews/index.php`} 
                                        className={`nav-link ${isReviews ? 'active' : ''}`}
                                        style={isReviews ? activeStyle : inactiveStyle}
                                    >
                                        Reseñas
                                    </a>
                                </li>
                                {user && puedeUsarCarrito && (
                                    <li className="nav-item">
                                        <a 
                                            href={`${cleanBase}wishlist/index.php`} 
                                            className={`nav-link ${isFavoritos ? 'active' : ''}`}
                                            style={isFavoritos ? activeStyle : inactiveStyle}
                                        >
                                            Favoritos
                                        </a>
                                    </li>
                                )}
                            </ul>
                        )}
                    </nav>

                    {/* BLOQUE DERECHO */}
                    <div className="navbar-auth-wrapper">
                        {/* Buscador (se oculta en Login/Register) */}
                        {!isAuth && (
                            <form className="header-search-bar" onSubmit={handleSearchSubmit}>
                                <svg className="search-icon" viewBox="0 0 24 24" width="16" height="16" stroke="currentColor" strokeWidth="2.5" fill="none" strokeLinecap="round" strokeLinejoin="round">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                                <input 
                                    type="text" 
                                    placeholder="Buscar productos..." 
                                    value={busqueda}
                                    onChange={(e) => setBusqueda(e.target.value)}
                                />
                            </form>
                        )}

                        {/* Botón de Usuarios para Staff (se oculta en Login/Register) */}
                        {!isAuth && user && (esAdmin || esTrabajador) && (
                            <a href={`${cleanBase}usuarios/index.php`} className="nav-link badge-admin">
                                Usuarios
                            </a>
                        )}

                        {/* Carrito (se oculta en Login/Register) */}
                        {!isAuth && puedeUsarCarrito && (
                            <button 
                                type="button"
                                className="header-cart-button"
                                onClick={() => setIsCartModalOpen(true)}
                                title="Ver mi carrito"
                            >
                                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                                    <circle cx="9" cy="21" r="1"></circle>
                                    <circle cx="20" cy="21" r="1"></circle>
                                    <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"></path>
                                </svg>
                                {totalCantidad > 0 && (
                                    <span className="header-cart-badge">{totalCantidad}</span>
                                )}
                            </button>
                        )}

                        {/* Pedidos (se oculta en Login/Register) */}
                        {!isAuth && user && (
                            <a 
                                href={`${cleanBase}pedidos/index.php`} 
                                className="header-orders-button"
                                title={puedeUsarCarrito ? "Mis pedidos" : "Gestión de Pedidos"}
                            >
                                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                                    <polyline points="14 2 14 8 20 8"></polyline>
                                    <line x1="16" y1="13" x2="8" y2="13"></line>
                                    <line x1="16" y1="17" x2="8" y2="17"></line>
                                    <polyline points="10 9 9 9 8 9"></polyline>
                                </svg>
                                <span>{puedeUsarCarrito ? 'Pedidos' : 'Gestión Pedidos'}</span>
                            </a>
                        )}

                        {user ? (
                            <div className="user-profile">
                                <span className="user-greeting">
                                    👤 <strong>{user.nombre || user.username || 'Usuario'}</strong>
                                </span>
                                <a href={`${cleanBase}auth/logout.php`} className="btn-logout">Salir</a>
                            </div>
                        ) : (
                            <div className="auth-buttons">
                                <a href={`${cleanBase}auth/login.php`} className="btn-login">Ingresar</a>
                                <a href={`${cleanBase}auth/register.php`} className="btn-register">Registro</a>
                            </div>
                        )}
                    </div>
                </div>
            </header>

            {/* Modal de Carrito */}
            {!isAuth && puedeUsarCarrito && isCartModalOpen && (
                <div className="cart-modal-backdrop" onClick={() => setIsCartModalOpen(false)}>
                    <div className="cart-modal-content" onClick={(e) => e.stopPropagation()}>
                        <div className="cart-modal-header">
                            <h3>🛍️ Mi Carrito ({totalCantidad})</h3>
                            <button className="cart-modal-close" onClick={() => setIsCartModalOpen(false)}>✕</button>
                        </div>
                        <div className="cart-modal-body">
                            {cartItems.length === 0 ? (
                                <div className="cart-modal-empty">
                                    <p>Tu carrito está vacío.</p>
                                    <a 
                                        href={`${cleanBase}productos/index.php`} 
                                        className="btn btn-sm"
                                        onClick={() => setIsCartModalOpen(false)}
                                    >
                                        Explorar productos
                                    </a>
                                </div>
                            ) : (
                                <div className="cart-modal-list">
                                    {cartItems.map((item) => (
                                        <div key={item.id} className="cart-modal-item">
                                            {item.imagen ? (
                                                <img src={item.imagen} alt={item.nombre} className="cart-modal-thumb" />
                                            ) : (
                                                <div className="cart-modal-thumb-placeholder">👟</div>
                                            )}
                                            <div className="cart-modal-item-info">
                                                <h4>{item.nombre}</h4>
                                                <div className="muted">{item.marca}</div>
                                                <div className="price">Q {parseFloat(item.precio).toFixed(2)}</div>
                                            </div>
                                            <div className="cart-modal-item-actions">
                                                <div className="qty-control">
                                                    <button onClick={() => cambiarCantidad(item.id, -1)} className="btn-qty">-</button>
                                                    <span>{item.cantidad}</span>
                                                    <button onClick={() => cambiarCantidad(item.id, 1)} className="btn-qty">+</button>
                                                </div>
                                                <button 
                                                    className="btn-remove"
                                                    title="Quitar producto"
                                                    onClick={() => eliminarItem(item.id)}
                                                >
                                                    🗑️
                                                </button>
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            )}
                        </div>
                        {cartItems.length > 0 && (
                            <div className="cart-modal-footer">
                                <div className="cart-modal-total">
                                    <span>Total:</span>
                                    <span className="price" style={{ fontSize: '1.25rem' }}>Q {totalMonto.toFixed(2)}</span>
                                </div>
                                <div className="cart-modal-buttons">
                                    <a 
                                        href={`${cleanBase}carrito/index.php`} 
                                        className="btn" 
                                        style={{ width: '100%', textAlign: 'center', display: 'block' }}
                                    >
                                        Tramitar Compra
                                    </a>
                                </div>
                            </div>
                        )}
                    </div>
                </div>
            )}
        </>
    );
};

const headerRootElement = document.getElementById('react-header');
if (headerRootElement) {
    const rawUserData = headerRootElement.getAttribute('data-user');
    const baseUrlData = headerRootElement.getAttribute('data-baseurl') || '/';
    let parsedUser = null;
    try {
        parsedUser = rawUserData ? JSON.parse(rawUserData) : null;
    } catch (e) {
        console.error("Error parseando datos de usuario en Header:", e);
    }
    const root = ReactDOM.createRoot(headerRootElement);
    root.render(<Header user={parsedUser} baseUrl={baseUrlData} />);
}