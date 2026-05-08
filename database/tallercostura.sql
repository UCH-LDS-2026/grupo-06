# Script SQL — Sistema de Gestión para Taller de Costura

```sql
-- =====================================
-- CREACIÓN DE BASE DE DATOS
-- =====================================

CREATE DATABASE IF NOT EXISTS taller_costura;
USE taller_costura;

-- =====================================
-- TABLA ADMINISTRADOR
-- =====================================

CREATE TABLE administrador (
    id_administrador INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    contrasena VARCHAR(255) NOT NULL
);

-- =====================================
-- TABLA CLIENTE
-- =====================================

CREATE TABLE cliente (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    telefono VARCHAR(20),
    email VARCHAR(100),
    contrasena VARCHAR(255)
);

-- =====================================
-- TABLA FICHA CLIENTE
-- =====================================

CREATE TABLE ficha_cliente (
    id_ficha INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT UNIQUE,
    talle VARCHAR(10),
    contorno_pecho DECIMAL(5,2),
    contorno_cintura DECIMAL(5,2),
    contorno_cadera DECIMAL(5,2),
    largo_manga DECIMAL(5,2),
    observaciones_cliente TEXT,

    CONSTRAINT fk_ficha_cliente
        FOREIGN KEY (id_cliente)
        REFERENCES cliente(id_cliente)
        ON DELETE CASCADE
);

-- =====================================
-- TABLA ENCARGO
-- =====================================

CREATE TABLE encargo (
    id_encargo INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_administrador INT NOT NULL,

    tipo VARCHAR(100) NOT NULL,
    descripcion TEXT,
    observaciones_encargo TEXT,

    fecha_entrega DATE NOT NULL,

    monto_total DECIMAL(10,2) NOT NULL,
    sena DECIMAL(10,2) DEFAULT 0,
    estado ENUM('Pendiente', 'En Proceso', 'Listo', 'Entregado') DEFAULT 'Pendiente',

    CONSTRAINT fk_encargo_cliente
        FOREIGN KEY (id_cliente)
        REFERENCES cliente(id_cliente)
        ON DELETE CASCADE,

    CONSTRAINT fk_encargo_admin
        FOREIGN KEY (id_administrador)
        REFERENCES administrador(id_administrador)
        ON DELETE CASCADE
);

-- =====================================
-- TABLA OBSERVACIONES
-- =====================================

CREATE TABLE observaciones (
    id_observacion INT AUTO_INCREMENT PRIMARY KEY,
    id_encargo INT NOT NULL,

    detalle TEXT NOT NULL,
    fecha DATE,

    CONSTRAINT fk_observacion_encargo
        FOREIGN KEY (id_encargo)
        REFERENCES encargo(id_encargo)
        ON DELETE CASCADE
);

-- =====================================
-- TABLA ALERTA
-- =====================================

CREATE TABLE alerta (
    id_alerta INT AUTO_INCREMENT PRIMARY KEY,
    id_encargo INT NOT NULL,

    mensaje TEXT NOT NULL,
    fecha DATE,
    tipo VARCHAR(50),

    CONSTRAINT fk_alerta_encargo
        FOREIGN KEY (id_encargo)
        REFERENCES encargo(id_encargo)
        ON DELETE CASCADE
);

-- =====================================
-- DATOS DE EJEMPLO
-- =====================================

INSERT INTO administrador (nombre, email, contrasena)
VALUES
('Administrador', 'admin@taller.com', '1234');

INSERT INTO cliente (nombre, telefono, email, contrasena)
VALUES
('Maria Lopez', '2991234567', 'maria@gmail.com', '1234'),
('Lucia Perez', '2997654321', 'lucia@gmail.com', '1234');

INSERT INTO ficha_cliente (
    id_cliente,
    talle,
    contorno_pecho,
    contorno_cintura,
    contorno_cadera,
    largo_manga,
    observaciones_cliente
)
VALUES
(1, 'M', 90, 70, 95, 58, 'Cliente frecuente'),
(2, 'S', 85, 65, 90, 55, 'Prefiere ropa holgada');

INSERT INTO encargo (
    id_cliente,
    id_administrador,
    tipo,
    descripcion,
    observaciones_encargo,
    fecha_entrega,
    monto_total,
    sena,
    estado
)
VALUES
(
    1,
    1,
    'Vestido',
    'Vestido de fiesta rojo',
    'Agregar encaje en mangas',
    '2026-06-15',
    50000,
    20000,
    'En Proceso'
),
(
    2,
    1,
    'Pantalón',
    'Ajuste de pantalón de jean',
    'Acortar botamangas',
    '2026-06-10',
    18000,
    5000,
    'Pendiente'
);

INSERT INTO observaciones (
    id_encargo,
    detalle,
    fecha
)
VALUES
(1, 'Cliente pidió prueba previa a la entrega', '2026-06-05'),
(2, 'Tela delicada', '2026-06-04');

INSERT INTO alerta (
    id_encargo,
    mensaje,
    fecha,
    tipo
)
VALUES
(1, 'Entrega próxima en 2 días', '2026-06-13', 'Vencimiento'),
(2, 'Encargo pendiente de iniciar', '2026-06-07', 'Pendiente');

-- =====================================
-- CONSULTAS ÚTILES
-- =====================================

-- Ver todos los encargos con nombre de cliente
SELECT 
    e.id_encargo,
    c.nombre AS cliente,
    e.tipo,
    e.fecha_entrega,
    e.estado,
    e.monto_total,
    e.sena,
    (e.monto_total - e.sena) AS saldo_pendiente
FROM encargo e
INNER JOIN cliente c
ON e.id_cliente = c.id_cliente;

-- Ver encargos pendientes
SELECT *
FROM encargo
WHERE estado = 'Pendiente';

-- Ver agenda ordenada por fecha
SELECT *
FROM encargo
ORDER BY fecha_entrega ASC;

-- Ver historial de observaciones
SELECT 
    o.id_observacion,
    e.tipo,
    o.detalle,
    o.fecha
FROM observaciones o
INNER JOIN encargo e
ON o.id_encargo = e.id_encargo;

```
