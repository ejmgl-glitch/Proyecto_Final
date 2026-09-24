-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 24, 2026 at 04:07 AM
-- Server version: 10.4.28-MariaDB
-- PHP Version: 8.2.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `chilero`
--

-- --------------------------------------------------------

--
-- Table structure for table `categoria`
--

CREATE TABLE `categoria` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `categoria`
--

INSERT INTO `categoria` (`id`, `nombre`, `descripcion`) VALUES
(1, 'Deportivos', 'Zapatillas para correr, entrenamiento y gimnasio'),
(2, 'Casuales', 'Zapatos cómodos para el uso diario'),
(3, 'Formales', 'Zapatos de vestir y cuero');

-- --------------------------------------------------------

--
-- Table structure for table `detalle_pedido`
--

CREATE TABLE `detalle_pedido` (
  `id` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detalle_pedido`
--

INSERT INTO `detalle_pedido` (`id`, `id_pedido`, `id_producto`, `cantidad`, `subtotal`) VALUES
(1, 1, 1, 3, 4500.00),
(2, 2, 1, 2, 3000.00);

-- --------------------------------------------------------

--
-- Table structure for table `pedido`
--

CREATE TABLE `pedido` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha` datetime NOT NULL DEFAULT current_timestamp(),
  `total` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estado` enum('realizado','enviado','entregado') NOT NULL DEFAULT 'realizado',
  `metodo_pago` enum('tarjeta','pay pal','transferencia') NOT NULL DEFAULT 'tarjeta'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pedido`
--

INSERT INTO `pedido` (`id`, `id_usuario`, `fecha`, `total`, `estado`, `metodo_pago`) VALUES
(1, 6, '2026-09-22 17:28:47', 4500.00, 'realizado', 'pay pal'),
(2, 7, '2026-09-22 20:27:17', 3000.00, 'entregado', 'transferencia');

-- --------------------------------------------------------

--
-- Table structure for table `producto`
--

CREATE TABLE `producto` (
  `id` int(11) NOT NULL,
  `nombre` varchar(99) NOT NULL,
  `marca` varchar(50) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `precio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `color` varchar(25) DEFAULT NULL,
  `genero` enum('hombre','mujer','ninos') DEFAULT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `id_categoria` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `producto`
--

INSERT INTO `producto` (`id`, `nombre`, `marca`, `descripcion`, `precio`, `color`, `genero`, `imagen`, `id_categoria`) VALUES
(1, 'Air', 'Jordan', 'Urbanos', 1500.00, 'Negro', 'hombre', 'https://encrypted-tbn2.gstatic.com/shopping?q=tbn:ANd9GcSbcLYYhqO3FQo-nMJvyheWSIxGi25CTrsfgY61c5b-9rzvrk2ilBhvnb3RMIE0d6-b_HKZbEgo_oUCLF04Lb0huuhRTsx8', 2),
(2, 'Grand Court 2.0', 'adidas', 'Tenis casual de estilo clásico inspirado en el tenis, ideal para uso diario.', 699.00, 'Negro/Blanco', 'hombre', 'https://assets.adidas.com/images/w_500,f_auto,q_auto/2f2a53f217c44a55afa4ed15e58ba0df_9366/GRAND_COURT_2.0_SHOES_White_JH9305.jpg ', 2),
(3, 'Air Force 1 07', 'Nike', 'Tenis urbano de diseño clásico, cómodo y versátil para uso cotidiano.', 899.00, 'Blanco', 'hombre', 'https://static.nike.com/a/images/t_PDP_1728_v1/f_auto,q_auto:eco,c_scale,w_300,u_9ddf04c7-2a9a-4d76-add1-d15af8f0263d,c_scale,fl_relative,w_1.0,h_1.0,fl_layer_apply/b5fe6c36-2d4d-4370-9423-cdd282017a14/AIR+FORCE+1+%2707+EDGE.png ', 2),
(4, 'Classic Leather', 'Reebok', 'Calzado casual de estilo retro con diseño versátil para diferentes ocasiones.', 750.00, 'Blanco/Gris', 'hombre', ' https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcTgG2dJV9NVVvphLmZVYKuPDg7Ed-ayXLA3vyvbtAZBqQ&s=10', 2),
(5, 'Delray', 'Payless', 'Zapato casual de diseño elegante y cómodo para combinar con diferentes atuendos.', 549.00, 'Azul', 'hombre', ' https://paylesshn.vtexassets.com/arquivos/ids/516261-800-800?v=638866717473900000&width=800&height=800&aspect=true ', 2),
(6, 'Casual Deportivo', 'Original Penguin', 'Tenis casual moderno con detalles deportivos y diseño versátil.', 669.00, 'Blanco/Verde', 'hombre', ' https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS49BXi1cuyzFzy92eJxZnssRmZE9Bwbl8AtVDve-NVU4nsub9OKJPtkpoj&s=10', 2),
(7, 'Charged Rogue 2', 'Under Armour', 'Tenis deportivo ligero diseñado para entrenamiento y actividades físicas.', 750.00, 'Gris/Negro', 'hombre', ' https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQzCEoweKl5f8d3ZRPlBNazQrMIwgFjns51O-LSwGPlPPZTSqkE8QxRnDFn&s=10', 1),
(8, 'Air Max Dawn', 'Nike', 'Tenis deportivo con diseño moderno y amortiguación para actividades diarias.', 750.00, 'Gris', 'hombre', 'https://static.nike.com/a/images/t_PDP_1728_v1/f_auto,q_auto:eco,c_scale,w_300,u_9ddf04c7-2a9a-4d76-add1-d15af8f0263d,c_scale,fl_relative,w_1.0,h_1.0,fl_layer_apply/cf3807e8-da59-4c49-8857-6c418e06d2c8/AIR+MAX+DAWN.png ', 1),
(9, 'Duramo SL 2.0', 'adidas', 'Tenis ligero diseñado para correr y realizar diferentes actividades deportivas.', 1200.00, 'Negro/Blanco', 'hombre', ' https://www.shopwss.com/cdn/shop/files/IF9400_1.jpg?v=1777393852', 1),
(10, 'Fresh Foam X 880v15', 'New Balance', 'Tenis para running con amortiguación y comodidad para entrenamientos.', 899.00, 'Negro', 'hombre', ' https://nb.scene7.com/is/image/NB/m880b15_nb_02_i?$dw_detail_gallery$', 1),
(11, 'Motus', 'Payless', 'Tenis deportivo ligero para actividades físicas y uso cotidiano.', 199.00, 'Negro/Gris', 'mujer', 'https://dynamic.zacdn.com/nm1OUOP0mhFau046n9hVo4tfkM0=/filters:quality(70):format(webp)/https://static-id.zacdn.com/p/payless-5574-6820635-2.jpg ', 1),
(12, 'Oxford Clásico', 'Florsheim', 'Zapato Oxford de diseño elegante para ocasiones formales y profesionales.', 699.00, 'Negro', 'hombre', ' https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSJC4E-zMqJqxiC58XQSB13Ocaj9MhkjRGibXCCHg6Al-A4EdhbJ1sX2Qwk&s=10', 3),
(13, 'Derby Ejecutivo', 'Steve Madden', 'Zapato de vestir estilo Derby para ocasiones formales y profesionales.', 649.00, 'Café', 'hombre', ' https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSpLWnkcQh9urffwW95rK1Q7FBEOV6NlL4ko1oloRGGT5WPu0Nf4kMBfBlf&s=10', 3),
(14, 'Loafer Clásico', 'Dockers', 'Zapato tipo mocasín con diseño elegante para oficina y ocasiones especiales.', 599.00, 'Café', 'hombre', ' https://i5.walmartimages.com/seo/Dockers-Mens-Colleague-Dress-Penny-Loafer-Shoe_4a6da9a9-6e8a-4b76-bbe7-f29eca207f76.16eafee6ef34d922e2366d7c6bd27248.jpeg?odnHeight=768&odnWidth=768&odnBg=FFFFFF ', 3),
(15, 'Oxford Elegance', 'Fioni', 'Zapato formal de diseño clásico para eventos y ocasiones especiales.', 449.00, 'Negro', 'mujer', ' https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcSXALOv6WEARbQubNXXkI9HWSTLSRCmfz5jzomMzCOhW71lSnaB2XYFbGlY&s=10', 3),
(16, 'Loafer Formal', 'Comfort Plus', 'Mocasín elegante que combina comodidad y estilo formal.', 429.00, 'Negro', 'mujer', ' https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRa_hqpGu8nBefnIbl4JgB2-efNpZKDRFZHEl7T7M8S6xR4McfKgMxJTEE&s=10', 3);

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `calificacion` tinyint(1) NOT NULL,
  `comentario` varchar(255) DEFAULT NULL,
  `fecha` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `id_usuario`, `id_producto`, `calificacion`, `comentario`, `fecha`) VALUES
(1, 7, 1, 5, 'Muy buen calzado', '2026-09-22');

-- --------------------------------------------------------

--
-- Table structure for table `usuario`
--

CREATE TABLE `usuario` (
  `id` int(11) NOT NULL,
  `nombre` varchar(99) NOT NULL,
  `correo` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `tipo_usuario` enum('cliente','trabajador','admin') NOT NULL DEFAULT 'cliente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `usuario`
--

INSERT INTO `usuario` (`id`, `nombre`, `correo`, `password`, `telefono`, `direccion`, `tipo_usuario`) VALUES
(1, 'Kevin', 'admin@chilero.com', '$2y$12$mcooO9IlXaIhJCqGT7qsSuAKG6uQt.A0Vbc0B6hRexEFeNYznDWty', '55512345', 'Sede Central', 'admin'),
(2, 'Martin', 'empleado@chilero.com', '$2y$12$mcooO9IlXaIhJCqGT7qsSuAKG6uQt.A0Vbc0B6hRexEFeNYznDWty', '55598765', 'Antigua Guatemala', 'trabajador'),
(3, 'Ana', 'cliente@chilero.com', '$2y$12$mcooO9IlXaIhJCqGT7qsSuAKG6uQt.A0Vbc0B6hRexEFeNYznDWty', '55567890', 'Zona 7, Ciudad', 'cliente'),
(5, 'Diego Hernandez', 'diego@chilero.com', '$2y$10$a73fEn4iGuVN0Ib.vgA32ukSkceq5oc6cai4OQMjsFruh5ZDJ8n66', '12345678', '12 av B 15-22', 'cliente'),
(6, 'Juan', 'juan@chilero.com', '$2y$10$QR4OvK0YWcib/dlcJJHfqOa8jayQreIUDQemrQTRByZFlyw/BW1E2', '54545454', '12 Avenida', 'cliente'),
(7, 'Alvaro', 'alvaro@chilero.com', '$2y$10$dyNXdeX746pFYe4GkGS5AuSOn8F8O5aAdLIkaDdTRYtAoYc453BUO', '54545454', '123 ave', 'cliente');

-- --------------------------------------------------------

--
-- Table structure for table `variante_producto`
--

CREATE TABLE `variante_producto` (
  `id` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `talla` varchar(10) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `codigo_unico` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `wishlist`
--

CREATE TABLE `wishlist` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `wishlist`
--

INSERT INTO `wishlist` (`id`, `id_usuario`, `id_producto`) VALUES
(2, 6, 1),
(5, 7, 1),
(6, 3, 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_pedido` (`id_pedido`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indexes for table `pedido`
--
ALTER TABLE `pedido`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indexes for table `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_categoria` (`id_categoria`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indexes for table `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- Indexes for table `variante_producto`
--
ALTER TABLE `variante_producto`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo_unico` (`codigo_unico`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indexes for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`),
  ADD KEY `id_producto` (`id_producto`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `categoria`
--
ALTER TABLE `categoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `pedido`
--
ALTER TABLE `pedido`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `producto`
--
ALTER TABLE `producto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `variante_producto`
--
ALTER TABLE `variante_producto`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD CONSTRAINT `detalle_pedido_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedido` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detalle_pedido_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id`) ON UPDATE CASCADE;

--
-- Constraints for table `pedido`
--
ALTER TABLE `pedido`
  ADD CONSTRAINT `pedido_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `producto_ibfk_1` FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `variante_producto`
--
ALTER TABLE `variante_producto`
  ADD CONSTRAINT `variante_producto_ibfk_1` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `wishlist`
--
ALTER TABLE `wishlist`
  ADD CONSTRAINT `wishlist_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `wishlist_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
