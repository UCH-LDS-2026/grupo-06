<?php
require_once BASE_PATH . '/config/database.php';

class Alerta {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Genera una nueva alerta para un encargo próximo a vencer
     */
    public function generarAlerta($administrador_id, $encargo_id, $mensaje, $tipo) {
        $sql = "INSERT INTO alerta 
                (administrador_id, encargo_id, mensaje, tipo) 
                VALUES (?, ?, ?, ?)";
        
        $this->db->query($sql, [
            $administrador_id,
            $encargo_id,
            $mensaje,
            $tipo
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Marca una alerta como leída
     */
    public function marcarLeida($id) {
        $sql = "UPDATE alerta SET leida = 1 WHERE id = ?";
        $this->db->query($sql, [$id]);
    }

    /**
     * Lista todas las alertas de un administrador
     * ordenadas por fecha descendente
     */
    public function listarPorAdmin($administrador_id) {
        $sql = "SELECT a.*, e.tipo as tipo_encargo, e.fecha_entrega,
                       c.nombre as nombre_cliente
                FROM alerta a
                LEFT JOIN encargo e ON a.encargo_id = e.id
                LEFT JOIN cliente c ON e.cliente_id = c.id
                WHERE a.administrador_id = ?
                ORDER BY a.fecha DESC";

        return $this->db->fetchAll($sql, [$administrador_id]);
    }

    /**
     * Cuenta las alertas no leídas de un administrador
     */
    public function contarNoLeidas($administrador_id) {
        $sql = "SELECT COUNT(*) as total 
                FROM alerta 
                WHERE administrador_id = ? AND leida = 0";

        $resultado = $this->db->fetch($sql, [$administrador_id]);
        return $resultado['total'];
    }
public function getClientasSinMedidas() {
    $sql = "SELECT c.nombre 
            FROM cliente c
            LEFT JOIN ficha_cliente f ON c.id = f.cliente_id
            WHERE f.id IS NULL";

    return $this->db->fetchAll($sql);
}
/**
 * Limpia alertas viejas: máximo 50 y no más de 30 días
 */
public function limpiarViejas($administrador_id) {
    // Borrar alertas de más de 30 días
    $sql = "DELETE FROM alerta 
            WHERE administrador_id = ? 
            AND fecha < DATE_SUB(NOW(), INTERVAL 30 DAY)";
    $this->db->query($sql, [$administrador_id]);

    // Si quedan más de 50, borrar las más viejas
    $sql = "DELETE FROM alerta 
            WHERE administrador_id = ? 
            AND id NOT IN (
                SELECT id FROM (
                    SELECT id FROM alerta 
                    WHERE administrador_id = ?
                    ORDER BY fecha DESC 
                    LIMIT 50
                ) tmp
            )";
    $this->db->query($sql, [$administrador_id, $administrador_id]);
}
}