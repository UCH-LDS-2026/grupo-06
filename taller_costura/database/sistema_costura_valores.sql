//esta base  de datos e sla base de datos orignal con atributos cargados en el sistema //


-- ============================================================
-- SEED - sistema_costura
-- Datos de prueba realistas
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;
TRUNCATE TABLE pago;
TRUNCATE TABLE observacion;
TRUNCATE TABLE alerta;
TRUNCATE TABLE ficha_cliente;
TRUNCATE TABLE encargo;
TRUNCATE TABLE cliente;
DELETE FROM administrador WHERE id != 1;
SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- CLIENTES (12) — 3 sin ficha de medidas
-- ============================================================

INSERT INTO `cliente` (`id`, `nombre`, `telefono`, `email`, `created_at`) VALUES
(1,  'Valentina Moreno',    '2994101122', 'valentina.moreno@gmail.com',   '2026-05-10 10:00:00'),
(2,  'Laura Fernández',     '2994203344', 'laura.fernandez@gmail.com',    '2026-05-12 11:00:00'),
(3,  'Agostina Ruiz',       '2994305566', 'agostina.ruiz@gmail.com',      '2026-05-15 09:30:00'),
(4,  'Camila Sánchez',      '2994407788', 'camila.sanchez@gmail.com',     '2026-05-18 14:00:00'),
(5,  'Florencia Gómez',     '2994509900', 'florencia.gomez@gmail.com',    '2026-05-20 16:00:00'),
(6,  'Martina López',       '2994601234', 'martina.lopez@gmail.com',      '2026-05-22 10:30:00'),
(7,  'Sofía Herrera',       '2994703456', 'sofia.herrera@gmail.com',      '2026-05-25 12:00:00'),
(8,  'Luciana Torres',      '2994805678', 'luciana.torres@gmail.com',     '2026-06-01 09:00:00'),
(9,  'Natalia Castro',      '2994907890', 'natalia.castro@gmail.com',     '2026-06-03 11:30:00'),
(10, 'Carolina Díaz',       '2994008901', 'carolina.diaz@gmail.com',      '2026-06-05 15:00:00'),
(11, 'Ana Belén Quiroga',   '2994109012', 'anabelen.quiroga@gmail.com',   '2026-06-10 10:00:00'),
(12, 'Romina Villanueva',   '2994200123', 'romina.villanueva@gmail.com',  '2026-06-15 13:00:00');

-- ============================================================
-- FICHAS DE MEDIDAS — clientes 1 al 9 tienen ficha
-- clientes 10, 11, 12 SIN ficha (para alertas)
-- ============================================================

INSERT INTO `ficha_cliente` (`id`, `cliente_id`, `talle`, `contorno_pecho`, `contorno_cintura`, `contorno_cadera`, `largo_manga`, `largo_espalda`, `largo_pantalon`, `observaciones_cliente`, `updated_at`) VALUES
(1, 1,  'M',  90.00, 70.00,  96.00, 58.00, 40.00, 98.00, NULL,                          '2026-05-10 10:30:00'),
(2, 2,  'S',  85.00, 65.00,  92.00, 56.00, 38.00, 95.00, NULL,                          '2026-05-12 11:30:00'),
(3, 3,  'L',  95.00, 75.00, 100.00, 60.00, 42.00, 100.00, 'Prefiere telas livianas',    '2026-05-15 10:00:00'),
(4, 4,  'M',  88.00, 68.00,  94.00, 57.00, 39.00, 97.00, NULL,                          '2026-05-18 14:30:00'),
(5, 5,  'XS', 80.00, 60.00,  86.00, 54.00, 36.00, 90.00, 'Cintura muy marcada',         '2026-05-20 16:30:00'),
(6, 6,  'L',  97.00, 78.00, 103.00, 61.00, 43.00, 102.00, NULL,                         '2026-05-22 11:00:00'),
(7, 7,  'M',  89.00, 69.00,  95.00, 58.00, 40.00, 98.00, 'Hombros anchos',              '2026-05-25 12:30:00'),
(8, 8,  'S',  83.00, 63.00,  90.00, 55.00, 37.00, 93.00, NULL,                          '2026-06-01 09:30:00'),
(9, 9,  'XL', 102.00, 82.00, 108.00, 62.00, 44.00, 104.00, 'Prefiere pinzas adelante',  '2026-06-03 12:00:00');
-- clientes 10, 11, 12 no tienen ficha → disparan alerta

