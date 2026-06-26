// ════════════════════════════════════════════════════════
//  index.js  —  JS exclusivo para views/encargos/index.php
// ════════════════════════════════════════════════════════

function switchTabEnc(tab) {
  document.querySelectorAll('.enc-tab-btn').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.enc-tab-panel').forEach(p => p.classList.remove('active'));
  document.getElementById('tab-btn-' + tab).classList.add('active');
  document.getElementById('tab-panel-' + tab).classList.add('active');
}

function validarYGuardarEncargo() {
  const tipo     = document.querySelector('#modalEncargo [name="tipo"]');
  const fecha    = document.getElementById('modal_fecha_entrega');
  const total    = document.getElementById('modal_monto_total');
  const sena     = document.getElementById('modal_sena');
  const errorDiv = document.getElementById('modal-error-encargo');
  const hoy      = new Date(); hoy.setHours(0,0,0,0);
  const errores  = [];

  if (!tipo || !tipo.value.trim()) errores.push('El tipo de prenda es obligatorio.');

  if (!fecha.value) {
    errores.push('La fecha de entrega es obligatoria.');
  } else if (new Date(fecha.value + 'T00:00:00') < hoy) {
    errores.push('La fecha de entrega no puede ser anterior a hoy.');
  }

  const montoVal = parseFloat(total.value);
  const senaVal  = parseFloat(sena.value);

  if (total.value !== '' && !isNaN(montoVal) && montoVal < 1000)
    errores.push('El precio total debe ser al menos $1.000.');

  if (!sena.value || isNaN(senaVal) || senaVal < 1) {
    errores.push('La seña inicial es obligatoria y debe ser mayor a $0.');
  } else if (total.value !== '' && !isNaN(montoVal) && montoVal >= 1000 && senaVal > montoVal) {
    errores.push('La seña no puede superar el precio total.');
  } else if ((total.value === '' || isNaN(montoVal)) && senaVal <= 1000) {
    errores.push('La seña no puede ser menor o igual al precio total mínimo ($1.000). Completá el precio total primero.');
  }

  if (errores.length > 0) {
    errorDiv.innerHTML = errores.map(e => `• ${e}`).join('<br>');
    errorDiv.style.display = 'block';
    errorDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    return;
  }

  errorDiv.style.display = 'none';
  tipo.closest('form').submit();
}

document.addEventListener('DOMContentLoaded', () => {
  initClienteAutocomplete(CLIENTES_MODAL);

  const meta = document.getElementById('index-meta');
  if (meta?.dataset.errorCrear === '1') abrirModalEncargo();
});