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
}
// POST pagos AJAX — PRIMERO antes de cualquier output
if (isset($_GET['page']) && $_GET['page'] === 'pagos' && isset($_GET['accion']) && $_GET['accion'] === 'registrar') {
    $db = Database::getInstance()->getConnection();
    require_once 'controllers/PagoController.php';
    $ctrl = new PagoController($db);
    $ctrl->manejar();
    exit;
}
 
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
            header('Location: ' . BASE_URL . '/index.php?nuevo=1');
        } else {
            header('Location: ' . BASE_URL . '/index.php?page=crear&error=1');
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

// AJAX: actualizar estado encargo
if (isset($_GET['page']) && $_GET['page'] === 'actualizar-estado-encargo' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $data   = json_decode(file_get_contents('php://input'), true);
    $id     = (int)($data['id'] ?? 0);
    $estado = $data['estado'] ?? '';
    $validos = ['pendiente','en_proceso','listo','entregado'];
    if ($id && in_array($estado, $validos)) {
        $db   = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE encargo SET estado = ? WHERE id = ?");
        echo json_encode(['ok' => $stmt->execute([$estado, $id])]);
    } else {
        echo json_encode(['ok' => false]);
    }
    exit;
}

// AJAX: registrar pago en detalle-encargo
if (isset($_GET['page']) && $_GET['page'] === 'registrar-pago-detalle' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $data      = json_decode(file_get_contents('php://input'), true);
    $encId     = (int)($data['encargo_id'] ?? 0);
    $monto     = (float)($data['monto'] ?? 0);
    $metodo    = $data['metodo'] ?? 'efectivo';
    $nota      = trim($data['nota'] ?? '');
    $adminId   = $_SESSION['admin_id'] ?? 1;
    $valMetodo = ['efectivo','transferencia','tarjeta','otro'];
    if ($encId && $monto > 0 && in_array($metodo, $valMetodo)) {
        $db   = Database::getInstance()->getConnection();
        // Verificar que no supere saldo
        $enc  = $db->prepare("SELECT monto_total, sena FROM encargo WHERE id = ?");
        $enc->execute([$encId]);
        $row  = $enc->fetch(PDO::FETCH_ASSOC);
        $saldo = (float)$row['monto_total'] - (float)$row['sena'];
        if ($monto > $saldo + 0.01) {
            echo json_encode(['ok' => false, 'mensaje' => 'El monto supera el saldo pendiente.']);
            exit;
        }
        $nuevaSena = (float)$row['sena'] + $monto;
        $db->prepare("UPDATE encargo SET sena = ? WHERE id = ?")->execute([$nuevaSena, $encId]);
        $db->prepare("INSERT INTO pago (encargo_id, administrador_id, monto, metodo, nota) VALUES (?,?,?,?,?)")
           ->execute([$encId, $adminId, $monto, $metodo, $nota ?: null]);
        echo json_encode(['ok' => true, 'nueva_sena' => $nuevaSena, 'monto_total' => $row['monto_total']]);
    } else {
        echo json_encode(['ok' => false, 'mensaje' => 'Datos inválidos.']);
    }
    exit;
}
 
$vistas = [
    'agenda'          => 'views/encargos/index.php',
    'crear'           => 'views/encargos/crear.php',
    'detalle-encargo' => 'views/encargos/detalle.php',
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