-- ============================================================
-- ENCARGOS (13 encargos, todos los estados cubiertos)
-- ============================================================

INSERT INTO `encargo` (`id`, `administrador_id`, `cliente_id`, `tipo`, `descripcion`, `observaciones_encargo`, `fecha_entrega`, `monto_total`, `sena`, `estado`, `metodo_pago`, `created_at`) VALUES
-- ENTREGADOS (pagos completos)
(1,  1, 1, 'Vestido de fiesta',    'Vestido largo negro con escote en V',        'Cierre invisible en espalda',   '2026-06-10', 25000.00, 10000.00, 'entregado',  'efectivo',      '2026-05-20 10:00:00'),
(2,  1, 2, 'Pantalón de vestir',   'Pantalón pinzado color beige',               NULL,                            '2026-06-15', 12000.00,  5000.00, 'entregado',  'transferencia', '2026-05-22 11:00:00'),
(3,  1, 3, 'Arreglo',              'Achique de campera de cuero en hombros',     'Cliente trae la campera',       '2026-06-18', 4000.00,   4000.00, 'entregado',  'efectivo',      '2026-05-25 09:00:00'),

-- EN PROCESO
(4,  1, 4, 'Vestido de novia',     'Vestido blanco marfil con cola corta',       'Urgente, boda el 20 de julio',  '2026-07-18', 85000.00, 30000.00, 'en_proceso', 'transferencia', '2026-06-01 10:00:00'),
(5,  1, 5, 'Falda midi',           'Falda plisada azul marino',                  'Largo hasta la rodilla',        '2026-07-10', 9000.00,   4000.00, 'en_proceso', 'efectivo',      '2026-06-03 11:00:00'),
(6,  1, 6, 'Conjunto de lino',     'Pantalón y camisa de lino blanco',           'Confirmar talle antes de cortar','2026-07-15', 22000.00,  8000.00, 'en_proceso', 'efectivo',      '2026-06-05 12:00:00'),

-- LISTOS PARA ENTREGAR
(7,  1, 7, 'Blusa de seda',        'Blusa crema con botones nacarados',          NULL,                            '2026-06-28', 15000.00,  7000.00, 'listo',      'transferencia', '2026-06-08 10:00:00'),
(8,  1, 8, 'Arreglo',              'Ruedo de vestido de graduación',             'Color bordo, tela delicada',    '2026-06-25', 3500.00,   3500.00, 'listo',      'efectivo',      '2026-06-10 09:00:00'),

-- PENDIENTES (algunos con saldo pendiente para alertas de morosos)
(9,  1, 9,  'Tapado de paño',      'Tapado largo color camel',                   'Forro interno a elección',      '2026-07-20', 45000.00, 15000.00, 'pendiente',  'efectivo',      '2026-06-12 10:00:00'),
(10, 1, 10, 'Vestido casual',      'Vestido estampado flores manga larga',       NULL,                            '2026-07-05', 11000.00,  4000.00, 'pendiente',  'transferencia', '2026-06-14 11:00:00'),
(11, 1, 11, 'Camisa de lino',      'Camisa celeste manga corta',                 NULL,                            '2026-07-08', 8500.00,   3000.00, 'pendiente',  'efectivo',      '2026-06-15 12:00:00'),
(12, 1, 12, 'Falda lápiz',         'Falda lápiz negra con tajo lateral',         'Largo hasta la rodilla',        '2026-07-12', 10000.00,  4000.00, 'pendiente',  'efectivo',      '2026-06-16 10:00:00'),
-- Encargo sin cliente registrado
(13, 1, NULL,'Arreglo',            'Ruedo de jeans sin cliente registrado',      NULL,                            '2026-07-01', 2500.00,   2500.00, 'pendiente',  'efectivo',      '2026-06-18 09:00:00');

