<?php
require_once BASE_PATH . '/models/Encargo.php';
require_once BASE_PATH . '/models/Pagos.php';
 
class PagoController {
 
    private $pdo;
    private $pagoModel;
    private $encargoModel;
    private int $adminId;
 
    public function __construct($pdo = null) {
        if ($pdo) {
            $this->pdo       = $pdo;
            $this->pagoModel = new Pago($pdo);
        }
        $this->encargoModel = new Encargo($pdo);
 
        if (session_status() === PHP_SESSION_NONE) session_start();
        $this->adminId = $_SESSION['admin_id'] ?? 1;
    }
 
    // -------------------------------------------------------
    // Métodos originales
    // -------------------------------------------------------
 
    public function registrarSenia($encargo_id, $monto_senia) {
        $encargo = $this->encargoModel->obtenerPorId($encargo_id);
 
        if (!$encargo) {
            return ['error' => 'Encargo no encontrado'];
        }
        if ($monto_senia > $encargo['monto_total']) {
            return ['error' => 'La seña no puede ser mayor al monto total'];
        }
 
        $this->encargoModel->actualizarSenia($encargo_id, $monto_senia);
        $saldo = $this->calcularSaldo($encargo['monto_total'], $monto_senia);
 
        return [
            'success'         => true,
            'senia'           => $monto_senia,
            'saldo_pendiente' => $saldo,
        ];
    }
 
    public function calcularSaldo($monto_total, $senia) {
        return $monto_total - $senia;
    }
 
    public function registrarPagoRestante($encargo_id) {
        $encargo = $this->encargoModel->obtenerPorId($encargo_id);
 
        if (!$encargo) {
            return ['error' => 'Encargo no encontrado'];
        }
 
        $saldo = $this->calcularSaldo($encargo['monto_total'], $encargo['sena']);
 
        if ($saldo <= 0) {
            return ['error' => 'Este encargo no tiene saldo pendiente'];
        }
 
        $this->encargoModel->actualizarSenia($encargo_id, $encargo['monto_total']);
 
        return [
            'success'      => true,
            'mensaje'      => 'Pago completo registrado',
            'monto_pagado' => $saldo,
        ];
    }
 
    // -------------------------------------------------------
    // Métodos nuevos para la vista Gestión de Pagos
    // -------------------------------------------------------
 
    public function cargarDatos(): void {
        $totalCobrado        = $this->pagoModel->getTotalCobrado($this->adminId);
        $saldoPendienteTotal = $this->pagoModel->getSaldoPendienteTotal($this->adminId);
        $totalSenas          = $this->pagoModel->getTotalSenas($this->adminId);
        $cuentasCount        = $this->pagoModel->getCuentasPorCobrarCount($this->adminId);
        $cuentasPorCobrar    = $this->pagoModel->getCuentasPorCobrar($this->adminId);
        $historialPagos      = $this->pagoModel->getHistorialPagos($this->adminId);
        $tabActiva           = $_GET['tab'] ?? 'cuentas';
        $flash               = $_SESSION['flash'] ?? null;
        $resumenMensual = $this->pagoModel->getResumenMensual($this->adminId);
        $GLOBALS['resumenMensual'] = $resumenMensual;
        $GLOBALS['pagoModel'] = $this->pagoModel;
        unset($_SESSION['flash']);
 
        foreach (get_defined_vars() as $key => $value) {
            $GLOBALS[$key] = $value;
        }
    }
 
    public function manejar(): void {
        $encargoId = (int)($_POST['encargo_id'] ?? 0);
        $monto      = (float)str_replace(',', '.', $_POST['monto'] ?? 0);
        $metodoPago = $_POST['metodo_pago'] ?? 'efectivo';
        $resultado  = $this->pagoModel->registrarPago($encargoId, $this->adminId, $monto, $metodoPago);
 
        header('Content-Type: application/json');
        echo json_encode($resultado);
        exit;
    }
}