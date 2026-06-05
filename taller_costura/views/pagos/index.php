<?php
// Panel de pagos - componente reutilizable
// Se incluye desde el detalle de encargo
// Variables esperadas: $monto_total, $sena
$saldo = ($monto_total ?? 0) - ($sena ?? 0);
?>
<div class="panel-pagos">
    <div class="panel-pagos-titulo">
        <span>💲</span>
        <h3>Pagos</h3>
    </div>
    <div class="pago-fila">
        <span class="pago-label">Precio Total</span>
        <span class="pago-valor">$<?= number_format($monto_total ?? 0, 0, ',', '.') ?></span>
    </div>
    <div class="divisor"></div>
    <div class="pago-fila">
        <span class="pago-label">Seña</span>
        <span class="pago-valor">$<?= number_format($sena ?? 0, 0, ',', '.') ?></span>
    </div>
    <div class="divisor"></div>
    <div class="pago-fila">
        <span class="pago-label">Saldo Pendiente</span>
        <span class="pago-valor saldo-pendiente">$<?= number_format($saldo, 0, ',', '.') ?></span>
    </div>
</div>

<style>
.panel-pagos {
    background: #FFFFFF;
    border: 1px solid #EDE8E0;
    border-radius: 12px;
    padding: 24px;
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.panel-pagos-titulo {
    display: flex;
    align-items: center;
    gap: 8px;
    padding-bottom: 8px;
}

.panel-pagos-titulo h3 {
    font-family: 'Playfair Display', serif;
    font-size: 18px;
    font-weight: 500;
    color: #2C1810;
}

.panel-pagos-titulo span {
    color: #7D4E2F;
    font-size: 18px;
}

.pago-fila {
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.pago-label {
    font-size: 13px;
    color: #8B7355;
}

.pago-valor {
    font-size: 15px;
    font-weight: 500;
    color: #2C1810;
}

.saldo-pendiente {
    color: #C0392B;
    font-size: 18px;
    font-weight: 600;
}

.divisor {
    height: 1px;
    background: #EDE8E0;
}
</style>