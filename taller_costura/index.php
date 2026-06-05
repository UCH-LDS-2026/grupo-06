<?php
require_once 'config/database.php';
require_once 'config/config.php';
require_once 'controllers/AuthController.php';
require_once 'models/Encargo.php';

// AuthController::requiereLogin();

$page = $_GET['page'] ?? 'agenda';

$vistas = [
    'agenda'           => 'views/encargos/index.php',
    'crear'            => 'views/encargos/crear.php',
    'detalle-encargo' => 'views/encargos/detalle.php',
    'clientes'         => 'views/clientes/index.php',
    'pagos'            => 'views/pagos/index.php',
    'alertas'          => 'views/alertas/index.php',
];

$vista = $vistas[$page] ?? $vistas['agenda'];

require_once 'views/layout/sidebar.php';
require_once $vista;
?>