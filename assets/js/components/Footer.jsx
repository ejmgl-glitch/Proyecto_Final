const Footer = ({ 
    projectName = "Paso Chilero", 
    tagline = "Desarrollado por Kevin & Martín | © Todos los derechos reservados | Guatemala 2026",
    links = [
        { label: "Inicio", url: "/chileroPasos/index.php" },
        { label: "Productos", url: "/chileroPasos/productos/index.php" },
        { label: "Reseñas", url: "/chileroPasos/reviews/index.php" },
        { label: "Lista de Deseos", url: "/chileroPasos/wishlist/index.php" }
    ]
}) => {
    const currentYear = new Date().getFullYear();

    return (
        <footer className="site-footer">
            <div className="footer-content" style={{
                maxWidth: "1200px",
                margin: "0 auto",
                padding: "25px 20px",
                display: "flex",
                flexWrap: "wrap",
                justifyContent: "space-between",
                alignItems: "center",
                gap: "15px"
            }}>
                {/* Sección de Marca e Información */}
                <div className="footer-brand">
                    <h3 style={{ margin: "0 0 5px 0" }}>{projectName}</h3>
                    <p style={{ margin: 0, opacity: 0.8, fontSize: "0.9rem" }}>
                        {tagline}
                    </p>
                </div>

                {/* Navegación Reutilizable */}
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
                                        textDecoration: "none",
                                        color: "inherit",
                                        fontSize: "0.95rem"
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