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
        $sql = "SELECT id, tipo, fecha_entrega, cliente_id 
                FROM encargo 
                WHERE administrador_id = ?
                AND estado NOT IN ('entregado')
                AND fecha_entrega <= DATE_ADD(CURDATE(), INTERVAL 3 DAY)
                AND fecha_entrega >= CURDATE()
                AND id NOT IN (
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
            
            if ($diasRestantes == 0) {
                $mensaje = "El encargo #{$encargo['id']} vence hoy.";
            } else {
                $mensaje = "El encargo #{$encargo['id']} vence en {$diasRestantes} día/s.";
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
        return $this->alertaModel->listarPorAdmin($administrador_id);
    }

    /**
     * Cuenta alertas no leídas para la campana
     */
    public function contarNoLeidas($administrador_id) {
        return $this->alertaModel->contarNoLeidas($administrador_id);
    }
}
?>