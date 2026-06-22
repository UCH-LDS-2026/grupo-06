<?php
// models/Pago.php
 
class Pago {
    private $pdo;
 
    public function __construct($pdo) {
        $this->pdo = $pdo;
    }
 
    // -------------------------------------------------------
    // Resumen general para las tarjetas del header
    // -------------------------------------------------------
 
    /**
     * Suma de todas las señas registradas (monto ya cobrado)
     */
    public function getTotalCobrado(int $adminId): float {
        $stmt = $this->pdo->prepare(
            "SELECT COALESCE(SUM(sena), 0) AS total
             FROM encargo
             WHERE administrador_id = :admin_id
               AND estado != 'entregado'"
        );
        $stmt->execute([':admin_id' => $adminId]);
        return (float) $stmt->fetchColumn();
    }
 
    /**
     * Suma de todos los saldos pendientes (monto_total - sena)
     */
    public function getSaldoPendienteTotal(int $adminId): float {
        $stmt = $this->pdo->prepare(
            "SELECT COALESCE(SUM(monto_total - sena), 0) AS total
             FROM encargo
             WHERE administrador_id = :admin_id
               AND estado != 'entregado'"
        );
        $stmt->execute([':admin_id' => $adminId]);
        return (float) $stmt->fetchColumn();
    }
 
    /**
     * Total de señas (igual a getTotalCobrado — alias semántico para la UI)
     */
    public function getTotalSenas(int $adminId): float {
        return $this->getTotalCobrado($adminId);
    }
 
    /**
     * Cantidad de encargos con saldo pendiente > 0
     */
    public function getCuentasPorCobrarCount(int $adminId): int {
        $stmt = $this->pdo->prepare(
            "SELECT COUNT(*) 
             FROM encargo
             WHERE administrador_id = :admin_id
               AND estado != 'entregado'
               AND (monto_total - sena) > 0"
        );
        $stmt->execute([':admin_id' => $adminId]);
        return (int) $stmt->fetchColumn();
    }
 
    // -------------------------------------------------------
    // Listados
    // -------------------------------------------------------
 
