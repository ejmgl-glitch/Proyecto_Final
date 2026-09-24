-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Sep 24, 2026 at 05:28 AM
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
  `id_variante` int(11) DEFAULT NULL,
  `talla` varchar(10) DEFAULT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `detalle_pedido`
--

INSERT INTO `detalle_pedido` (`id`, `id_pedido`, `id_producto`, `id_variante`, `talla`, `cantidad`, `subtotal`) VALUES
(1, 1, 1, NULL, NULL, 3, 4500.00),
(2, 2, 1, NULL, NULL, 2, 3000.00),
(3, 3, 4, 18, '9.5', 1, 750.00),
(4, 3, 3, 13, '8.5', 2, 1798.00),
(5, 3, 12, 57, '11', 2, 1398.00),
(6, 4, 3, 13, '8.5', 1, 899.00),
(7, 4, 5, 23, '12', 3, 1647.00),
(8, 4, 3, 11, '7.5', 2, 1798.00),
(9, 4, 4, 18, '9.5', 1, 750.00);

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
(2, 7, '2026-09-22 20:27:17', 3000.00, 'entregado', 'transferencia'),
(3, 7, '2026-09-23 20:09:20', 3946.00, 'realizado', 'pay pal'),
(4, 3, '2026-09-23 20:24:27', 5094.00, 'realizado', 'tarjeta');

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

--
-- Dumping data for table `variante_producto`
--

