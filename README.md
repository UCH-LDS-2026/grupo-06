# Sistema de Gestión para Taller de Costura

## Integrantes

- Carolina Fetta
- Delfina Ibañez
- Candela Aguilar

---

# Descripción del proyecto

Sistema web destinado a la gestión de encargos de un taller de costura.  
Permite registrar clientes, almacenar medidas, gestionar encargos, registrar señas y visualizar entregas próximas mediante una agenda organizada.

---

# Tecnologías utilizadas

## Frontend
- HTML5
- CSS3
- JavaScript

## Backend
- PHP 8

## Base de datos
- MySQL

---

# Justificación del Stack

Se eligió JavaScript para el frontend debido a su facilidad para generar interfaces dinámicas e interactivas, especialmente útil para la agenda visual y la actualización de estados de encargos en tiempo real.

PHP fue seleccionado para el backend por su integración sencilla con aplicaciones web y bases de datos relacionales, además de ser una tecnología ampliamente utilizada y adecuada para proyectos CRUD como este sistema.

MySQL se eligió como sistema gestor de base de datos por su estabilidad, facilidad de uso y compatibilidad con PHP, permitiendo almacenar clientes, encargos, observaciones y pagos de forma estructurada y segura.

Además, el sistema se plantea como una aplicación web porque permite acceder desde distintos dispositivos sin necesidad de instalación local. La arquitectura cliente-servidor facilita la separación entre interfaz, lógica y datos, permitiendo escalabilidad y mantenimiento futuro.

---

# Requisitos

- PHP 8 o superior
- MySQL 8
- Node.js 18+
- XAMPP
- Navegador web moderno

---

# Instalación

## 1. Clonar repositorio

```bash
git clone https://github.com/UCH-LDS-2026/grupo-06

## 2. Instalar dependencias

```bash
npm install
```

## 3. Configurar base de datos

- Crear base de datos MySQL
- Importar archivo:

```bash
database/script.sql
```

## 4. Iniciar servidor

Con XAMPP o Laragon ejecutar Apache y MySQL.

---

# Estrategia de ramas

- `main` → versión estable
- `develop` → integración
- `feature/nombre-feature` → nuevas funcionalidades
- `fix/nombre-fix` → corrección de errores

La rama `main` se encuentra protegida para evitar cambios directos.

---

# Funcionalidades MVP

- Registro de clientes
- Gestión de encargos
- Agenda visual
- Registro de señas
- Cálculo de saldo pendiente
- Gestión de observaciones
- Historial de encargos

---

# Entorno configurado

| PHP → 8.2 |
| MySQL → 8 |
| Node.js → 18 |
| VS Code → Última |
| XAMPP → Última |

---

# Diagramas UML

## Casos de uso
- Gestión de clientes
- Gestión de encargos
- Gestión de pagos
- Consulta de historial
- Agenda visual

## Clases principales
- Cliente
- FichaCliente
- Encargo
- Observaciones
- Administrador
- Alerta

---

# Documentación

La carpeta `/docs` contiene:

- Diagrama de casos de uso
- Diagrama de clases
- Informe PDF

---

# Objetivo del proyecto

Digitalizar la organización de un taller de costura para evitar pérdidas de información, mejorar el control de entregas y facilitar la gestión de clientes y pagos.
Holis hice un cambio 

