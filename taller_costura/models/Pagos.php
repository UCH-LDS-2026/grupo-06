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
        "SELECT COALESCE(SUM(p.monto), 0) AS total
         FROM pago p
         INNER JOIN encargo e ON p.encargo_id = e.id
         WHERE e.administrador_id = :admin_id
           AND e.estado != 'entregado'"
    );
    $stmt->execute([':admin_id' => $adminId]);
    return (float) $stmt->fetchColumn();
}
 
    /**
     * Suma de todos los saldos pendientes (monto_total - sena)
     */
    public function getSaldoPendienteTotal(int $adminId): float {
    $stmt = $this->pdo->prepare(
        "SELECT COALESCE(SUM(e.monto_total - COALESCE(pagado.total, 0)), 0) AS total
         FROM encargo e
         LEFT JOIN (
             SELECT encargo_id, SUM(monto) as total
             FROM pago
             GROUP BY encargo_id
         ) pagado ON pagado.encargo_id = e.id
         WHERE e.administrador_id = :admin_id
           AND e.estado != 'entregado'
           AND (e.monto_total - COALESCE(pagado.total, 0)) > 0"
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
             ORDER BY 
            CASE WHEN e.fecha_entrega >= CURDATE() THEN 0 ELSE 1 END ASC,
            ABS(DATEDIFF(e.fecha_entrega, CURDATE())) ASC"
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
            COALESCE(SUM(CASE WHEN MONTH(p.fecha) = MONTH(CURDATE()) 
                AND YEAR(p.fecha) = YEAR(CURDATE()) 
                THEN p.monto ELSE 0 END), 0) AS cobrado_este_mes,
            COALESCE(SUM(CASE WHEN MONTH(p.fecha) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) 
                AND YEAR(p.fecha) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) 
                THEN p.monto ELSE 0 END), 0) AS cobrado_mes_anterior
         FROM pago p
         INNER JOIN encargo e ON p.encargo_id = e.id
         WHERE e.administrador_id = :admin_id"
    );
    $stmt->execute([':admin_id' => $adminId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

/**
 * Resumen de cobros agrupado por mes y año
 */
public function getResumenPorMes(int $adminId): array {
    $stmt = $this->pdo->prepare(
        "SELECT 
            YEAR(p.fecha) as anio,
            MONTH(p.fecha) as mes,
            SUM(p.monto) as total
         FROM pago p
         INNER JOIN encargo e ON p.encargo_id = e.id
         WHERE e.administrador_id = :admin_id
         GROUP BY YEAR(p.fecha), MONTH(p.fecha)
         ORDER BY anio DESC, mes DESC"
    );
    $stmt->execute([':admin_id' => $adminId]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Detalle completo de un mes específico
 */
public function getDetalleMes(int $adminId, int $anio, int $mes): array {
    // Total y cantidad de pagos
    $stmtTotal = $this->pdo->prepare(
        "SELECT 
            COUNT(*) as cantidad_pagos,
            SUM(p.monto) as total,
            SUM(CASE WHEN e.estado = 'entregado' THEN 1 ELSE 0 END) as entregados
         FROM pago p
         INNER JOIN encargo e ON p.encargo_id = e.id
         WHERE e.administrador_id = :admin_id
           AND YEAR(p.fecha) = :anio
           AND MONTH(p.fecha) = :mes"
    );
    $stmtTotal->execute([':admin_id' => $adminId, ':anio' => $anio, ':mes' => $mes]);
    $resumen = $stmtTotal->fetch(PDO::FETCH_ASSOC);

    // Por método de pago
    $stmtMetodos = $this->pdo->prepare(
        "SELECT 
            p.metodo,
            SUM(p.monto) as total,
            COUNT(*) as cantidad
         FROM pago p
         INNER JOIN encargo e ON p.encargo_id = e.id
         WHERE e.administrador_id = :admin_id
           AND YEAR(p.fecha) = :anio
           AND MONTH(p.fecha) = :mes
         GROUP BY p.metodo
         ORDER BY total DESC"
    );
    $stmtMetodos->execute([':admin_id' => $adminId, ':anio' => $anio, ':mes' => $mes]);
    $metodos = $stmtMetodos->fetchAll(PDO::FETCH_ASSOC);

    // Top clientas
    $stmtClientes = $this->pdo->prepare(
        "SELECT 
            COALESCE(c.nombre, 'Sin cliente') as nombre,
            SUM(p.monto) as total
         FROM pago p
         INNER JOIN encargo e ON p.encargo_id = e.id
         LEFT JOIN cliente c ON e.cliente_id = c.id
         WHERE e.administrador_id = :admin_id
           AND YEAR(p.fecha) = :anio
           AND MONTH(p.fecha) = :mes
         GROUP BY c.id, c.nombre
         ORDER BY total DESC
         LIMIT 3"
    );
    $stmtClientes->execute([':admin_id' => $adminId, ':anio' => $anio, ':mes' => $mes]);
    $topClientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);

    // Pagos individuales
    $stmtPagos = $this->pdo->prepare(
        "SELECT 
            p.monto,
            p.metodo,
            p.nota,
            p.fecha,
            e.tipo,
            COALESCE(c.nombre, 'Sin cliente') as cliente_nombre
         FROM pago p
         INNER JOIN encargo e ON p.encargo_id = e.id
         LEFT JOIN cliente c ON e.cliente_id = c.id
         WHERE e.administrador_id = :admin_id
           AND YEAR(p.fecha) = :anio
           AND MONTH(p.fecha) = :mes
         ORDER BY p.fecha DESC"
    );
    $stmtPagos->execute([':admin_id' => $adminId, ':anio' => $anio, ':mes' => $mes]);
    $pagos = $stmtPagos->fetchAll(PDO::FETCH_ASSOC);

    return [
        'resumen'     => $resumen,
        'metodos'     => $metodos,
        'topClientes' => $topClientes,
        'pagos'       => $pagos,
    ];
}

}