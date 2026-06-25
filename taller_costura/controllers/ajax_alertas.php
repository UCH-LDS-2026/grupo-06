<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/Alerta.php';

header('Content-Type: application/json');

$adminId = $_SESSION['admin_id'] ?? null;
if (!$adminId) {
    echo json_encode(['ok' => false, 'total' => 0]);
    exit;
}

$accion = $_GET['accion'] ?? '';

if ($accion === 'contar') {
    $alertaModel = new Alerta();
    $total = $alertaModel->contarNoLeidas($adminId);
    echo json_encode(['ok' => true, 'total' => (int)$total]);
    exit;
}

echo json_encode(['ok' => false]);