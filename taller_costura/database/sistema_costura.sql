-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 11-06-2026 a las 23:26:40
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
  KEY `administrador_id` (`administrador_id`),
  KEY `encargo_id` (`encargo_id`)
) ENGINE=MyISAM AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `alerta`
--

INSERT INTO `alerta` (`id`, `administrador_id`, `encargo_id`, `mensaje`, `tipo`, `leida`, `fecha`) VALUES
(6, 1, 5, 'camisa de María García vence en 1 día/s y tiene saldo pendiente.', 'vencimiento', 0, '2026-06-11 22:56:29'),
(5, 1, 4, 'Top de Laura Pérez vence en 1 día/s y tiene saldo pendiente.', 'vencimiento', 0, '2026-06-11 22:56:29'),
(7, 1, 7, 'Pantalon Jean de Laura Pérez vence en 1 día/s y tiene saldo pendiente.', 'vencimiento', 0, '2026-06-11 22:57:42'),
(8, 1, 6, 'Pantalon Jean de Agostina vence en 1 día/s y tiene saldo pendiente.', 'vencimiento', 0, '2026-06-11 23:07:11'),
(9, 1, 8, 'Pantalon Jean de Laura Pérez vence en 1 día/s y tiene saldo pendiente.', 'vencimiento', 0, '2026-06-11 23:07:48'),
(10, 1, 9, 'aaaa de usuario1 vence en 1 día/s y tiene saldo pendiente.', 'vencimiento', 0, '2026-06-11 23:08:20'),
(11, 1, 10, 'asasa de Agostina vence en 1 día/s.', 'vencimiento', 1, '2026-06-11 23:10:29');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

CREATE TABLE IF NOT EXISTS `pago` (
  `id` int NOT NULL AUTO_INCREMENT,
  `encargo_id` int NOT NULL,
  `administrador_id` int NOT NULL DEFAULT 1,
  `monto` decimal(10,2) NOT NULL,
  `metodo` enum('efectivo','transferencia','tarjeta','otro') DEFAULT 'efectivo',
  `nota` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `encargo_id` (`encargo_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `pago` (`encargo_id`, `administrador_id`, `monto`, `metodo`, `nota`, `created_at`) VALUES
(1, 1, 10000.00, 'efectivo',      'Seña inicial', '2026-05-14 23:00:00'),
(2, 1,  5000.00, 'transferencia', 'Seña 50%',     '2026-05-15 10:30:00');