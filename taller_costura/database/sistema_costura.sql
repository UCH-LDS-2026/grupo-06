-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 14-05-2026 a las 22:53:34
-- Versión del servidor: 8.4.7
-- Versión de PHP: 8.3.28

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
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contrasena` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `administrador`
--

INSERT INTO `administrador` (`id`, `nombre`, `email`, `contrasena`, `created_at`) VALUES
(1, 'Costurera Admin', 'admin@taller.com', '$2y$10$ejemploHashSeguro12345', '2026-05-14 22:34:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alerta`
--

DROP TABLE IF EXISTS `alerta`;
CREATE TABLE IF NOT EXISTS `alerta` (
  `id` int NOT NULL AUTO_INCREMENT,
  `administrador_id` int NOT NULL,
  `encargo_id` int DEFAULT NULL,
  `mensaje` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('vencimiento','estado','pago') COLLATE utf8mb4_unicode_ci NOT NULL,
  `leida` tinyint(1) DEFAULT '0',
  `fecha` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `administrador_id` (`administrador_id`),
  KEY `encargo_id` (`encargo_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `alerta`
--

INSERT INTO `alerta` (`id`, `administrador_id`, `encargo_id`, `mensaje`, `tipo`, `leida`, `fecha`) VALUES
(1, 1, 2, 'Encargo de Laura vence en 3 días', 'vencimiento', 0, '2026-05-14 22:34:05'),
(2, 1, 3, 'Encargo de arreglo vence mañana', 'vencimiento', 0, '2026-05-14 22:34:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

DROP TABLE IF EXISTS `cliente`;
CREATE TABLE IF NOT EXISTS `cliente` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`id`, `nombre`, `telefono`, `email`, `created_at`) VALUES
(1, 'María García', '2994001122', 'maria@mail.com', '2026-05-14 22:34:05'),
(2, 'Laura Pérez', '2994003344', 'laura@mail.com', '2026-05-14 22:34:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `encargo`
--

DROP TABLE IF EXISTS `encargo`;
CREATE TABLE IF NOT EXISTS `encargo` (
  `id` int NOT NULL AUTO_INCREMENT,
  `administrador_id` int NOT NULL,
  `cliente_id` int DEFAULT NULL,
  `tipo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` text COLLATE utf8mb4_unicode_ci,
  `observaciones_encargo` text COLLATE utf8mb4_unicode_ci,
  `fecha_entrega` date NOT NULL,
  `monto_total` decimal(10,2) DEFAULT '0.00',
  `sena` decimal(10,2) DEFAULT '0.00',
  `estado` enum('pendiente','en_proceso','listo','entregado') COLLATE utf8mb4_unicode_ci DEFAULT 'pendiente',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `administrador_id` (`administrador_id`),
  KEY `cliente_id` (`cliente_id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `encargo`
--

INSERT INTO `encargo` (`id`, `administrador_id`, `cliente_id`, `tipo`, `descripcion`, `observaciones_encargo`, `fecha_entrega`, `monto_total`, `sena`, `estado`, `created_at`) VALUES
(1, 1, 1, 'Vestido', 'Vestido de fiesta azul marino', 'Sin cierre en la espalda', '2025-06-15', 25000.00, 10000.00, 'en_proceso', '2026-05-14 22:34:05'),
(2, 1, 2, 'Pantalón', 'Pantalón de vestir negro', NULL, '2025-06-10', 12000.00, 5000.00, 'pendiente', '2026-05-14 22:34:05'),
(3, 1, NULL, 'Arreglo', 'Ruedo de jeans sin cliente registrado', NULL, '2025-06-08', 3000.00, 0.00, 'pendiente', '2026-05-14 22:34:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ficha_cliente`
--

DROP TABLE IF EXISTS `ficha_cliente`;
CREATE TABLE IF NOT EXISTS `ficha_cliente` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cliente_id` int NOT NULL,
  `talle` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contorno_pecho` decimal(5,2) DEFAULT NULL,
  `contorno_cintura` decimal(5,2) DEFAULT NULL,
  `contorno_cadera` decimal(5,2) DEFAULT NULL,
  `largo_manga` decimal(5,2) DEFAULT NULL,
  `observaciones_cliente` text COLLATE utf8mb4_unicode_ci,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cliente_id` (`cliente_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `ficha_cliente`
--

INSERT INTO `ficha_cliente` (`id`, `cliente_id`, `talle`, `contorno_pecho`, `contorno_cintura`, `contorno_cadera`, `largo_manga`, `observaciones_cliente`, `updated_at`) VALUES
(1, 1, 'M', 90.00, 70.00, 96.00, 58.00, NULL, '2026-05-14 22:34:05'),
(2, 2, 'S', 85.00, 65.00, 92.00, 56.00, NULL, '2026-05-14 22:34:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `observacion`
--

DROP TABLE IF EXISTS `observacion`;
CREATE TABLE IF NOT EXISTS `observacion` (
  `id` int NOT NULL AUTO_INCREMENT,
  `encargo_id` int NOT NULL,
  `detalle` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `encargo_id` (`encargo_id`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `observacion`
--

INSERT INTO `observacion` (`id`, `encargo_id`, `detalle`, `fecha`) VALUES
(1, 1, 'La clienta pidió que el escote sea un poco más alto', '2026-05-14 22:34:05'),
(2, 1, 'Prueba de vestuario pactada para el 10/06', '2026-05-14 22:34:05');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
