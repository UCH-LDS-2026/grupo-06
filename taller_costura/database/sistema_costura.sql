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
('Valentina Ríos',      '2994112233', 'vale.rios@gmail.com'),
('Sofía Mendoza',       '2994224455', 'sofi.mendoza@gmail.com'),
('Lucía Ferreyra',      '2614336677', 'lu.ferreyra@gmail.com'),
('Camila Bustos',       '2616448899', 'cami.bustos@gmail.com'),
('Florencia Alvarez',   '2994550011', 'flor.alvarez@gmail.com'),
('Matías Correa',       '2994661122', 'mati.correa@gmail.com'),
('Tomás Herrera',       '2614772233', 'tomas.herrera@gmail.com'),
('Agustín Molina',      '2616883344', 'agus.molina@gmail.com'),
('Julieta Paredes',     '2994994455', 'juli.paredes@gmail.com'),
('Renata Villanueva',   '2614005566', 'rena.villa@gmail.com');

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

INSERT INTO `ficha_cliente` (`cliente_id`, `talle`, `contorno_pecho`, `contorno_cintura`, `contorno_cadera`, `largo_manga`, `largo_espalda`, `largo_pantalon`, `observaciones_cliente`) VALUES
(1,  'S',  83.00, 63.00, 89.00,  55.00, 38.00, 96.00, 'Prefiere cortes entallados'),
(2,  'M',  90.00, 70.00, 96.00,  58.00, 40.00, 98.00, 'Alérgica a la lana'),
(3,  'L',  96.00, 76.00, 102.00, 60.00, 41.00, 100.00, 'Le gustan las telas livianas'),
(4,  'XS', 80.00, 60.00, 86.00,  53.00, 37.00, 94.00, NULL),
(5,  'M',  88.00, 68.00, 94.00,  57.00, 39.00, 97.00, 'Usa escotes moderados'),
(6,  'L',  100.00, 82.00, 104.00, 62.00, 42.00, 102.00, NULL),
(7,  'M',  91.00, 74.00, 97.00,  59.00, 40.00, 100.00, 'Prefiere ropa holgada'),
(8,  'S',  85.00, 65.00, 91.00,  56.00, 38.00, 97.00, NULL),
(9,  'M',  89.00, 69.00, 95.00,  57.00, 39.00, 98.00, 'Clienta frecuente, muy puntual'),
(10, 'S',  84.00, 64.00, 90.00,  55.00, 38.00, 96.00, 'Prefiere colores neutros');

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
  `metodo_pago` enum('efectivo','transferencia','tarjeta') COLLATE utf8mb4_unicode_ci DEFAULT 'efectivo',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_encargo_admin` FOREIGN KEY (`administrador_id`) REFERENCES `administrador` (`id`),
  CONSTRAINT `fk_encargo_cliente` FOREIGN KEY (`cliente_id`) REFERENCES `cliente` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `encargo` (`administrador_id`, `cliente_id`, `tipo`, `descripcion`, `observaciones_encargo`, `fecha_entrega`, `monto_total`, `sena`, `estado`, `metodo_pago`) VALUES

-- ENTREGADOS (saldo = 0, pagados completos)
(1, 1,  'Vestido de fiesta',       'Vestido largo azul marino con escote en V',         'Sin cierre, con corchetes en espalda',     '2026-06-10', 28000.00, 28000.00, 'entregado',  'transferencia'),
(1, 9,  'Pantalón de vestir',      'Pantalón negro con pinzas, tiro alto',               NULL,                                       '2026-06-15', 14000.00, 14000.00, 'entregado',  'efectivo'),

-- LISTOS para retirar (saldo pendiente, esperando que el cliente pase)
(1, 2,  'Vestido de novia',        'Vestido blanco con bordado en el corpiño',           'Prueba final aprobada, solo falta retirar', '2026-06-28', 85000.00, 40000.00, 'listo',      'transferencia'),
(1, 5,  'Saco sastre',             'Saco gris marengo con forro, talle M',               'Botones nacarados, no dorados',             '2026-07-01', 32000.00, 16000.00, 'listo',      'efectivo'),

-- EN PROCESO (en confección, sena cobrada)
(1, 3,  'Falda plisada',           'Falda midi verde oliva, plisado fino',               'Largo exacto hasta la rodilla',             '2026-07-12', 18000.00, 9000.00,  'en_proceso', 'efectivo'),
(1, 6,  'Camisa de lino',          'Camisa beige manga corta, escote mao',               NULL,                                        '2026-07-15', 15000.00, 7000.00,  'en_proceso', 'transferencia'),
(1, 10, 'Conjunto de baño',        'Malla entera negra con refuerzo interno',            'Tela elastizada importada del cliente',     '2026-07-18', 22000.00, 11000.00, 'en_proceso', 'tarjeta'),

-- PENDIENTES (todavía no se empezaron, con seña)
(1, 4,  'Vestido de egresadas',    'Vestido rojo corto con escote cruzado',              'Entrega urgente, evento el 25/07',          '2026-07-25', 35000.00, 15000.00, 'pendiente',  'efectivo'),
(1, 7,  'Pantalón cargo',          'Pantalón verde militar con bolsillos laterales',     NULL,                                        '2026-07-30', 20000.00, 8000.00,  'pendiente',  'transferencia'),
(1, 8,  'Blusa de seda',           'Blusa color champagne, manga larga con lazo',        'No usar tela sintética',                    '2026-08-05', 17000.00, 8500.00,  'pendiente',  'efectivo'),

-- PENDIENTE sin cliente (caso especial para mostrar alerta)
(1, NULL, 'Arreglo ruedo',         'Ruedo de jeans, cliente dejó sin datos',             NULL,                                        '2026-07-08',  3000.00,  0.00,    'pendiente',  'efectivo'),

-- PENDIENTE atrasado (fecha vencida, para mostrar badge "Atrasado")
(1, 2,  'Arreglo vestido',         'Achique de vestido de fiesta floreado',              'Clienta llamó dos veces, prioridad',        '2026-06-20', 8000.00,  4000.00, 'pendiente',  'efectivo'),

-- LISTOS con fecha vencida (sin retirar)
(1, 3,  'Vestido de cóctel',       'Vestido verde esmeralda, manga corta, talle L',      'Cierre lateral invisible',                  '2026-06-05', 24000.00, 12000.00, 'listo',      'efectivo'),
(1, 7,  'Pantalón de vestir',      'Pantalón gris oscuro con pinzas, tiro medio',        NULL,                                        '2026-06-08', 16000.00, 16000.00, 'listo',      'transferencia'),
(1, 4,  'Blusa con volados',       'Blusa blanca manga larga con volados en escote',     'No almidonar',                              '2026-06-12', 12000.00,  6000.00, 'listo',      'efectivo'),
(1, 10, 'Falda lápiz',             'Falda negra entallada con abertura atrás',           'Largo hasta la rodilla exacto',             '2026-06-18', 14000.00,  7000.00, 'listo',      'tarjeta'),
(1, 6,  'Camisa formal',           'Camisa blanca manga larga, cuello clásico',          NULL,                                        '2026-06-22', 13000.00, 13000.00, 'listo',      'transferencia');

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

INSERT INTO `observacion` (`encargo_id`, `detalle`, `fecha`) VALUES
(3,  'Primera prueba de vestuario realizada, queda perfecto',            '2026-06-05 11:00:00'),
(3,  'Segunda prueba: ajuste en el corpiño, un cm más de busto',        '2026-06-18 10:30:00'),
(3,  'Prueba final aprobada. Lista para retirar',                        '2026-06-25 16:00:00'),
(5,  'Tela cortada, comenzando confección',                              '2026-06-28 09:00:00'),
(5,  'Plisado terminado, falta coser cierre',                            '2026-07-02 14:00:00'),
(8,  'Clienta confirmó talle S tras medición',                           '2026-07-01 10:00:00'),
(12, 'Clienta no pasó a buscar, se la avisó por WhatsApp',              '2026-06-21 09:00:00'),
(12, 'Segunda llamada sin respuesta',                                    '2026-06-25 11:00:00');

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
(1, 3,  'Vestido de novia de Sofía Mendoza está listo y tiene saldo pendiente de $25.000.',        'estado',      0),
(1, 4,  'Saco sastre de Florencia Alvarez está listo para retirar. Saldo pendiente: $16.000.',     'estado',      0),
(1, 8,  'Vestido de egresadas de Camila Bustos vence el 25/07. Marcar como urgente.',              'vencimiento', 0),
(1, 12, 'Arreglo de Sofía Mendoza está ATRASADO. Fecha de entrega: 20/06.',                       'vencimiento', 0),
(1, 11, 'Hay un encargo sin cliente asignado (ruedo de jeans).',                                   'pago',        0),
(1, 6,  'Camisa de Matías Correa en proceso. Vence el 15/07.',                                     'vencimiento', 1),
(1, 9,  'Pantalón cargo de Tomás Herrera registrado. Seña cobrada: $8.000.',                       'pago',        1),
(1, 13, 'Vestido de cóctel de Lucía Ferreyra listo desde el 05/06. Sin retirar.',                 'estado',      0),
(1, 15, 'Blusa con volados de Camila Bustos lista desde el 12/06. Sin retirar.',                  'estado',      0),
(1, 16, 'Falda lápiz de Renata Villanueva lista desde el 18/06. Sin retirar.',                    'estado',      0);

-- --------------------------------------------------------
-- Tabla: pago
-- --------------------------------------------------------

DROP TABLE IF EXISTS `pago`;
CREATE TABLE `pago` (
  `id` int NOT NULL AUTO_INCREMENT,
  `encargo_id` int NOT NULL,
  `administrador_id` int NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `metodo` enum('efectivo','transferencia','tarjeta') NOT NULL DEFAULT 'efectivo',
  `nota` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `encargo_id` (`encargo_id`),
  KEY `administrador_id` (`administrador_id`),
  CONSTRAINT `fk_pago_encargo` FOREIGN KEY (`encargo_id`) REFERENCES `encargo` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pago_admin` FOREIGN KEY (`administrador_id`) REFERENCES `administrador` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `pago` (`encargo_id`, `administrador_id`, `monto`, `metodo`, `nota`, `fecha`) VALUES
(3,  1, 20000.00, 'transferencia', 'Segundo pago antes de la prueba',   '2026-06-20 10:00:00'),
(12, 1,  2000.00, 'efectivo',      'Abono parcial, prometió el resto',  '2026-06-22 15:30:00');

UPDATE `encargo` SET `sena` = 60000.00 WHERE `id` = 3;
UPDATE `encargo` SET `sena` =  6000.00 WHERE `id` = 12;

COMMIT;