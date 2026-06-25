<?php
require_once 'config/database.php';
require_once 'config/config.php';
require_once 'controllers/AuthController.php';
require_once 'models/Encargo.php';

if (session_status() === PHP_SESSION_NONE) session_start();

// Verificar vencimientos en cada carga
if (isset($_SESSION['admin_id'])) {
    require_once 'controllers/AlertaController.php';
    $alertaCtrl = new AlertaController();
    $alertaCtrl->verificarVencimientos($_SESSION['admin_id']);
    $alertaCtrl->verificarMorosos($_SESSION['admin_id']);
}

// Rutas AJAX de encargos (estado, pago-detalle, observaciones, editar) — salen antes de cualquier output
require_once 'controllers/ajax_encargos.php';

// Rutas AJAX de pagos (registrar pago desde panel, stats) — antes de cualquier output
require_once 'controllers/ajax_pagos.php';

// Manejar todos los POST antes de cualquier output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $accion = $_POST['accion'] ?? '';

    if ($accion === 'login' || $accion === 'logout' || $accion === 'cambiar_contrasena') {
        AuthController::dispatch();
        exit;
    }

    // POST de crear encargo
    if (isset($_GET['page']) && $_GET['page'] === 'crear') {
        $db = Database::getInstance()->getConnection();
        $encargo = new Encargo($db);
        $encargo->administrador_id      = 1;
        $encargo->cliente_id            = !empty($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : null;
        $encargo->tipo                  = trim($_POST['tipo'] ?? '');
        $encargo->descripcion           = trim($_POST['descripcion'] ?? '');
        $encargo->observaciones_encargo = trim($_POST['observaciones_encargo'] ?? '');
        $encargo->fecha_entrega         = $_POST['fecha_entrega'] ?? '';
        $encargo->monto_total           = !empty($_POST['monto_total']) ? (float)$_POST['monto_total'] : 0;
        $encargo->sena                  = !empty($_POST['sena']) ? (float)$_POST['sena'] : 0;
        $encargo->metodo_pago           = $_POST['metodo_pago'] ?? 'efectivo';

        if ($encargo->tipo !== '' && $encargo->fecha_entrega !== '' && $encargo->sena > 0 && $encargo->create()) {
            header('Location: ' . BASE_URL . '/index.php?nuevo=1');
        } else {
            header('Location: ' . BASE_URL . '/index.php?page=agenda&error=1');
        }
        exit;
    }

    // POST de clientes
    if (in_array($accion, ['registrar', 'editar', 'eliminar', 'guardar_ficha'])) {
        require_once 'controllers/ClienteController.php';
        ClienteController::dispatch();
        exit;
    }
}

$page = $_GET['page'] ?? 'agenda';

$vistas = [
    'agenda'          => 'views/encargos/index.php',
    'encargos'        => 'views/encargos/index.php',
    'crear'           => 'views/encargos/crear.php',
    'detalle-encargo' => 'views/encargos/detalle.php',
    'editar-encargo'  => 'views/encargos/editar.php',
    'clientes'        => 'views/clientes/index.php',
    'ficha-cliente'   => 'views/clientes/ficha.php',
    'pagos'           => 'views/pagos/index.php',
    'alertas'         => 'views/alertas/index.php',
];

$vista = $vistas[$page] ?? $vistas['agenda'];

if ($page === 'alertas' && isset($_GET['accion'])) {
    require_once 'controllers/AlertaController.php';
    $ctrl = new AlertaController();
    $ctrl->manejar();
    exit;
}

require_once 'views/layout/sidebar.php';

if ($page === 'pagos') {
    $db = Database::getInstance()->getConnection();
    require_once 'controllers/PagoController.php';
    $ctrl = new PagoController($db);
    $ctrl->cargarDatos();
}

require_once $vista;