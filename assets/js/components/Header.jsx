// En assets/js/components/Header.jsx
const { useState, useEffect } = React;

const Header = ({ user, baseUrl = '/' }) => {
    const [isMenuOpen, setIsMenuOpen] = useState(false);
    const [cartCount, setCartCount] = useState(0);

    const toggleMenu = () => {
        setIsMenuOpen(!isMenuOpen);
    };

    const cleanBase = baseUrl.endsWith('/') ? baseUrl : baseUrl + '/';

    // Leer cantidad de productos en carrito y escuchar cambios
    const actualizarContador = () => {
        try {
            const raw = localStorage.getItem('chilero_carrito');
            if (raw) {
                const items = JSON.parse(raw);
                const totalCant = items.reduce((acc, item) => acc + (item.cantidad || 1), 0);
                setCartCount(totalCant);
            } else {
                setCartCount(0);
            }
        } catch (e) {
            setCartCount(0);
        }
    };

    useEffect(() => {
        actualizarContador();
        window.addEventListener('carrito_actualizado', actualizarContador);
        window.addEventListener('storage', actualizarContador);
        return () => {
            window.removeEventListener('carrito_actualizado', actualizarContador);
            window.removeEventListener('storage', actualizarContador);
        };
    }, []);

    return (
        <header className="main-navbar">
            <div className="navbar-container">
                {/* 1. Izquierda: Logotipo */}
                <div className="navbar-brand-wrapper">
                    <a href={`${cleanBase}index.php`} className="navbar-brand">
                        <span className="brand-highlight">Mi</span>Tienda
                    </a>
                </div>

                {/* Botón responsive para móviles */}
                <button 
                    className="navbar-toggler" 
                    onClick={toggleMenu} 
                    aria-label="Alternar navegación"
                >
                    <span className="toggler-icon">{isMenuOpen ? '✕' : '☰'}</span>
                </button>

                {/* 2. Centro: Navegación */}
                <nav className={`nav-menu ${isMenuOpen ? 'active' : ''}`}>
                    <ul className="nav-list">
                        <li className="nav-item">
                            <a href={`${cleanBase}index.php`} className="nav-link">Inicio</a>
                        </li>
                        <li className="nav-item">
                            <a href={`${cleanBase}productos/index.php`} className="nav-link">Productos</a>
                        </li>
                        <li className="nav-item">
                            <a href={`${cleanBase}reviews/index.php`} className="nav-link">Reseñas</a>
                        </li>
                        
                        {user && (
                            <li className="nav-item">
                                <a href={`${cleanBase}wishlist/index.php`} className="nav-link">Favoritos</a>
                            </li>
                        )}

                        {/* Enlace al Carrito */}
                        <li className="nav-item">
                            <a href={`${cleanBase}carrito/index.php`} className="nav-link nav-cart-link">
                                🛒 Carrito 
                                {cartCount > 0 && <span className="cart-badge">{cartCount}</span>}
                            </a>
                        </li>

                        {user && (user.tipo_usuario === 'admin' || user.rol === 'admin' || user.role === 'admin') && (
                            <li className="nav-item">
                                <a href={`${cleanBase}usuarios/index.php`} className="nav-link badge-admin">
                                    Usuarios
                                </a>
                            </li>
                        )}
                    </ul>

                    {/* Autenticación en menú móvil */}
                    <div className="mobile-auth-wrapper">
                        {user ? (
                            <div className="user-profile">
                                <span className="user-greeting">
                                    Hola, <strong>{user.nombre || user.username || 'Usuario'}</strong>
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
                </nav>

                {/* 3. Derecha: Sesión y salida (Escritorio) */}
                <div className="navbar-auth-wrapper">
                    {user ? (
                        <div className="user-profile">
                            <span className="user-greeting">
                                Hola, <strong>{user.nombre || user.username || 'Usuario'}</strong>
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