    /**
     * Encargos con saldo pendiente (tab "Cuentas por Cobrar")
     */
    public function getCuentasPorCobrar(int $adminId): array {
        $stmt = $this->pdo->prepare(
            "SELECT 
                e.id,
                e.tipo,
                e.descripcion,
                e.fecha_entrega,
                e.monto_total,
                e.sena,
                (e.monto_total - e.sena) AS saldo_pendiente,
                CASE WHEN e.monto_total > 0 
                     THEN ROUND((e.sena / e.monto_total) * 100) 
                     ELSE 0 END AS porcentaje_pagado,
                e.estado,
                c.nombre AS cliente_nombre,
                -- Conteo de pagos: si hay seña cuenta como 1 pago
                CASE WHEN e.sena > 0 THEN 1 ELSE 0 END AS pagos_count
             FROM encargo e
             LEFT JOIN cliente c ON e.cliente_id = c.id
             WHERE e.administrador_id = :admin_id
               AND (e.monto_total - e.sena) > 0
             ORDER BY e.fecha_entrega ASC"
        );
        $stmt->execute([':admin_id' => $adminId]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
 
    /**
     * Historial: encargos entregados o con saldo saldado
     */
    public function getHistorialPagos(int $adminId): array {
    $stmt = $this->pdo->prepare(
        "SELECT 
            e.id,
            e.tipo,
            e.descripcion,
            e.fecha_entrega,
            e.monto_total,
            e.sena,
            e.metodo_pago,
            (e.monto_total - e.sena) AS saldo_pendiente,
            e.estado,
            c.nombre AS cliente_nombre,
            (SELECT COUNT(*) FROM pago p WHERE p.encargo_id = e.id) AS cantidad_pagos
        FROM encargo e
        LEFT JOIN cliente c ON e.cliente_id = c.id
        WHERE e.administrador_id = :admin_id
        AND e.sena > 0
        ORDER BY (SELECT MAX(p2.fecha) FROM pago p2 WHERE p2.encargo_id = e.id) DESC"
    );
    $stmt->execute([':admin_id' => $adminId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
    
    /**
     * Obtener un encargo por ID (para validar antes de registrar pago)
     */
    public function getEncargoPorId(int $encargoId, int $adminId): array|false {
        $stmt = $this->pdo->prepare(
            "SELECT e.*, c.nombre AS cliente_nombre
             FROM encargo e
             LEFT JOIN cliente c ON e.cliente_id = c.id
             WHERE e.id = :id AND e.administrador_id = :admin_id"
        );
        $stmt->execute([':id' => $encargoId, ':admin_id' => $adminId]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
 
    // -------------------------------------------------------
    // Operaciones de escritura
    // -------------------------------------------------------
 
    /**
     * Registra un pago parcial o total sobre el saldo pendiente.
     * Actualiza la columna `sena` sumando el monto recibido.
     * Si el saldo queda en 0 y el estado es 'listo', lo pasa a 'entregado'.
     *
     * @return array ['ok' => bool, 'mensaje' => string]
     */
    public function registrarPago(int $encargoId, int $adminId, float $monto, string $metodoPago = 'efectivo', string $nota = ''): array {
    $encargo = $this->getEncargoPorId($encargoId, $adminId);
    if (!$encargo) {
        return ['ok' => false, 'mensaje' => 'Encargo no encontrado.'];
    }

    $saldoPendiente = (float)$encargo['monto_total'] - (float)$encargo['sena'];

    if ($monto <= 0) {
        return ['ok' => false, 'mensaje' => 'El monto debe ser mayor a cero.'];
    }
    if ($monto > $saldoPendiente) {
        return ['ok' => false, 'mensaje' => "El monto ($monto) supera el saldo pendiente ($saldoPendiente)."];
    }

    $nuevaSena = (float)$encargo['sena'] + $monto;
    $nuevoEstado = $encargo['estado'];

    if ($nuevaSena >= (float)$encargo['monto_total'] && $encargo['estado'] === 'listo') {
        $nuevoEstado = 'entregado';
    }

    // Actualizar seña en encargo
    $stmt = $this->pdo->prepare(
        "UPDATE encargo 
        SET sena = :sena, estado = :estado, metodo_pago = :metodo_pago
        WHERE id = :id AND administrador_id = :admin_id"
    );
    $stmt->execute([
        ':sena'        => $nuevaSena,
        ':estado'      => $nuevoEstado,
        ':metodo_pago' => $metodoPago,
        ':id'          => $encargoId,
        ':admin_id'    => $adminId,
    ]);

    // Guardar pago individual en tabla pago
    $stmt2 = $this->pdo->prepare(
        "INSERT INTO pago (encargo_id, administrador_id, monto, metodo, nota)
         VALUES (:encargo_id, :admin_id, :monto, :metodo, :nota)"
    );
    $stmt2->execute([
        ':encargo_id' => $encargoId,
        ':admin_id'   => $adminId,
        ':monto'      => $monto,
        ':metodo'     => $metodoPago,
        ':nota'       => $nota ?: null,
    ]);

    $mensaje = $nuevoEstado === 'entregado'
        ? 'Pago registrado. Encargo marcado como entregado.'
        : 'Pago registrado correctamente.';

    return ['ok' => true, 'mensaje' => $mensaje];
}

/**
 * Trae todos los pagos individuales de un encargo
 */
public function getPagosPorEncargo(int $encargoId): array {
    $stmt = $this->pdo->prepare(
        "SELECT id, monto, metodo, nota, fecha
         FROM pago
         WHERE encargo_id = :encargo_id
         ORDER BY fecha ASC"
    );
    $stmt->execute([':encargo_id' => $encargoId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

    public function getResumenMensual(int $adminId): array {
    $stmt = $this->pdo->prepare(
        "SELECT 
            COALESCE(SUM(CASE WHEN MONTH(e.created_at) = MONTH(CURDATE()) 
                AND YEAR(e.created_at) = YEAR(CURDATE()) 
                THEN e.sena ELSE 0 END), 0) AS cobrado_este_mes,
            COALESCE(SUM(CASE WHEN MONTH(e.created_at) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) 
                AND YEAR(e.created_at) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) 
                THEN e.sena ELSE 0 END), 0) AS cobrado_mes_anterior
         FROM encargo e
         WHERE e.administrador_id = :admin_id"
    );
    $stmt->execute([':admin_id' => $adminId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

}