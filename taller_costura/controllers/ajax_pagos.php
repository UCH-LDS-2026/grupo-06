<?php
// controllers/ajax_pagos.php
// Endpoints AJAX exclusivos del módulo pagos — incluido desde index.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once BASE_PATH . '/models/Alerta.php';

$ajaxPage = $_GET['page'] ?? '';

// ── AJAX: registrar pago desde panel pagos ────────────────────────────────────
if ($ajaxPage === 'pagos' && isset($_GET['accion']) && $_GET['accion'] === 'registrar'
    && $_SERVER['REQUEST_METHOD'] === 'POST'
    && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {

    header('Content-Type: application/json');

    $encargoId  = (int)($_POST['encargo_id'] ?? 0);
    $monto      = (float)str_replace(',', '.', $_POST['monto'] ?? 0);
    $metodoPago = $_POST['metodo_pago'] ?? 'efectivo';
    $valMetodo  = ['efectivo', 'transferencia', 'tarjeta'];

    if (!$encargoId || $monto <= 0 || !in_array($metodoPago, $valMetodo)) {
        echo json_encode(['ok' => false, 'mensaje' => 'Datos inválidos.']);
        exit;
    }

    $db  = Database::getInstance()->getConnection();
    $enc = $db->prepare("SELECT monto_total, sena FROM encargo WHERE id = ?");
    $enc->execute([$encargoId]);
    $row = $enc->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
        echo json_encode(['ok' => false, 'mensaje' => 'Encargo no encontrado.']);
        exit;
    }

    $saldo = (float)$row['monto_total'] - (float)$row['sena'];

    if ($monto > $saldo + 0.01) {
        echo json_encode(['ok' => false, 'mensaje' => 'El monto supera el saldo pendiente.']);
        exit;
    }

    $nuevaSena     = (float)$row['sena'] + $monto;
    $saldoRestante = (float)$row['monto_total'] - $nuevaSena;

    // Actualizar seña en encargo
    $db->prepare("UPDATE encargo SET sena = ? WHERE id = ?")->execute([$nuevaSena, $encargoId]);

    // Insertar registro en tabla pago
    $adminId = $_SESSION['admin_id'] ?? 1;
    $db->prepare(
        "INSERT INTO pago (encargo_id, administrador_id, monto, metodo) VALUES (?, ?, ?, ?)"
    )->execute([$encargoId, $adminId, $monto, $metodoPago]);

    // Generar alerta si queda saldo
    if ($saldoRestante > 0.01) {
        $info = $db->prepare(
            "SELECT e.tipo, c.nombre AS cliente_nombre
             FROM encargo e LEFT JOIN cliente c ON e.cliente_id = c.id
             WHERE e.id = ?"
        );
        $info->execute([$encargoId]);
        $r = $info->fetch(PDO::FETCH_ASSOC);
        if ($r) {
            $cliente = $r['cliente_nombre'] ? " de {$r['cliente_nombre']}" : '';
            $stmt = $db->prepare("SELECT id FROM alerta WHERE encargo_id = ? AND tipo = 'pago' AND leida = 0");
            $stmt->execute([$encargoId]);
            if (!$stmt->fetch()) {
                (new Alerta())->generarAlerta(
                    $adminId, $encargoId,
                    "{$r['tipo']}{$cliente} tiene saldo pendiente.",
                    'pago'
                );
            }
        }
    }

    // ── Devolver datos actualizados de la card ────────────────────────────────
    $encActualizado = $db->prepare(
        "SELECT e.id, e.tipo, e.estado, e.monto_total, e.sena, e.fecha_entrega,
                c.nombre AS cliente_nombre
         FROM encargo e
         LEFT JOIN cliente c ON e.cliente_id = c.id
         WHERE e.id = ?"
    );
    $encActualizado->execute([$encargoId]);
    $cardData = $encActualizado->fetch(PDO::FETCH_ASSOC);

    $nuevoSaldo    = (float)$cardData['monto_total'] - (float)$cardData['sena'];
    $porcentaje    = $cardData['monto_total'] > 0
        ? round(((float)$cardData['sena'] / (float)$cardData['monto_total']) * 100)
        : 0;
    $pagado        = $nuevoSaldo <= 0.01;

    echo json_encode([
        'ok'                => true,
        'mensaje'           => $pagado
            ? '¡Pago completo registrado!'
            : 'Pago registrado correctamente.',
        'encargo_id'        => $encargoId,
        'nueva_sena'        => $cardData['sena'],
        'saldo_pendiente'   => $nuevoSaldo,
        'monto_total'       => $cardData['monto_total'],
        'porcentaje_pagado' => $porcentaje,
        'pagado_completo'   => $pagado,
    ]);
    exit;
}

// ── AJAX: obtener stats actualizadas del panel ────────────────────────────────
if ($ajaxPage === 'pagos' && isset($_GET['accion']) && $_GET['accion'] === 'stats'
    && $_SERVER['REQUEST_METHOD'] === 'GET'
    && !empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {

    header('Content-Type: application/json');

    require_once BASE_PATH . '/models/Pagos.php';
    $db        = Database::getInstance()->getConnection();
    $adminId   = $_SESSION['admin_id'] ?? 1;
    $pagoModel = new Pago($db);

    echo json_encode([
        'ok'                  => true,
        'saldo_pendiente_total' => $pagoModel->getSaldoPendienteTotal($adminId),
        'cuentas_count'       => $pagoModel->getCuentasPorCobrarCount($adminId),
        'resumen_mensual'     => $pagoModel->getResumenMensual($adminId),
    ]);
    exit;
}