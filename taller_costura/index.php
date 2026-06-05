<?php
require_once 'config/database.php';
require_once 'config/config.php';
require_once 'controllers/AuthController.php';

// Verificar que esté logueado
// AuthController::requiereLogin();

// Leer qué página se pidió
$page = $_GET['page'] ?? 'agenda';

// Mapa de páginas permitidas (seguridad: nunca usar el GET directo en require)
$vistas = [
    'agenda'   => 'views/encargos/index.php',
    'clientes' => 'views/clientes/index.php',
    'pagos'    => 'views/pagos/index.php',
    'alertas'  => 'views/alertas/index.php',
];

$vista = $vistas[$page] ?? $vistas['agenda']; // si la página no existe, vuelve al inicio

// Mostrar layout
require_once 'views/layout/sidebar.php';

// Mostrar contenido dinámico
require_once $vista;
?>