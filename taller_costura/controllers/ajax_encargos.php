<?php
// controllers/ajax_encargos.php
// Todas las rutas AJAX del módulo encargos — incluido desde index.php

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/config.php';
require_once BASE_PATH . '/models/Alerta.php';

function alertaEncargoSiNoExiste(PDO $db, int $encargoId, int $adminId, string $tipo, string $mensaje): void {
    $stmt = $db->prepare("SELECT id FROM alerta WHERE encargo_id = ? AND tipo = ? AND leida = 0");
    $stmt->execute([$encargoId, $tipo]);
    if (!$stmt->fetch()) {
        (new Alerta())->generarAlerta($adminId, $encargoId, $mensaje, $tipo);
    }
}

$ajaxPage = $_GET['page'] ?? '';

// ── AJAX: actualizar estado encargo ──────────────────────────────────────────
if ($ajaxPage === 'actualizar-estado-encargo' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $data   = json_decode(file_get_contents('php://input'), true);
    $id     = (int)($data['id'] ?? 0);
    $estado = $data['estado'] ?? '';
    $validos = ['pendiente','en_proceso','listo','entregado'];
    if ($id && in_array($estado, $validos)) {
        $db   = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE encargo SET estado = ? WHERE id = ?");
        $ok   = $stmt->execute([$estado, $id]);
        echo json_encode(['ok' => $ok]);

        if ($ok && $estado === 'listo') {
            $info = $db->prepare("SELECT e.tipo, c.nombre AS cliente_nombre
                                   FROM encargo e LEFT JOIN cliente c ON e.cliente_id = c.id
                                   WHERE e.id = ?");
            $info->execute([$id]);
            $row = $info->fetch(PDO::FETCH_ASSOC);
            if ($row) {
                $cliente = $row['cliente_nombre'] ? " de {$row['cliente_nombre']}" : '';
                alertaEncargoSiNoExiste($db, $id, $_SESSION['admin_id'] ?? 1, 'estado', "{$row['tipo']}{$cliente} está listo para entregar.");
            }
        }
    } else {
        echo json_encode(['ok' => false]);
    }
    exit;
}

// ── AJAX: registrar pago en detalle-encargo ───────────────────────────────────
if ($ajaxPage === 'registrar-pago-detalle' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $data      = json_decode(file_get_contents('php://input'), true);
    $encId     = (int)($data['encargo_id'] ?? 0);
    $monto     = (float)($data['monto'] ?? 0);
    $metodo    = $data['metodo'] ?? 'efectivo';
    $valMetodo = ['efectivo','transferencia','tarjeta','otro'];

    if ($encId && $monto > 0 && in_array($metodo, $valMetodo)) {
        $db  = Database::getInstance()->getConnection();
        $enc = $db->prepare("SELECT monto_total, sena FROM encargo WHERE id = ?");
        $enc->execute([$encId]);
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

        $nuevaSena = (float)$row['sena'] + $monto;
        $saldoRestante = (float)$row['monto_total'] - $nuevaSena;
        if ($saldoRestante > 0) {
            $info = $db->prepare("SELECT e.tipo, c.nombre AS cliente_nombre
                                FROM encargo e LEFT JOIN cliente c ON e.cliente_id = c.id
                                WHERE e.id = ?");
            $info->execute([$encId]);
            $r = $info->fetch(PDO::FETCH_ASSOC);
            if ($r) {
                $cliente = $r['cliente_nombre'] ? " de {$r['cliente_nombre']}" : '';
                alertaEncargoSiNoExiste($db, $encId, $_SESSION['admin_id'] ?? 1, 'pago', "{$r['tipo']}{$cliente} tiene saldo pendiente.");
            }
        }
        $db->prepare("UPDATE encargo SET sena = ? WHERE id = ?")->execute([$nuevaSena, $encId]);
        echo json_encode(['ok' => true, 'nueva_sena' => $nuevaSena, 'monto_total' => $row['monto_total']]);
    } else {
        echo json_encode(['ok' => false, 'mensaje' => 'Datos inválidos.']);
    }
    exit;
}

// ── AJAX: eliminar observación especial del encargo ───────────────────────────
if ($ajaxPage === 'eliminar-observacion-especial' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents('php://input'), true);
    $id   = (int)($data['id'] ?? 0);
    if ($id) {
        $db   = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE encargo SET observaciones_encargo = NULL WHERE id = ?");
        echo json_encode(['ok' => $stmt->execute([$id])]);
    } else {
        echo json_encode(['ok' => false]);
    }
    exit;
}

// ── AJAX: agregar observación al historial ────────────────────────────────────
if ($ajaxPage === 'agregar-observacion' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $data      = json_decode(file_get_contents('php://input'), true);
    $encargoId = (int)($data['encargo_id'] ?? 0);
    $detalle   = trim($data['detalle'] ?? '');
    if ($encargoId && $detalle !== '') {
        $db   = Database::getInstance()->getConnection();
        $stmt = $db->prepare("INSERT INTO observacion (encargo_id, detalle) VALUES (?, ?)");
        if ($stmt->execute([$encargoId, $detalle])) {
            $newId = $db->lastInsertId();
            $row = $db->prepare("SELECT * FROM observacion WHERE id = ?");
            $row->execute([$newId]);
            $obs = $row->fetch(PDO::FETCH_ASSOC);
            echo json_encode(['ok' => true, 'observacion' => $obs]);
        } else {
            echo json_encode(['ok' => false]);
        }
    } else {
        echo json_encode(['ok' => false, 'mensaje' => 'Datos inválidos.']);
    }
    exit;
}

// ── AJAX: eliminar observación del historial ──────────────────────────────────
if ($ajaxPage === 'eliminar-observacion' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents('php://input'), true);
    $id   = (int)($data['id'] ?? 0);
    if ($id) {
        $db   = Database::getInstance()->getConnection();
        $stmt = $db->prepare("DELETE FROM observacion WHERE id = ?");
        echo json_encode(['ok' => $stmt->execute([$id])]);
    } else {
        echo json_encode(['ok' => false]);
    }
    exit;
}

