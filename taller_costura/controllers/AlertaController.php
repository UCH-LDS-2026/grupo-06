<?php
require_once BASE_PATH . '/models/Alerta.php';
require_once BASE_PATH . '/models/Encargo.php';

class AlertaController {

    private $alertaModel;

    public function __construct() {
        $this->alertaModel = new Alerta();
    }

    /**
     * Verifica encargos próximos a vencer y genera alertas
     * Se llama al hacer login
     */
    public function verificarVencimientos($administrador_id) {
        $db = Database::getInstance()->getConnection();

        // Buscar encargos que vencen en los próximos 3 días
                $sql = "SELECT e.id, e.tipo, e.fecha_entrega, e.cliente_id, c.nombre as nombre_cliente
        FROM encargo e
        LEFT JOIN cliente c ON e.cliente_id = c.id
        WHERE e.administrador_id = ?
        AND e.estado NOT IN ('entregado')
        AND e.fecha_entrega <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)
        AND e.fecha_entrega >= CURDATE()
        AND e.id NOT IN (
            SELECT encargo_id FROM alerta 
            WHERE administrador_id = ? 
            AND tipo = 'vencimiento'
            AND DATE(fecha) = CURDATE()
        )";

        $stmt = $db->prepare($sql);
        $stmt->execute([$administrador_id, $administrador_id]);
        $encargos = $stmt->fetchAll();

        foreach ($encargos as $encargo) {
            $diasRestantes = (strtotime($encargo['fecha_entrega']) - strtotime('today')) / 86400;
            $cliente = $encargo['nombre_cliente'] ? " de {$encargo['nombre_cliente']}" : '';

            if ($diasRestantes == 0) {
                $mensaje = "{$encargo['tipo']}{$cliente} vence hoy.";
            } else {
                $mensaje = "{$encargo['tipo']}{$cliente} vence en {$diasRestantes} día/s.";
            }

            $this->alertaModel->generarAlerta(
                $administrador_id,
                $encargo['id'],
                $mensaje,
                'vencimiento'
            );
        }
    }

    /**
     * Marca una alerta como leída
     */
    public function marcarLeida($alerta_id) {
        $this->alertaModel->marcarLeida($alerta_id);
    }

    /**
     * Lista todas las alertas de un administrador
     */
    public function listar($administrador_id) {
    $this->alertaModel->limpiarViejas($administrador_id);
    return $this->alertaModel->listarPorAdmin($administrador_id);
    }

    /**
     * Cuenta alertas no leídas para la campana
     */
    public function contarNoLeidas($administrador_id) {
        return $this->alertaModel->contarNoLeidas($administrador_id);
    }

    public function marcarTodas($administrador_id) {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare("UPDATE alerta SET leida = 1 WHERE administrador_id = ?");
        $stmt->execute([$administrador_id]);
    }

    public function manejar(): void {
        $accion = $_GET['accion'] ?? '';
        $adminId = $_SESSION['admin_id'] ?? 1;

        if ($accion === 'marcar') {
            $id = (int)($_GET['id'] ?? 0);
            $this->marcarLeida($id);
            header('Content-Type: application/json');
            echo json_encode(['ok' => true]);
            exit;
        }

        if ($accion === 'marcar_todas') {
            $this->marcarTodas($adminId);
            header('Content-Type: application/json');
            echo json_encode(['ok' => true]);
            exit;
        }
    }
    public function verificarClientasSinFicha($administrador_id) {

    $clientas = $this->alertaModel->getClientasSinMedidas();

    foreach ($clientas as $clienta) {

        $mensaje = "La clienta {$clienta['nombre']} no tiene ficha de medidas.";

        $db = Database::getInstance();

        $existe = $db->fetch(
            "SELECT id
             FROM alerta
             WHERE administrador_id = ?
             AND mensaje = ?",
            [$administrador_id, $mensaje]
        );

        if (!$existe) {
            $this->alertaModel->generarAlerta(
                $administrador_id,
                null,
                $mensaje,
                'sin_ficha'
            );
        }
    }
}

public function obtenerClientasSinFicha() {
    return $this->alertaModel->getClientasSinMedidas();
}
}

