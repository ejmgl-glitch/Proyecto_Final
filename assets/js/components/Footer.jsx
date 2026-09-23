const Footer = () => {
    const currentYear = new Date().getFullYear();
    const footerContainer = document.getElementById('react-footer-root');
    const baseUrl = footerContainer?.getAttribute('data-baseurl') || '/chileroPasos';
    const cleanBase = baseUrl.endsWith('/') ? baseUrl : baseUrl + '/';

    const links = [
        { label: "Inicio", url: `${cleanBase}index.php` },
        { label: "Productos", url: `${cleanBase}productos/index.php` },
        { label: "Reseñas", url: `${cleanBase}reviews/index.php` },
        { label: "Lista de Deseos", url: `${cleanBase}wishlist/index.php` }
    ];

    return (
        <footer className="site-footer">
            <div className="footer-content" style={{
                maxWidth: "1200px",
                margin: "0 auto",
                padding: "20px 24px",
                display: "flex",
                flexWrap: "wrap",
                justifyContent: "space-between",
                alignItems: "center",
                gap: "15px"
            }}>
                {/* Sección de Marca e Información */}
                <div className="footer-brand">
                    <p style={{ margin: 0, fontSize: "0.88rem" }}>
                        <strong>Paso Chilero</strong> | Desarrollado por Kevin &amp; Martín | © Todos los derechos reservados | Guatemala {currentYear}
                    </p>
                </div>

                {/* Navegación */}
                <nav className="footer-nav">
                    <ul style={{
                        listStyle: "none",
                        display: "flex",
                        gap: "20px",
                        margin: 0,
                        padding: 0,
                        flexWrap: "wrap"
                    }}>
                        {links.map((link, index) => (
                            <li key={index}>
                                <a 
                                    href={link.url}
                                    style={{
                                        fontSize: "0.92rem",
                                        fontWeight: 500
                                    }}
                                >
                                    {link.label}
                                </a>
                            </li>
                        ))}
                    </ul>
                </nav>
            </div>
        </footer>
    );
};

const footerContainer = document.getElementById('react-footer-root');
if (footerContainer) {
    const root = ReactDOM.createRoot(footerContainer);
    root.render(<Footer />);
}