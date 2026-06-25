# Sistema de Gestión para Taller de Costura

## Integrantes

- Carolina Fetta
- Delfina Ibañez
- Candela Aguilar

---

## Descripción del proyecto

Sistema web destinado a la gestión de encargos de un taller de costura.  
Permite registrar clientes, almacenar medidas, gestionar encargos, registrar señas y visualizar entregas próximas mediante una agenda organizada.

---

## Tecnologías utilizadas

| Capa | Tecnología |
|---|---|
| Frontend | HTML5, CSS3, JavaScript |
| Backend | PHP 8 |
| Base de datos | MySQL 8 |
| Servidor local | WAMP / XAMPP |

---

## Justificación del Stack

**JavaScript** se eligió para el frontend por su facilidad para generar interfaces dinámicas, especialmente útil para la agenda visual y la actualización de estados de encargos.

**PHP** fue seleccionado para el backend por su integración sencilla con bases de datos relacionales y su adecuación para proyectos CRUD como este sistema.

**MySQL** se eligió como gestor de base de datos por su estabilidad, facilidad de uso y compatibilidad con PHP, permitiendo almacenar clientes, encargos, observaciones y pagos de forma estructurada.

El sistema se plantea como aplicación web para permitir el acceso desde distintos dispositivos sin instalación local, con arquitectura MVC que facilita la separación entre interfaz, lógica y datos.

---

## Requisitos

- PHP 8 o superior
- MySQL 8
- WAMP o XAMPP
- Navegador web moderno

---

## Instalación

### 1. Clonar repositorio

```bash
git clone https://github.com/UCH-LDS-2026/grupo-06
```

### 2. Configurar base de datos

- Crear la base de datos `sistema_costura` en MySQL
- Importar el archivo:

```bash
taller_costura/database/sistema_costura.sql
```

### 3. Configurar conexión

Cada integrante debe crear su propio `config/database.php` con sus credenciales locales (este archivo está excluido del repositorio por `.gitignore`).

```php
<?php
define('DB_HOST', 'localhost');
define('DB_PORT', '3306');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sistema_costura');
```

### 4. Iniciar servidor

Con WAMP o XAMPP iniciá Apache y MySQL.

### 5. Acceder al sistema

```
http://localhost/sistema_costura/grupo-06/taller_costura/views/auth/login.php
```

---

## Credenciales de prueba

| Campo | Valor |
|---|---|
| Email | admin@taller.com |
| Contraseña | Admin1234 |

> ⚠️ Recordá correr el script `actualizar_pass.php` una sola vez para generar el hash correcto, luego eliminarlo.

---

## Estrategia de ramas

| Rama | Uso |
|---|---|
| `main` | Versión estable — protegida, solo merge por PR |
| `development` | Integración de funcionalidades |
| `feature/nombre-feature` | Nuevas funcionalidades |
| `fix/nombre-fix` | Corrección de errores |

---

## Funcionalidades MVP

- Registro de clientes
- Gestión de encargos
- Agenda visual con colores según proximidad de entrega
- Registro de señas
- Cálculo de saldo pendiente
- Gestión de observaciones por encargo
- Historial de encargos entregados

---

## Arquitectura — MVC

```
taller_costura/
├── config/
│   ├── config.php
│   └── database.php          # ⚠️ No se sube al repo (credenciales locales)
├── controllers/
│   ├── AuthController.php
│   ├── ClienteController.php
│   ├── EncargoController.php
│   ├── AgendaController.php
│   └── PagoController.php
├── models/
│   ├── Administrador.php
│   ├── Cliente.php
│   ├── FichaCliente.php
│   ├── Encargo.php
│   ├── Observacion.php
│   └── Alerta.php
├── views/
│   ├── auth/
│   ├── clientes/
│   ├── encargos/
│   ├── agenda/
│   ├── pagos/
│   └── layout/
├── database/
│   └── sistema_costura.sql
├── public/
│   ├── css/
│   └── js/
└── index.php
```

---

## División de tareas

| Integrante | Módulo | Archivos |
|---|---|---|
| Delfina Ibañez | Autenticación & Clientes | `Administrador.php`, `Cliente.php`, `FichaCliente.php`, `AuthController.php`, `ClienteController.php`, `views/auth/`, `views/clientes/` |
| Carolina Fetta | Encargos & Agenda | `Encargo.php`, `Observacion.php`, `EncargoController.php`, `AgendaController.php`, `views/encargos/`, `views/agenda/` |
| Candela Aguilar | Pagos, Alertas & Infraestructura | `Alerta.php`, `PagoController.php`, `config/database.php`, `index.php`, `.htaccess`, `views/layout/`, `views/pagos/` |

---

## Diagrama ER — Tablas principales

| Tabla | Descripción |
|---|---|
| `administrador` | Usuario único del sistema |
| `cliente` | Clientes del taller |
| `ficha_cliente` | Medidas de cada cliente (1:1 con cliente) |
| `encargo` | Encargos de costura |
| `observacion` | Notas por encargo |
| `alerta` | Alertas de vencimiento y estado |

---

## Estados de un encargo

```
pendiente → en_proceso → listo → entregado
```

---

## Diagramas UML

> 📁 En construcción — se agregarán en la carpeta `/docs`

- Diagrama de casos de uso
- Diagrama de clases
- Informe final

---

## Objetivo del proyecto

Digitalizar la organización de un taller de costura para evitar pérdidas de información, mejorar el control de entregas y facilitar la gestión de clientes y pagos.

# 📸 Capturas del Sistema

## 🖥️ Versión Escritorio

### Login

<img width="1357" height="615" alt="Captura de pantalla 2026-06-25 111009" src="https://github.com/user-attachments/assets/c7cfc742-8bd2-4ba8-b7ca-24b27cee5d3a" />

### Gestión de Clientes
<img width="1334" height="615" alt="Captura de pantalla 2026-06-25 111411" src="https://github.com/user-attachments/assets/ce8386b3-ad11-452c-a532-f1261593efb2" />


### Gestión de Encargos

### Gestión de Pagos

<img width="1351" height="616" alt="Captura de pantalla 2026-06-25 111448" src="https://github.com/user-attachments/assets/fb06580f-9903-4ba2-8910-f756f23bf467" />

### Centro de Alertas

<img width="1350" height="614" alt="Captura de pantalla 2026-06-25 111511" src="https://github.com/user-attachments/assets/8219cedc-d4b4-46d2-bb22-229cef6b2788" />


---

## 📱 Versión Responsive

### Login

<img width="433" height="674" alt="Captura de pantalla 2026-06-25 111649" src="https://github.com/user-attachments/assets/f12699c2-b617-46d4-8ce9-266bdea0691a" />

### Gestión de Clientes
<img width="227" height="491" alt="Captura de pantalla 2026-06-25 111922" src="https://github.com/user-attachments/assets/ea3d0f01-9a88-46fb-a3dc-4d93f3ab99bf" />

### Gestión de Pagos

<img width="225" height="488" alt="Captura de pantalla 2026-06-25 111938" src="https://github.com/user-attachments/assets/4a42e062-033f-43ee-a226-f4adbc42d80d" />


### Gestión de Encargos


### Centro de Alertas



