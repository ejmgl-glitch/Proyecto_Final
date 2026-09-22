SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE DATABASE IF NOT EXISTS `chilero`
DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_general_ci;

USE `chilero`;


-- ============================================================
-- USUARIO
-- ============================================================

CREATE TABLE `usuario` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(99) NOT NULL,
    `correo` VARCHAR(50) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `telefono` VARCHAR(15) DEFAULT NULL,
    `direccion` VARCHAR(255) DEFAULT NULL,
    `tipo_usuario` ENUM('cliente','trabajador','admin') NOT NULL DEFAULT 'cliente',
    PRIMARY KEY (`id`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;


-- ============================================================
-- CATEGORIA
-- ============================================================

CREATE TABLE `categoria` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(50) NOT NULL,
    `descripcion` VARCHAR(255) DEFAULT NULL,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;


-- ============================================================
-- PRODUCTO
-- ============================================================

CREATE TABLE `producto` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `nombre` VARCHAR(99) NOT NULL,
    `marca` VARCHAR(50) NOT NULL,
    `descripcion` VARCHAR(255) DEFAULT NULL,
    `precio` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `color` VARCHAR(25) DEFAULT NULL,
    `genero` ENUM('hombre','mujer','ninos') DEFAULT NULL,
    `imagen` VARCHAR(255) DEFAULT NULL,
    `id_categoria` INT(11) DEFAULT NULL,

    PRIMARY KEY (`id`),
    KEY `id_categoria` (`id_categoria`),

    CONSTRAINT `producto_ibfk_1`
        FOREIGN KEY (`id_categoria`)
        REFERENCES `categoria` (`id`)
        ON DELETE SET NULL
        ON UPDATE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;


-- ============================================================
-- VARIANTE PRODUCTO
-- ============================================================

CREATE TABLE `variante_producto` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `id_producto` INT(11) NOT NULL,
    `talla` VARCHAR(10) NOT NULL,
    `stock` INT(11) NOT NULL DEFAULT 0,
    `codigo_unico` VARCHAR(255) UNIQUE DEFAULT NULL,

    PRIMARY KEY (`id`),
    KEY `id_producto` (`id_producto`),

    CONSTRAINT `variante_producto_ibfk_1`
        FOREIGN KEY (`id_producto`)
        REFERENCES `producto` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;


-- ============================================================
-- PEDIDO
-- ============================================================

CREATE TABLE `pedido` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `id_usuario` INT(11) NOT NULL,
    `fecha` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `total` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    `estado` ENUM('realizado','enviado','entregado') NOT NULL DEFAULT 'realizado',
    `metodo_pago` ENUM('tarjeta','pay pal','transferencia') NOT NULL DEFAULT 'tarjeta',

    PRIMARY KEY (`id`),
    KEY `id_usuario` (`id_usuario`),

    CONSTRAINT `pedido_ibfk_1`
        FOREIGN KEY (`id_usuario`)
        REFERENCES `usuario` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;


-- ============================================================
-- DETALLE PEDIDO
-- ============================================================

CREATE TABLE `detalle_pedido` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `id_pedido` INT(11) NOT NULL,
    `id_producto` INT(11) NOT NULL,
    `cantidad` INT(11) NOT NULL DEFAULT 1,
    `subtotal` DECIMAL(10,2) NOT NULL,

    PRIMARY KEY (`id`),
    KEY `id_pedido` (`id_pedido`),
    KEY `id_producto` (`id_producto`),

    CONSTRAINT `detalle_pedido_ibfk_1`
        FOREIGN KEY (`id_pedido`)
        REFERENCES `pedido` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT `detalle_pedido_ibfk_2`
        FOREIGN KEY (`id_producto`)
        REFERENCES `producto` (`id`)
        ON DELETE RESTRICT
        ON UPDATE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;


-- ============================================================
-- REVIEWS
-- ============================================================

CREATE TABLE `reviews` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `id_usuario` INT(11) NOT NULL,
    `id_producto` INT(11) NOT NULL,
    `calificacion` TINYINT(1) NOT NULL,
    `comentario` VARCHAR(255) DEFAULT NULL,
    `fecha` DATE NOT NULL,

    PRIMARY KEY (`id`),
    KEY `id_usuario` (`id_usuario`),
    KEY `id_producto` (`id_producto`),

    CONSTRAINT `reviews_ibfk_1`
        FOREIGN KEY (`id_usuario`)
        REFERENCES `usuario` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT `reviews_ibfk_2`
        FOREIGN KEY (`id_producto`)
        REFERENCES `producto` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;


-- ============================================================
-- WISHLIST
-- ============================================================

CREATE TABLE `wishlist` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `id_usuario` INT(11) NOT NULL,
    `id_producto` INT(11) NOT NULL,

    PRIMARY KEY (`id`),
    KEY `id_usuario` (`id_usuario`),
    KEY `id_producto` (`id_producto`),

    CONSTRAINT `wishlist_ibfk_1`
        FOREIGN KEY (`id_usuario`)
        REFERENCES `usuario` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE,

    CONSTRAINT `wishlist_ibfk_2`
        FOREIGN KEY (`id_producto`)
        REFERENCES `producto` (`id`)
        ON DELETE CASCADE
        ON UPDATE CASCADE
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;


-- ============================================================
-- USUARIOS DE PRUEBA
-- Contraseña: Password123!
-- ============================================================

INSERT INTO `usuario`
(`nombre`, `correo`, `password`, `telefono`, `direccion`, `tipo_usuario`)
VALUES
(
    'Kevin',
    'admin@chilero.com',
    '$2y$12$0k/zlBUkf5GWgIhm6dE/TeGA9Beovjc5eFz4M35VVBYdFA8Pwrbki',
    '55512345',
    'Sede Central',
    'admin'
),
(
    'Martin',
    'empleado@chilero.com',
    '$2y$12$0k/zlBUkf5GWgIhm6dE/TeGA9Beovjc5eFz4M35VVBYdFA8Pwrbki',
    '55598765',
    'Antigua Guatemala',
    'trabajador'
),
(
    'Ana',
    'cliente@chilero.com',
    '$2y$12$0k/zlBUkf5GWgIhm6dE/TeGA9Beovjc5eFz4M35VVBYdFA8Pwrbki',
    '55567890',
    'Zona 7, Ciudad',
    'cliente'
);


-- ============================================================
-- CATEGORIAS INICIALES
-- ============================================================

INSERT INTO `categoria`
(`nombre`, `descripcion`)
VALUES
(
    'Deportivos',
    'Zapatillas para correr, entrenamiento y gimnasio'
),
(
    'Casuales',
    'Zapatos cómodos para el uso diario'
),
(
    'Formales',
    'Zapatos de vestir y cuero'
);

COMMIT;