<?php
require_once BASE_PATH . '/models/Encargo.php';

class PagoController {
    private $encargoModel;

    public function __construct() {
        $this->encargoModel = new Encargo();
    }

    /**
     * Registra la seña inicial de un encargo
     */
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
            'success' => true,
            'senia' => $monto_senia,
            'saldo_pendiente' => $saldo
        ];
    }

    /**
     * Calcula el saldo pendiente de un encargo
     */
    public function calcularSaldo($monto_total, $senia) {
        return $monto_total - $senia;
    }

    /**
     * Registra el pago del saldo restante
     */
    public function registrarPagoRestante($encargo_id) {
        $encargo = $this->encargoModel->obtenerPorId($encargo_id);

        if (!$encargo) {
            return ['error' => 'Encargo no encontrado'];
        }

        $saldo = $this->calcularSaldo(
            $encargo['monto_total'],
            $encargo['sena']
        );

        if ($saldo <= 0) {
            return ['error' => 'Este encargo no tiene saldo pendiente'];
        }

        $this->encargoModel->actualizarSenia($encargo_id, $encargo['monto_total']);

        return [
            'success' => true,
            'mensaje' => 'Pago completo registrado',
            'monto_pagado' => $saldo
        ];
    }
}
?>