// ── AJAX: eliminar encargo (baja física) ───────────────────────────────────────
if ($ajaxPage === 'eliminar-encargo' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../models/Encargo.php';
    header('Content-Type: application/json');
    $data = json_decode(file_get_contents('php://input'), true);
    $id   = (int)($data['id'] ?? 0);
    if ($id) {
        $db  = Database::getInstance()->getConnection();
        $enc = new Encargo($db);
        $enc->id = $id;
        echo json_encode(['ok' => $enc->delete()]);
    } else {
        echo json_encode(['ok' => false]);
    }
    exit;
}

// ── POST: guardar edición de encargo (form HTML) ──────────────────────────────
if ($ajaxPage === 'editar-encargo' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    require_once __DIR__ . '/../models/Encargo.php';
    $id = (int)($_POST['id'] ?? 0);
    if ($id) {
        $db  = Database::getInstance()->getConnection();
        $enc = new Encargo($db);
        $enc->id                    = $id;
        $enc->cliente_id            = !empty($_POST['cliente_id']) ? (int)$_POST['cliente_id'] : null;
        $enc->tipo                  = trim($_POST['tipo'] ?? '');
        $enc->descripcion           = trim($_POST['descripcion'] ?? '');
        $enc->observaciones_encargo = trim($_POST['observaciones_encargo'] ?? '') ?: null;
        $enc->fecha_entrega         = $_POST['fecha_entrega'] ?? '';
        $enc->monto_total           = !empty($_POST['monto_total']) ? (float)$_POST['monto_total'] : 0;
        $enc->sena                  = !empty($_POST['sena']) ? (float)$_POST['sena'] : 0;
        $enc->estado                = $_POST['estado'] ?? 'pendiente';

        if ($enc->tipo !== '' && $enc->fecha_entrega !== '' && $enc->update()) {
            if ($enc->estado === 'listo') {
                $cliente = '';
                if ($enc->cliente_id) {
                    $c = $db->prepare("SELECT nombre FROM cliente WHERE id = ?");
                    $c->execute([$enc->cliente_id]);
                    $cliente = ' de ' . ($c->fetchColumn() ?: '');
                }
                alertaEncargoSiNoExiste($db, $id, $_SESSION['admin_id'] ?? 1, 'estado', "{$enc->tipo}{$cliente} está listo para entregar.");
            }
            header('Location: ' . BASE_URL . '/index.php?page=detalle-encargo&id=' . $id . '&editado=1');
        } else {
            header('Location: ' . BASE_URL . '/index.php?page=editar-encargo&id=' . $id . '&error=1');
        }
    } else {
        header('Location: ' . BASE_URL . '/index.php');
    }
    exit;
}