-- ============================================================
-- OBSERVACIONES
-- ============================================================

INSERT INTO `observacion` (`encargo_id`, `detalle`, `fecha`) VALUES
(4,  'La novia quiere prueba de vestuario el 10 de julio',    '2026-06-01 10:30:00'),
(4,  'Agregar bordado en escote según muestra',               '2026-06-05 11:00:00'),
(6,  'Confirmar si el lino es lavado o crudo',                '2026-06-05 12:30:00'),
(9,  'El paño llega el 20 de junio, esperar stock',           '2026-06-12 10:30:00'),
(1,  'Entregado con bolso de tela incluido',                  '2026-06-10 15:00:00');

-- ============================================================
-- PAGOS
-- Lógica:
--   entregado → pagado completo
--   listo/en_proceso → seña pagada + algún pago parcial
--   pendiente → solo seña o con saldo abierto
-- ============================================================

INSERT INTO `pago` (`encargo_id`, `administrador_id`, `monto`, `metodo`, `nota`, `fecha`) VALUES
-- Encargo 1 (entregado, total 25000, seña 10000) → pagado completo
(1, 1, 10000.00, 'efectivo',      'Seña inicial',         '2026-05-20 10:30:00'),
(1, 1, 15000.00, 'efectivo',      'Pago final al retirar','2026-06-10 15:00:00'),

-- Encargo 2 (entregado, total 12000, seña 5000) → pagado completo
(2, 1,  5000.00, 'transferencia', 'Seña inicial',         '2026-05-22 11:30:00'),
(2, 1,  7000.00, 'transferencia', 'Pago final',           '2026-06-15 12:00:00'),

-- Encargo 3 (entregado, total 4000, seña 4000) → pagado completo
(3, 1,  4000.00, 'efectivo',      'Pago total adelantado','2026-05-25 09:30:00'),

-- Encargo 4 (en_proceso, total 85000, seña 30000) → solo seña + un pago parcial
(4, 1, 30000.00, 'transferencia', 'Seña inicial',         '2026-06-01 10:30:00'),
(4, 1, 10000.00, 'transferencia', 'Pago parcial',         '2026-06-10 11:00:00'),
-- saldo pendiente: 45000

-- Encargo 5 (en_proceso, total 9000, seña 4000) → solo seña
(5, 1,  4000.00, 'efectivo',      'Seña inicial',         '2026-06-03 11:30:00'),
-- saldo pendiente: 5000

-- Encargo 6 (en_proceso, total 22000, seña 8000) → seña + parcial
(6, 1,  8000.00, 'efectivo',      'Seña inicial',         '2026-06-05 12:30:00'),
(6, 1,  5000.00, 'efectivo',      'Pago parcial',         '2026-06-15 10:00:00'),
-- saldo pendiente: 9000

-- Encargo 7 (listo, total 15000, seña 7000) → seña + parcial, falta el resto
(7, 1,  7000.00, 'transferencia', 'Seña inicial',         '2026-06-08 10:30:00'),
(7, 1,  3000.00, 'transferencia', 'Pago parcial',         '2026-06-20 11:00:00'),
-- saldo pendiente: 5000

-- Encargo 8 (listo, total 3500, seña 3500) → pagado completo
(8, 1,  3500.00, 'efectivo',      'Pago total adelantado','2026-06-10 09:30:00'),

-- Encargo 9 (pendiente, total 45000, seña 15000) → solo seña
(9, 1, 15000.00, 'efectivo',      'Seña inicial',         '2026-06-12 10:30:00'),
-- saldo pendiente: 30000

-- Encargos 10, 11, 12 → sin ningún pago aún (máximo saldo abierto para alertas)
-- Encargo 13 (sin cliente) → pagado adelantado
(13, 1, 2500.00, 'efectivo',      'Pago total adelantado','2026-06-18 09:30:00');

COMMIT;