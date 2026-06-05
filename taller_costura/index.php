<?php
require_once 'config/database.php';
require_once 'config/config.php';
require_once 'controllers/AuthController.php';
require_once 'models/Encargo.php';

if (session_status() === PHP_SESSION_NONE) session_start();

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

        if ($encargo->tipo !== '' && $encargo->fecha_entrega !== '' && $encargo->create()) {
            header('Location: /grupo-06/taller_costura/index.php?nuevo=1');
        } else {
            header('Location: /grupo-06/taller_costura/index.php?page=crear&error=1');
        }
        exit;
    }
}

$page = $_GET['page'] ?? 'agenda';

$vistas = [
    'agenda'          => 'views/encargos/index.php',
    'crear'           => 'views/encargos/crear.php',
    'detalle-encargo' => 'views/encargos/detalle.php',
    'clientes'        => 'views/clientes/index.php',
    'pagos'           => 'views/pagos/index.php',
    'alertas'         => 'views/alertas/index.php',
];

$vista = $vistas[$page] ?? $vistas['agenda'];

require_once 'views/layout/sidebar.php';
require_once $vista;