INSERT INTO `variante_producto` (`id`, `id_producto`, `talla`, `stock`, `codigo_unico`) VALUES
(1, 1, '5', 5, 'AIR1-T05-NCSB'),
(2, 1, '5.5', 15, 'AIR1-T55-PITC'),
(3, 1, '7', 5, 'AIR1-T07-8WUC'),
(4, 1, '10', 14, 'AIR1-T10-BHUE'),
(5, 1, '11', 15, 'AIR1-T11-FEL3'),
(6, 2, '5.5', 4, 'GRA2-T55-UWRT'),
(7, 2, '6.5', 12, 'GRA2-T65-Q7NI'),
(8, 2, '7.5', 7, 'GRA2-T75-ZIUS'),
(9, 2, '8', 12, 'GRA2-T08-0K9E'),
(10, 3, '6', 3, 'AIR3-T06-8CU2'),
(11, 3, '7.5', 10, 'AIR3-T75-MMR2'),
(12, 3, '8', 4, 'AIR3-T08-48RX'),
(13, 3, '8.5', 0, 'AIR3-T85-0LUY'),
(14, 3, '10', 16, 'AIR3-T10-KNYA'),
(15, 4, '5.5', 3, 'CLA4-T55-HK0O'),
(16, 4, '7.5', 17, 'CLA4-T75-CQT5'),
(17, 4, '8.5', 15, 'CLA4-T85-5KOM'),
(18, 4, '9.5', 12, 'CLA4-T95-8FGI'),
(19, 4, '10.5', 9, 'CLA4-T105-A3GK'),
(20, 5, '9.5', 12, 'DEL5-T95-8YSW'),
(21, 5, '10.5', 3, 'DEL5-T105-Q58Y'),
(22, 5, '11.5', 14, 'DEL5-T115-OORO'),
(23, 5, '12', 5, 'DEL5-T12-CHFM'),
(24, 6, '5', 5, 'CAS6-T05-8WCH'),
(25, 6, '6', 14, 'CAS6-T06-FJMN'),
(26, 6, '6.5', 5, 'CAS6-T65-ER9R'),
(27, 6, '9.5', 11, 'CAS6-T95-DDMJ'),
(28, 7, '5', 2, 'CHA7-T05-1KXD'),
(29, 7, '6', 10, 'CHA7-T06-S6MI'),
(30, 7, '6.5', 18, 'CHA7-T65-LI39'),
(31, 7, '7.5', 8, 'CHA7-T75-330I'),
(32, 7, '9', 18, 'CHA7-T09-R092'),
(33, 7, '13', 17, 'CHA7-T13-JY8Q'),
(34, 8, '5.5', 17, 'AIR8-T55-HHW6'),
(35, 8, '6.5', 2, 'AIR8-T65-RX2D'),
(36, 8, '10.5', 5, 'AIR8-T105-621R'),
(37, 8, '11.5', 7, 'AIR8-T115-PWD8'),
(38, 8, '12', 14, 'AIR8-T12-Q0DF'),
(39, 8, '13', 6, 'AIR8-T13-AVQX'),
(40, 9, '6', 2, 'DUR9-T06-A8XS'),
(41, 9, '9', 6, 'DUR9-T09-P53H'),
(42, 9, '10.5', 10, 'DUR9-T105-HS1L'),
(43, 9, '11.5', 15, 'DUR9-T115-4C06'),
(44, 9, '12', 18, 'DUR9-T12-P7ST'),
(45, 9, '12.5', 18, 'DUR9-T125-S51V'),
(46, 10, '7', 5, 'FRE10-T07-ULST'),
(47, 10, '7.5', 5, 'FRE10-T75-5CGB'),
(48, 10, '8.5', 5, 'FRE10-T85-SU16'),
(49, 10, '9.5', 16, 'FRE10-T95-L9VH'),
(50, 11, '6.5', 18, 'MOT11-T65-57JU'),
(51, 11, '9', 8, 'MOT11-T09-4EEP'),
(52, 11, '10.5', 4, 'MOT11-T105-YPHK'),
(53, 11, '12', 5, 'MOT11-T12-6FZX'),
(54, 11, '12.5', 6, 'MOT11-T125-JEQ0'),
(55, 12, '6', 9, 'OXF12-T06-FPSM'),
(56, 12, '10', 8, 'OXF12-T10-MDNM'),
(57, 12, '11', 14, 'OXF12-T11-PALW'),
(58, 12, '12.5', 18, 'OXF12-T125-8E7I'),
(59, 13, '5', 7, 'DER13-T05-JEP6'),
(60, 13, '6', 10, 'DER13-T06-OTSR'),
(61, 13, '7', 12, 'DER13-T07-DCYP'),
(62, 13, '9', 4, 'DER13-T09-JADJ'),
(63, 14, '5.5', 2, 'LOA14-T55-MT7J'),
(64, 14, '6', 6, 'LOA14-T06-BZ78'),
(65, 14, '7', 10, 'LOA14-T07-BHLK'),
(66, 14, '8.5', 8, 'LOA14-T85-KSGM'),
(67, 14, '11.5', 2, 'LOA14-T115-9BAS'),
(68, 14, '13', 8, 'LOA14-T13-SIQX'),
(69, 15, '8', 11, 'OXF15-T08-Y9M3'),
(70, 15, '9', 6, 'OXF15-T09-OMBE'),
(71, 15, '11.5', 4, 'OXF15-T115-W5PB'),
(72, 15, '12', 14, 'OXF15-T12-5YKI'),
(73, 15, '12.5', 11, 'OXF15-T125-BGJA'),
(74, 15, '13', 13, 'OXF15-T13-89TI'),
(75, 16, '5', 14, 'LOA16-T05-DKXI'),
(76, 16, '6', 2, 'LOA16-T06-D3FV'),
(77, 16, '7.5', 14, 'LOA16-T75-AKIV'),
(78, 16, '8', 18, 'LOA16-T08-4F62'),
(79, 16, '10.5', 14, 'LOA16-T105-1ZRK');

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
(6, 3, 1),
(7, 7, 3),
(8, 7, 9);

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
  ADD KEY `id_producto` (`id_producto`),
  ADD KEY `id_variante` (`id_variante`);

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
  ADD UNIQUE KEY `uniq_producto_talla` (`id_producto`,`talla`),
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `pedido`
--
ALTER TABLE `pedido`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=80;

--
-- AUTO_INCREMENT for table `wishlist`
--
ALTER TABLE `wishlist`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD CONSTRAINT `detalle_pedido_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedido` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `detalle_pedido_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `detalle_pedido_ibfk_3` FOREIGN KEY (`id_variante`) REFERENCES `variante_producto` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;

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
