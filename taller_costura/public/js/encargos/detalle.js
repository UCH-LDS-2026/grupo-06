// ════════════════════════════════════════════════════════
//  detalle.js  —  JS exclusivo para detalle_encargo.php
// ════════════════════════════════════════════════════════

const meta = document.getElementById('detalle-meta');
const SALDO_PENDIENTE_DETALLE = meta ? parseFloat(meta.dataset.saldo) : 0;

document.addEventListener('DOMContentLoaded', () => {
    const fill = document.getElementById('progresoFill');
    if (fill && meta) fill.dataset.total = meta.dataset.montoTotal;
});

function cerrarModalPago() {
    document.getElementById('modalPago').style.display = 'none';
    document.getElementById('inputMonto').value = '';
    document.getElementById('inputNota').value  = '';
    document.getElementById('inputMontoHint').textContent = '';
    document.getElementById('btnConfirmarPagoDetalle').disabled = false;
    const efectivo = document.querySelector('input[name="detalle_metodo_pago"][value="efectivo"]');
    if (efectivo) efectivo.checked = true;
}

function validarMontoDetalle(input) {
    const hint = document.getElementById('inputMontoHint');
    const btn  = document.getElementById('btnConfirmarPagoDetalle');
    const val  = parseFloat(input.value);
    if (isNaN(val) || val <= 0) {
        hint.textContent = 'Ingresá un monto mayor a cero.';
        btn.disabled = true;
    } else if (val > SALDO_PENDIENTE_DETALLE) {
        hint.textContent = 'El monto no puede superar el saldo pendiente.';
        btn.disabled = true;
    } else {
        hint.textContent = '';
        btn.disabled = false;
    }
}

function toggleHistorialPagos() {
    const lista = document.getElementById('historial-pagos-lista');
    const icon  = document.getElementById('historial-toggle-icon');
    if (lista.style.display === 'none' || lista.classList.contains('historial-pagos-lista--hidden')) {
        lista.style.display = 'block';
        lista.classList.remove('historial-pagos-lista--hidden');
        icon.textContent = '↑';
    } else {
        lista.style.display = 'none';
        icon.textContent = '↓';
    }
}

function eliminarPago(pagoId, monto, encargoId) {
    if (!confirm('¿Eliminar este pago de $' + Number(monto).toLocaleString('es-AR') + '?')) return;
    fetch('index.php?page=eliminar-pago', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ pago_id: pagoId, encargo_id: encargoId, monto: monto })
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            document.getElementById('pago-' + pagoId).remove();
            setTimeout(() => location.reload(), 800);
        } else {
            alert(data.mensaje || 'Error al eliminar');
        }
    })
    .catch(() => alert('Error de conexión'));
}