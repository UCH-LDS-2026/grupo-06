// ════════════════════════════════════════════════════════
//  editar.js  —  JS exclusivo para views/encargos/editar.php
// ════════════════════════════════════════════════════════

document.addEventListener('DOMContentLoaded', () => {
  initClienteAutocomplete(CLIENTES);

  const meta     = document.getElementById('editar-meta');
  const senActual = meta ? parseFloat(meta.dataset.sena) : 0;

  document.querySelector('form').addEventListener('submit', function(e) {
    const total    = parseFloat(document.getElementById('monto_total').value) || 0;
    const errorDiv = document.getElementById('editar-error');
    const errores  = [];

    if (senActual > total) {
      errores.push('El precio total no puede ser menor a lo ya cobrado ($' + Math.round(senActual).toLocaleString('es-AR') + ').');
    }

    if (errores.length > 0) {
      e.preventDefault();
      errorDiv.innerHTML = errores.map(e => `• ${e}`).join('<br>');
      errorDiv.style.display = 'block';
      errorDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    } else {
      errorDiv.style.display = 'none';
    }
  });
});