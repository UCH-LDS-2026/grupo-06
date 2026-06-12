-- ============================================================
-- Sistema Costura - Script completo
-- Crear base de datos, tablas e insertar datos de ejemplo
-- ============================================================

START TRANSACTION;

CREATE DATABASE IF NOT EXISTS `sistema_costura` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `sistema_costura`;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- --------------------------------------------------------
-- Tabla: administrador
-- --------------------------------------------------------

DROP TABLE IF EXISTS `administrador`;
CREATE TABLE `administrador` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `contrasena` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `administrador` (`nombre`, `email`, `contrasena`) VALUES
('Costurera Admin', 'admin@taller.com', '$2y$10$0kf1V9v5jHvzv9vIsJzWTe1pR5XQatJvRU4aaMjQRYvQoh2wRAVYO');

-- --------------------------------------------------------
-- Tabla: cliente
-- --------------------------------------------------------

DROP TABLE IF EXISTS `cliente`;
CREATE TABLE `cliente` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `telefono` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `cliente` (`nombre`, `telefono`, `email`) VALUES
('María García', '2994001122', 'maria@mail.com'),
('Laura Pérez', '2994003344', 'laura@mail.com'),
('Agostina Ruiz', '2614005566', 'agostina@mail.com'),
('usuario1', '02616938099', 'delfinaibanezgiordano@gmail.com');

-- --------------------------------------------------------
-- Tabla: ficha_cliente
-- --------------------------------------------------------

DROP TABLE IF EXISTS `ficha_cliente`;
CREATE TABLE `ficha_cliente` (
  `id` int NOT NULL AUTO_INCREMENT,
  `cliente_id` int NOT NULL,
  `talle` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contorno_pecho` decimal(5,2) DEFAULT NULL,
  `contorno_cintura` decimal(5,2) DEFAULT NULL,
  `contorno_cadera` decimal(5,2) DEFAULT NULL,
  `largo_manga` decimal(5,2) DEFAULT NULL,
  `largo_espalda` decimal(5,2) DEFAULT NULL,
  `largo_pantalon` decimal(5,2) DEFAULT NULL,
  `observaciones_cliente` text COLLATE utf8mb4_unicode_ci,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `cliente_id` (`cliente_id`),
  CONSTRAINT `fk_ficha_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `ficha_cliente` (`cliente_id`, `talle`, `contorno_pecho`, `contorno_cintura`, `contorno_cadera`, `largo_manga`, `largo_espalda`, `largo_pantalon`) VALUES
(1, 'M',  90.00, 70.00, 96.00, 58.00, NULL,  NULL),
(2, 'S',  85.00, 65.00, 92.00, 56.00, NULL,  NULL),
(3, 'L',  95.00, 75.00, 100.00, 60.00, 40.00, 98.00),
(4, NULL, 53.50, 53.50, NULL,  45.00, 53.50, 46.00);

-- --------------------------------------------------------
-- Tabla: encargo
-- --------------------------------------------------------

DROP TABLE IF EXISTS `encargo`;
CREATE TABLE `encargo` (
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
  CONSTRAINT `fk_encargo_admin` FOREIGN KEY (`administrador_id`) REFERENCES `administrador` (`id`),
  CONSTRAINT `fk_encargo_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `encargo` (`administrador_id`, `cliente_id`, `tipo`, `descripcion`, `observaciones_encargo`, `fecha_entrega`, `monto_total`, `sena`, `estado`) VALUES
(1, 1, 'Vestido',   'Vestido de fiesta azul marino',        'Sin cierre en la espalda', '2026-07-15', 25000.00, 10000.00, 'en_proceso'),
(1, 2, 'Pantalón',  'Pantalón de vestir negro',              NULL,                       '2026-07-10', 12000.00,  5000.00, 'pendiente'),
(1, NULL, 'Arreglo','Ruedo de jeans sin cliente registrado', NULL,                       '2026-07-08',  3000.00,     0.00, 'pendiente'),
(1, 3, 'Camisa',    'Camisa de lino beige manga corta',      NULL,                       '2026-07-20', 15000.00,  5000.00, 'pendiente'),
(1, 4, 'Falda',     'Falda plisada color verde',             'Largo hasta la rodilla',   '2026-07-25',  9000.00,  3000.00, 'en_proceso'),
(1, 1, 'Arreglo',   'Achique de vestido de novia',           NULL,                       '2026-06-30',  8000.00,  4000.00, 'listo'),
(1, 2, 'Vestido',   'Vestido casual estampado flores',       NULL,                       '2026-08-01', 18000.00,  6000.00, 'pendiente');

-- --------------------------------------------------------
-- Tabla: observacion
-- --------------------------------------------------------

DROP TABLE IF EXISTS `observacion`;
CREATE TABLE `observacion` (
  `id` int NOT NULL AUTO_INCREMENT,
  `encargo_id` int NOT NULL,
  `detalle` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `fecha` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_observacion_encargo` FOREIGN KEY (`encargo_id`) REFERENCES `encargo` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `observacion` (`encargo_id`, `detalle`) VALUES
(1, 'La clienta pidió que el escote sea un poco más alto'),
(1, 'Prueba de vestuario pactada para el 10/07'),
(2, 'El pantalón lleva pinzas adelante'),
(4, 'Confirmar talle antes de cortar'),
(6, 'Urgente, evento el 2 de julio');

-- --------------------------------------------------------
-- Tabla: alerta
-- --------------------------------------------------------

DROP TABLE IF EXISTS `alerta`;
CREATE TABLE `alerta` (
  `id` int NOT NULL AUTO_INCREMENT,
  `administrador_id` int NOT NULL,
  `encargo_id` int DEFAULT NULL,
  `mensaje` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` enum('vencimiento','estado','pago') COLLATE utf8mb4_unicode_ci NOT NULL,
  `leida` tinyint(1) DEFAULT '0',
  `fecha` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_alerta_admin` FOREIGN KEY (`administrador_id`) REFERENCES `administrador` (`id`),
  CONSTRAINT `fk_alerta_encargo` FOREIGN KEY (`encargo_id`) REFERENCES `encargo` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `alerta` (`administrador_id`, `encargo_id`, `mensaje`, `tipo`, `leida`) VALUES
(1, 1, 'Vestido de María García vence en 3 días y tiene saldo pendiente.', 'vencimiento', 0),
(1, 2, 'Pantalón de Laura Pérez vence en 2 días y tiene saldo pendiente.', 'vencimiento', 0),
(1, 6, 'Arreglo de vestido de novia está listo para entregar.',            'estado',      0),
(1, 4, 'Camisa de Agostina Ruiz tiene saldo pendiente.',                   'pago',        1);

-- --------------------------------------------------------

COMMIT;