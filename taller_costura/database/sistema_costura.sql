

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 26-06-2026 a las 19:30:17
-- Versión del servidor: 9.1.0
-- Versión de PHP: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `sistema_costura`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administrador`
--

DROP TABLE IF EXISTS `administrador`;
CREATE TABLE IF NOT EXISTS `administrador` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contrasena` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `administrador`
--

INSERT INTO `administrador` (`id`, `nombre`, `email`, `contrasena`, `created_at`) VALUES
(1, 'Costurera Admin', 'admin@taller.com', '$2y$10$0kf1V9v5jHvzv9vIsJzWTe1pR5XQatJvRU4aaMjQRYvQoh2wRAVYO', '2026-06-22 14:34:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alerta`
--

DROP TABLE IF EXISTS `alerta`;
CREATE TABLE IF NOT EXISTS `alerta` (
  `id` int NOT NULL AUTO_INCREMENT,
  `administrador_id` int NOT NULL,
  `encargo_id` int DEFAULT NULL,
  `mensaje` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('vencimiento','estado','pago') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `leida` tinyint(1) DEFAULT '0',
  `fecha` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_alerta_admin` (`administrador_id`),
  KEY `fk_alerta_encargo` (`encargo_id`)
) ENGINE=InnoDB AUTO_INCREMENT=36 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `alerta`
--

INSERT INTO `alerta` (`id`, `administrador_id`, `encargo_id`, `mensaje`, `tipo`, `leida`, `fecha`) VALUES
(1, 1, NULL, 'Vestido de María García vence en 3 días y tiene saldo pendiente.', 'vencimiento', 1, '2026-06-22 14:34:05'),
(2, 1, 2, 'Pantalón de Laura Pérez vence en 2 días y tiene saldo pendiente.', 'vencimiento', 1, '2026-06-22 14:34:05'),
(3, 1, 6, 'Arreglo de vestido de novia está listo para entregar.', 'estado', 1, '2026-06-22 14:34:05'),
(4, 1, 4, 'Camisa de Agostina Ruiz tiene saldo pendiente.', 'pago', 1, '2026-06-22 14:34:05'),
(5, 1, 3, 'Arreglo está listo para entregar.', 'estado', 1, '2026-06-22 14:41:02'),
(6, 1, 2, 'Pantalón de Laura Pérez está listo para entregar.', 'estado', 1, '2026-06-22 14:41:15'),
(7, 1, NULL, 'La clienta candela aguilar no tiene ficha de medidas.', '', 1, '2026-06-22 23:51:06'),
(8, 1, 8, 'faldaa vence en 3 día/s.', 'vencimiento', 1, '2026-06-23 03:04:47'),
(9, 1, 6, 'Arreglo de María García está listo para entregar.', 'estado', 1, '2026-06-23 04:01:14'),
(10, 1, 8, 'faldaa vence en 2 día/s.', 'vencimiento', 1, '2026-06-24 20:23:25'),
(11, 1, NULL, 'La clienta usuario1 no tiene ficha de medidas.', '', 1, '2026-06-24 21:26:44'),
(12, 1, NULL, 'La clienta asdsadas no tiene ficha de medidas.', '', 1, '2026-06-24 22:44:58'),
(13, 1, NULL, 'La clienta sgfsgfdgdfgdf no tiene ficha de medidas.', '', 1, '2026-06-24 22:44:58'),
(14, 1, NULL, 'La clienta erwerwet no tiene ficha de medidas.', '', 1, '2026-06-24 23:09:04'),
(15, 1, NULL, 'La clienta eeeeeeeeeeeeeeeeee no tiene ficha de medidas.', '', 1, '2026-06-24 23:09:04'),
(16, 1, 8, 'faldaa vence en 1 día/s.', 'vencimiento', 1, '2026-06-25 04:01:13'),
(17, 1, NULL, 'La clienta mariluvina no tiene ficha de medidas.', '', 1, '2026-06-25 04:42:25'),
(18, 1, NULL, 'tanga de candela aguilar vence en 1 día/s.', 'vencimiento', 1, '2026-06-25 04:43:15'),
(19, 1, NULL, 'La clienta 55555555555 no tiene ficha de medidas.', '', 1, '2026-06-25 04:56:00'),
(20, 1, NULL, 'La clienta usuario milefa no tiene ficha de medidas.', '', 1, '2026-06-25 04:56:33'),
(21, 1, NULL, 'La clienta usuario56 no tiene ficha de medidas.', '', 1, '2026-06-25 14:15:02'),
(22, 1, NULL, 'La clienta usuario90 no tiene ficha de medidas.', '', 1, '2026-06-25 14:15:02'),
(23, 1, NULL, 'La clienta usuario24 no tiene ficha de medidas.', '', 1, '2026-06-25 14:15:02'),
(24, 1, NULL, 'La clienta usuario50 no tiene ficha de medidas.', '', 1, '2026-06-25 14:15:02'),
(25, 1, NULL, 'La clienta usuario400 no tiene ficha de medidas.', '', 1, '2026-06-25 14:15:02'),
(26, 1, 8, 'faldaa tiene saldo pendiente.', 'pago', 1, '2026-06-25 22:48:12'),
(27, 1, NULL, 'tanga de usuario56 tiene saldo pendiente.', 'pago', 1, '2026-06-25 23:41:41'),
(28, 1, 3, 'Arreglo tiene saldo pendiente.', 'pago', 1, '2026-06-25 23:42:15'),
(29, 1, 10, 'faldaa de usuario1 está listo para entregar.', 'estado', 1, '2026-06-26 00:30:53'),
(30, 1, 8, 'faldaa vence hoy.', 'vencimiento', 1, '2026-06-26 03:00:12'),
(31, 1, 13, 'mariguana de usuario45 vence hoy.', 'vencimiento', 1, '2026-06-26 03:00:12'),
(32, 1, 10, 'faldaa de usuario1 venció hace 3 día/s y tiene saldo pendiente de $5.000.', 'pago', 1, '2026-06-26 03:00:12'),
(33, 1, 11, 'faldaa venció hace 1 día/s y tiene saldo pendiente de $5.000.', 'pago', 1, '2026-06-26 03:00:12'),
(34, 1, 12, 'pantalon cargo venció hace 1 día/s y tiene saldo pendiente de $5.000.', 'pago', 1, '2026-06-26 03:00:12'),
(35, 1, 14, 'falda acuadrille de usuario400 vence hoy.', 'vencimiento', 0, '2026-06-26 03:24:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

DROP TABLE IF EXISTS `cliente`;
CREATE TABLE IF NOT EXISTS `cliente` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`id`, `nombre`, `telefono`, `email`, `created_at`) VALUES
(1, 'usuario503', '2994001122', 'maria@mail.com', '2026-06-22 14:34:04'),
(2, 'usuario15', '2994003344', 'laura@mail.com', '2026-06-22 14:34:04'),
(3, 'usuario567', '2615059493', 'agostina@mail.com', '2026-06-22 14:34:04'),
(4, 'usuario1', '02616938099', 'delfinaibanezgiordano@gmail.com', '2026-06-22 14:34:04'),
(5, 'usuario56', '2615059493', 'candelobaaguile@gmail.com', '2026-06-22 23:50:06'),
(7, 'usuario90', '253546343', 'sadada@asdasdasda.com', '2026-06-24 22:44:23'),
(9, 'usuario24', '2615363', 'erterterter@gmail.com', '2026-06-24 23:08:09'),
(10, 'usuario50', '272637248', 'eeeeeeeeee@gmail.com', '2026-06-24 23:08:54'),
(13, 'usuario45', '2616938099', 'fghdfhgdo@gmail.com', '2026-06-25 04:40:52'),
(14, 'usuario67', '2616938099', 'tttttttt@gmail.com', '2026-06-25 04:41:16'),
(15, 'usuario1', '5555555', 'juulios@gmail.com', '2026-06-25 04:53:23'),
(16, 'usuario400', '35337732', 'sdasads@gmial.com', '2026-06-25 04:56:33');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `encargo`
--

DROP TABLE IF EXISTS `encargo`;
CREATE TABLE IF NOT EXISTS `encargo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `administrador_id` int NOT NULL,
  `cliente_id` int DEFAULT NULL,
  `tipo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `observaciones_encargo` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `fecha_entrega` date NOT NULL,
  `monto_total` decimal(10,2) DEFAULT '0.00',
  `sena` decimal(10,2) DEFAULT '0.00',
  `estado` enum('pendiente','en_proceso','listo','entregado') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente',
  `metodo_pago` enum('efectivo','transferencia','tarjeta') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'efectivo',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_encargo_admin` (`administrador_id`),
  KEY `fk_encargo_cliente` (`cliente_id`)
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `encargo`
--

INSERT INTO `encargo` (`id`, `administrador_id`, `cliente_id`, `tipo`, `descripcion`, `observaciones_encargo`, `fecha_entrega`, `monto_total`, `sena`, `estado`, `metodo_pago`, `created_at`) VALUES
(2, 1, 2, 'Pantalón', 'Pantalón de vestir negro', NULL, '2026-07-10', 12000.00, 10000.00, 'entregado', 'efectivo', '2026-06-22 14:34:04'),
(3, 1, NULL, 'Arreglo', 'Ruedo de jeans sin cliente registrado', NULL, '2026-07-08', 3000.00, 3000.00, 'entregado', 'efectivo', '2026-06-22 14:34:04'),
(4, 1, 3, 'Camisa', 'Camisa de lino beige manga corta', NULL, '2026-07-20', 15000.00, 5000.00, 'pendiente', 'efectivo', '2026-06-22 14:34:04'),
(5, 1, 4, 'Falda', 'Falda plisada color verde', 'Largo hasta la rodilla', '2026-07-25', 9000.00, 3000.00, 'en_proceso', 'efectivo', '2026-06-22 14:34:04'),
(6, 1, 1, 'Arreglo', 'Achique de vestido de novia', NULL, '2026-06-30', 8000.00, 8000.00, 'en_proceso', 'efectivo', '2026-06-22 14:34:04'),
(7, 1, 2, 'Vestido', 'Vestido casual estampado flores', NULL, '2026-08-01', 18000.00, 6000.00, 'en_proceso', 'efectivo', '2026-06-22 14:34:04'),
(8, 1, NULL, 'faldaa', '2222', '22222', '2026-06-26', 2000.00, 2000.00, 'pendiente', 'transferencia', '2026-06-22 23:54:53'),
(10, 1, 15, 'faldaa', 'gvj', 'nhgjgjghh', '2026-06-23', 10000.00, 5000.00, 'listo', 'efectivo', '2026-06-26 00:25:10'),
(11, 1, NULL, 'faldaa', 'retret', 'ertretre', '2026-06-25', 10000.00, 5000.00, 'pendiente', 'transferencia', '2026-06-26 00:25:43'),
(12, 1, NULL, 'pantalon cargo', 'retret', 'ertretre', '2026-06-25', 10000.00, 5000.00, 'pendiente', 'efectivo', '2026-06-26 00:26:05'),
(13, 1, 13, 'mariguana', 'sdfsdfs', 'fsdfsd', '2026-06-26', 1000000.00, 8767.00, 'pendiente', 'efectivo', '2026-06-26 02:26:10'),
(14, 1, 16, 'falda acuadrille', 'sdfsdfs', 'fsdfsd', '2026-06-26', 1000000.00, 8767.00, 'pendiente', 'efectivo', '2026-06-26 03:24:52');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ficha_cliente`
--

DROP TABLE IF EXISTS `ficha_cliente`;
CREATE TABLE IF NOT EXISTS `ficha_cliente` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cliente_id` int NOT NULL,
  `talle` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contorno_pecho` decimal(5,2) DEFAULT NULL,
  `contorno_cintura` decimal(5,2) DEFAULT NULL,
  `contorno_cadera` decimal(5,2) DEFAULT NULL,
  `largo_manga` decimal(5,2) DEFAULT NULL,
  `largo_espalda` decimal(5,2) DEFAULT NULL,
  `largo_pantalon` decimal(5,2) DEFAULT NULL,
  `observaciones_cliente` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cliente_id` (`cliente_id`)
) ENGINE=InnoDB AUTO_INCREMENT=8 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `ficha_cliente`
--

INSERT INTO `ficha_cliente` (`id`, `cliente_id`, `talle`, `contorno_pecho`, `contorno_cintura`, `contorno_cadera`, `largo_manga`, `largo_espalda`, `largo_pantalon`, `observaciones_cliente`, `updated_at`) VALUES
(1, 1, 'M', 90.00, 70.00, 96.00, 58.00, NULL, NULL, NULL, '2026-06-22 14:34:04'),
(2, 2, 'S', 85.00, 65.00, 92.00, 56.00, NULL, NULL, NULL, '2026-06-22 14:34:04'),
(3, 3, 'L', 95.00, 75.00, 100.00, 60.00, 40.00, 98.00, NULL, '2026-06-22 14:34:04'),
(4, 4, NULL, 53.50, 53.50, NULL, 45.00, 53.50, 46.00, NULL, '2026-06-22 14:34:04'),
(6, 13, NULL, 5.00, 5.00, 8.00, 98.00, 5.00, 8.00, NULL, '2026-06-25 04:40:52'),
(7, 14, NULL, 5.00, 5.00, 8.00, 98.00, 5.00, 8.00, NULL, '2026-06-25 04:41:16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `observacion`
--

DROP TABLE IF EXISTS `observacion`;
CREATE TABLE IF NOT EXISTS `observacion` (
  `id` int NOT NULL AUTO_INCREMENT,
  `encargo_id` int NOT NULL,
  `detalle` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `fk_observacion_encargo` (`encargo_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `observacion`
--

INSERT INTO `observacion` (`id`, `encargo_id`, `detalle`, `fecha`) VALUES
(3, 2, 'El pantalón lleva pinzas adelante', '2026-06-22 14:34:04'),
(4, 4, 'Confirmar talle antes de cortar', '2026-06-22 14:34:04'),
(5, 6, 'Urgente, evento el 2 de julio', '2026-06-22 14:34:04');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pago`
--

DROP TABLE IF EXISTS `pago`;
CREATE TABLE IF NOT EXISTS `pago` (
  `id` int NOT NULL AUTO_INCREMENT,
  `encargo_id` int NOT NULL,
  `administrador_id` int NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `metodo` enum('efectivo','transferencia','tarjeta') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'efectivo',
  `nota` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `encargo_id` (`encargo_id`),
  KEY `administrador_id` (`administrador_id`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `pago`
--

INSERT INTO `pago` (`id`, `encargo_id`, `administrador_id`, `monto`, `metodo`, `nota`, `fecha`) VALUES
(1, 2, 1, 5000.00, 'efectivo', NULL, '2026-06-23 04:00:27'),
(2, 6, 1, 3000.00, 'efectivo', NULL, '2026-06-23 04:00:39'),
(3, 6, 1, 1000.00, 'efectivo', NULL, '2026-06-23 04:00:50'),
(4, 8, 1, 1500.00, 'efectivo', NULL, '2026-06-25 22:48:12'),
(5, 8, 1, 100.00, 'efectivo', NULL, '2026-06-25 22:48:45'),
(6, 8, 1, 398.00, 'efectivo', NULL, '2026-06-25 23:40:57'),
(9, 3, 1, 1000.00, 'efectivo', NULL, '2026-06-25 23:42:15'),
(10, 3, 1, 1000.00, 'transferencia', NULL, '2026-06-25 23:42:26'),
(11, 3, 1, 1000.00, 'tarjeta', NULL, '2026-06-25 23:42:34'),
(12, 14, 1, 8767.00, 'efectivo', 'Seña inicial', '2026-06-26 03:24:52');

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `alerta`
--
ALTER TABLE `alerta`
  ADD CONSTRAINT `fk_alerta_admin` FOREIGN KEY (`administrador_id`) REFERENCES `administrador` (`id`),
  ADD CONSTRAINT `fk_alerta_encargo` FOREIGN KEY (`encargo_id`) REFERENCES `encargo` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `encargo`
--
ALTER TABLE `encargo`
  ADD CONSTRAINT `fk_encargo_admin` FOREIGN KEY (`administrador_id`) REFERENCES `administrador` (`id`),
  ADD CONSTRAINT `fk_encargo_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`) ON DELETE SET NULL;

--
-- Filtros para la tabla `ficha_cliente`
--
ALTER TABLE `ficha_cliente`
  ADD CONSTRAINT `fk_ficha_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `observacion`
--
ALTER TABLE `observacion`
  ADD CONSTRAINT `fk_observacion_encargo` FOREIGN KEY (`encargo_id`) REFERENCES `encargo` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pago`
--
ALTER TABLE `pago`
  ADD CONSTRAINT `fk_pago_admin` FOREIGN KEY (`administrador_id`) REFERENCES `administrador` (`id`),
  ADD CONSTRAINT `fk_pago_encargo` FOREIGN KEY (`encargo_id`) REFERENCES `encargo` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
