// ════════════════════════════════════════════════════════
//  encargos.js  —  JS unificado para views/encargos/
// ════════════════════════════════════════════════════════

// ── Utilidades compartidas ───────────────────────────────
function fmtMontoJS(n) {
  return '$' + Math.round(n).toLocaleString('es-AR');
}
function showToast(msg, ok) {
  const t = document.getElementById('toast');
  if (!t) return;
  t.textContent = msg;
  t.className = 'toast' + (ok === false ? ' toast-error' : '');
  void t.offsetWidth;
  t.classList.add('show');
  setTimeout(() => t.classList.remove('show'), 2800);
}

// ── Autocomplete de clientes (crear.php y editar.php) ────
function initClienteAutocomplete(listaClientes) {
  const inputBusqueda = document.getElementById('clienteBusqueda');
  const inputHidden   = document.getElementById('cliente_id');
  const listaEl       = document.getElementById('clienteLista');
  if (!inputBusqueda || !listaEl) return;

  function renderLista(filtro) {
    const texto = filtro.trim().toLowerCase();
    const filtrados = texto === '' ? listaClientes : listaClientes.filter(c => c.nombre.toLowerCase().includes(texto));
    let html = '<div class="cliente-opcion vacia" data-id="">Sin cliente...</div>';
    html += filtrados.length
      ? filtrados.map(c => `<div class="cliente-opcion" data-id="${c.id}" data-nombre="${c.nombre.replace(/"/g,'&quot;')}">${c.nombre}</div>`).join('')
      : '<div class="cliente-opcion vacia">Sin resultados</div>';
    listaEl.innerHTML = html;
    listaEl.style.display = 'block';
  }

  inputBusqueda.addEventListener('input', () => {
    inputHidden.value = '';
    renderLista(inputBusqueda.value);
  });
  inputBusqueda.addEventListener('focus', () => {
    if (!inputHidden.value && inputBusqueda.value !== 'Sin cliente...') {
      inputBusqueda.value = '';
    }
    renderLista(inputBusqueda.value);
  });

  listaEl.addEventListener('click', (e) => {
    const opcion = e.target.closest('.cliente-opcion');
    if (!opcion || opcion.classList.contains('vacia') && !opcion.dataset.id && opcion.textContent.trim() === 'Sin resultados') return;
    if (opcion.dataset.id) {
      inputHidden.value   = opcion.dataset.id;
      inputBusqueda.value = opcion.dataset.nombre;
    } else {
      inputHidden.value   = '';
      inputBusqueda.value = 'Sin cliente...';
    }
    listaEl.style.display = 'none';
  });

  document.addEventListener('click', (e) => {
    if (!e.target.closest('.cliente-autocomplete')) {
      listaEl.style.display = 'none';
      if (!inputHidden.value && inputBusqueda.value !== 'Sin cliente...') {
        inputBusqueda.value = '';
      }
    }
  });
}

// ── index.php: modal nuevo encargo ──────────────────────
function abrirModalEncargo() {
  document.getElementById('modalEncargo').classList.add('visible');
  document.body.style.overflow = 'hidden';
}
function cerrarModalEncargo() {
  const modal = document.getElementById('modalEncargo');
  modal.classList.remove('visible');
  document.body.style.overflow = '';
  modal.querySelectorAll('input[type="text"], input[type="number"], input[type="date"], textarea').forEach(el => el.value = '');
  modal.querySelectorAll('select').forEach(el => el.selectedIndex = 0);
  const clienteHidden = document.getElementById('cliente_id');
  const clienteBusqueda = document.getElementById('clienteBusqueda');
  if (clienteHidden) clienteHidden.value = '';
  if (clienteBusqueda) clienteBusqueda.value = '';
  const errorDiv = document.getElementById('modal-error-encargo');
  if (errorDiv) errorDiv.style.display = 'none';
}

// ── index.php: modal todos los entregados ───────────────
function abrirModalEntregados() {
  document.getElementById('modalEntregados').classList.add('visible');
  document.body.style.overflow = 'hidden';
}
function cerrarModalEntregados() {
  document.getElementById('modalEntregados').classList.remove('visible');
  document.body.style.overflow = '';
}

// ── index.php: paginación ────────────────────────────────
let encFiltroEstados = [];
let encFiltroSinCliente = false;
let encPaginaActual  = 1;
const ENC_POR_PAG    = 5;
let encTodasVisibles = [];

function renderPaginaEnc() {
  const total   = encTodasVisibles.length;
  const totPags = Math.max(1, Math.ceil(total / ENC_POR_PAG));
  if (encPaginaActual > totPags) encPaginaActual = totPags;

  const inicio = (encPaginaActual - 1) * ENC_POR_PAG;
  const fin    = inicio + ENC_POR_PAG;

  document.querySelectorAll('#enc-cards-container .card-encargo').forEach(c => c.style.display = 'none');
  encTodasVisibles.slice(inicio, fin).forEach(c => c.style.display = '');

  document.getElementById('enc-pag-info').textContent =
    total === 0 ? 'Sin resultados' : `Página ${encPaginaActual} de ${totPags}`;

  document.getElementById('enc-pag-prev').disabled = encPaginaActual <= 1;
  document.getElementById('enc-pag-next').disabled = encPaginaActual >= totPags;
  document.getElementById('enc-paginacion').style.display = total <= ENC_POR_PAG ? 'none' : 'flex';
}

function cambiarPaginaEnc(dir) {
  encPaginaActual += dir;
  renderPaginaEnc();
  document.getElementById('enc-section-title').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function toggleCalendarioEnc() {
  document.getElementById('enc-date-picker').classList.toggle('visible');
}

function filtrarPorEstadoCard(estados) {
  encFiltroEstados = estados;
  filtrarEncargos();
}

function filtrarSinCliente() {
  encFiltroSinCliente = true;
  filtrarEncargos();
  document.getElementById('enc-limpiar-btn').style.display = '';
  document.getElementById('enc-section-title').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function filtrarEncargos() {
  const textoVal = document.getElementById('enc-q').value || '';
  const texto    = textoVal.toLowerCase();
  const desde    = document.getElementById('enc-desde').value;
  const hasta    = document.getElementById('enc-hasta').value;

  encTodasVisibles = Array.from(document.querySelectorAll('#enc-cards-container .card-encargo')).filter(card => {
    const cliente = (card.dataset.cliente || '').toLowerCase();
    const tipo    = (card.querySelector('h3')?.textContent || '').toLowerCase();
    const fecha   = card.dataset.fecha  || '';
    const estado  = card.dataset.estado || '';

    const okTexto  = !texto || cliente.includes(texto) || tipo.includes(texto);
    const okEstado     = encFiltroEstados.length === 0 || encFiltroEstados.includes(estado);
    const okSinCliente = !encFiltroSinCliente || card.dataset.sinCliente === '1';
    let   okFecha  = true;
    if (desde && fecha < desde) okFecha = false;
    if (hasta && fecha > hasta) okFecha = false;

    return okTexto && okEstado && okFecha && okSinCliente;
  });

  encPaginaActual = 1;
  renderPaginaEnc();

  const activo = textoVal || desde || hasta || encFiltroEstados.length > 0;
  document.getElementById('enc-limpiar-btn').style.display = activo ? '' : 'none';
}

function limpiarFiltrosEnc() {
  encFiltroEstados = [];
  encFiltroSinCliente = false;
  document.getElementById('enc-q').value      = '';
  document.getElementById('enc-desde').value  = '';
  document.getElementById('enc-hasta').value  = '';
  document.getElementById('enc-date-picker').classList.remove('visible');
  document.getElementById('enc-limpiar-btn').style.display = 'none';
  encTodasVisibles = Array.from(document.querySelectorAll('#enc-cards-container .card-encargo'));
  encPaginaActual  = 1;
  renderPaginaEnc();
}

// ── detalle.php: eliminar encargo ────────────────────────
function eliminarEncargo(id, pagado) {
  if (pagado > 0) {
    if (!confirm(`Este encargo tiene $${Math.round(pagado).toLocaleString('es-AR')} de pago. ¿Estás seguro de que querés eliminarlo de todas formas? Esta acción no se puede deshacer.`)) return;
  } else {
    if (!confirm('¿Estás seguro de que querés eliminar este encargo? Esta acción no se puede deshacer.')) return;
  }
  fetch('index.php?page=eliminar-encargo', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({id})
  })
  .then(r => r.json())
  .then(d => {
    if (d.ok) {
      showToast('✅ Encargo eliminado correctamente');
      setTimeout(() => location.href = 'index.php', 700);
    } else {
      showToast('❌ Error al eliminar el encargo', false);
    }
  })
  .catch(() => showToast('❌ Error de red', false));
}

// ── detalle.php: observación especial ───────────────────
function eliminarObservacionEspecial(idEncargo) {
  if (!confirm('¿Estás seguro de que querés eliminar la observación especial?')) return;
  fetch('index.php?page=eliminar-observacion-especial', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({id: idEncargo})
  })
  .then(r => r.json())
  .then(d => {
    if (d.ok) {
      showToast('✅ Observación eliminada con éxito');
      setTimeout(() => location.reload(), 600);
    } else {
      showToast('❌ Error al eliminar la observación', false);
    }
  })
  .catch(() => showToast('❌ Error de conexión de red', false));
}

// ── detalle.php: historial de observaciones ──────────────
function abrirFormObs() {
  document.getElementById('formNuevaObs').style.display = 'block';
  document.getElementById('inputNuevaObs').focus();
}
function cancelarFormObs() {
  document.getElementById('formNuevaObs').style.display = 'none';
  document.getElementById('inputNuevaObs').value = '';
}
function guardarNuevaObs() {
  const detalle = document.getElementById('inputNuevaObs').value.trim();
  if (!detalle) { showToast('❌ Escribí una observación antes de guardar', false); return; }

  const ENC_ID = parseInt(document.getElementById('estadoGrid')?.dataset.id || 0);
  fetch('index.php?page=agregar-observacion', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({encargo_id: ENC_ID, detalle})
  })
  .then(r => r.json())
  .then(d => {
    if (!d.ok) { showToast('❌ Error al guardar', false); return; }
    const obs  = d.observacion;
    const fecha = new Date(obs.fecha).toLocaleDateString('es-AR', {day:'2-digit',month:'2-digit',year:'numeric',hour:'2-digit',minute:'2-digit'});
    const sinMsg = document.getElementById('sinObsMsg');
    if (sinMsg) sinMsg.remove();
    document.getElementById('listaHistorialObs').insertAdjacentHTML('beforeend', `
      <div class="obs-item" id="obs-${obs.id}" style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px;">
        <div>
          ${obs.detalle.replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;')}
          <div class="obs-fecha">${fecha}</div>
        </div>
        <button onclick="eliminarObsHistorial(${obs.id})" title="Eliminar"
          style="background:none;border:none;cursor:pointer;color:#b05040;font-size:1rem;flex-shrink:0;padding:2px 6px;">✕</button>
      </div>
    `);
    cancelarFormObs();
    showToast('✅ Observación agregada');
  })
  .catch(() => showToast('❌ Error de red', false));
}
function eliminarObsHistorial(id) {
  if (!confirm('¿Eliminar esta observación del historial?')) return;
  fetch('index.php?page=eliminar-observacion', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({id})
  })
  .then(r => r.json())
  .then(d => {
    if (d.ok) {
      const el = document.getElementById('obs-' + id);
      if (el) el.remove();
      const lista = document.getElementById('listaHistorialObs');
      if (!lista.querySelector('.obs-item')) {
        lista.insertAdjacentHTML('beforeend', '<p class="info-text" id="sinObsMsg">Sin observaciones registradas.</p>');
      }
      showToast('✅ Observación eliminada');
    } else {
      showToast('❌ Error al eliminar', false);
    }
  })
  .catch(() => showToast('❌ Error de red', false));
}

// ── detalle.php: cambio de estado ────────────────────────
const badgeClasses = {
  pendiente:  'badge-pendiente',
  en_proceso: 'badge-proceso',
  listo:      'badge-listo',
  entregado:  'badge-entregado'
};
const badgeLabels = {
  pendiente: 'Pendiente', en_proceso: 'En Proceso', listo: 'Listo', entregado: 'Entregado'
};

document.addEventListener('DOMContentLoaded', () => {

  // Paginación index
  if (document.getElementById('enc-cards-container')) {
    encTodasVisibles = Array.from(document.querySelectorAll('#enc-cards-container .card-encargo'));
    renderPaginaEnc();
  }

  // Modales index
  const modalEncargo = document.getElementById('modalEncargo');
  if (modalEncargo) {
    document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarModalEncargo(); });
    modalEncargo.addEventListener('click', e => { if (e.target === modalEncargo) cerrarModalEncargo(); });
  }
  const modalEntregados = document.getElementById('modalEntregados');
  if (modalEntregados) {
    modalEntregados.addEventListener('click', e => { if (e.target === modalEntregados) cerrarModalEntregados(); });
  }

  // Cerrar date-picker al click afuera
  const picker = document.getElementById('enc-date-picker');
  const calBtn = document.getElementById('enc-cal-btn');
  if (picker && calBtn) {
    document.addEventListener('click', (e) => {
      if (!picker.contains(e.target) && !calBtn.contains(e.target)) {
        picker.classList.remove('visible');
      }
    });
  }

  // Estado grid (detalle)
  const estadoGrid = document.getElementById('estadoGrid');
  if (estadoGrid) {
    estadoGrid.addEventListener('click', function(e) {
      const btn = e.target.closest('.estado-btn');
      if (!btn) return;
      const nuevoEstado = btn.dataset.estado;
      const id = this.dataset.id;
      const estadoAnterior = this.dataset.current;

      document.querySelectorAll('.estado-btn').forEach(b => b.classList.remove('activo'));
      btn.classList.add('activo');

      fetch('index.php?page=actualizar-estado-encargo', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id, estado: nuevoEstado})
      })
      .then(r => r.json())
      .then(d => {
        if (d.ok) {
          estadoGrid.dataset.current = nuevoEstado;
          const badge = document.getElementById('badgeEstado');
          badge.className = 'badge ' + (badgeClasses[nuevoEstado] || '');
          badge.textContent = badgeLabels[nuevoEstado] || nuevoEstado;
          showToast('✅ Estado actualizado');
        } else {
          document.querySelectorAll('.estado-btn').forEach(b => b.classList.remove('activo'));
          document.querySelectorAll('.estado-btn').forEach(b => {
            if (b.dataset.estado === estadoAnterior) b.classList.add('activo');
          });
          showToast('❌ ' + (d.mensaje || 'Error al actualizar'), false);
        }
      })
      .catch(() => showToast('❌ Error de red', false));
    });
  }

  // Modal de pago (detalle)
  const modal      = document.getElementById('modalPago');
  const btnAbrir   = document.getElementById('btnAbrirPago');
  const btnCerrar  = document.getElementById('btnCerrarModal');
  const btnConfirmar = document.getElementById('btnConfirmarPago');
  if (modal) {
    if (btnAbrir)  btnAbrir.addEventListener('click', () => modal.style.display = 'flex');
    if (btnCerrar) btnCerrar.addEventListener('click', () => modal.style.display = 'none');
    modal.addEventListener('click', e => { if (e.target === modal) modal.style.display = 'none'; });
  }
  if (btnConfirmar) {
    const MESES = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
    const ENCARGO_ID  = parseInt(document.getElementById('estadoGrid')?.dataset.id || 0);
    const MONTO_TOTAL = parseFloat(document.getElementById('progresoFill')?.dataset.total || 0);

    btnConfirmar.addEventListener('click', () => {
      const monto  = parseFloat(document.getElementById('inputMonto').value);
      const metodoRadio = document.querySelector('input[name="detalle_metodo_pago"]:checked');
      const metodo = metodoRadio ? metodoRadio.value : 'efectivo';
      const nota   = document.getElementById('inputNota').value.trim();

      if (!monto || monto <= 0) { showToast('❌ Ingresá un monto válido', false); return; }

      btnConfirmar.disabled = true;
      btnConfirmar.textContent = 'Guardando…';

      fetch('index.php?page=registrar-pago-detalle', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({encargo_id: ENCARGO_ID, monto, metodo, nota})
      })
      .then(r => r.json())
      .then(d => {
        btnConfirmar.disabled = false;
        btnConfirmar.textContent = 'Confirmar Pago';
        if (!d.ok) { showToast('❌ ' + (d.mensaje || 'Error'), false); return; }

        modal.style.display = 'none';
        document.getElementById('inputMonto').value = '';
        document.getElementById('inputNota').value  = '';

        const nuevoPagado = parseFloat(d.nueva_sena);
        const nuevoSaldo  = MONTO_TOTAL - nuevoPagado;
        const pct = MONTO_TOTAL > 0 ? Math.round((nuevoPagado / MONTO_TOTAL) * 100) : 0;

        document.getElementById('spanTotalPagado').textContent = fmtMontoJS(nuevoPagado);
        document.getElementById('spanSaldo').textContent       = fmtMontoJS(nuevoSaldo);
        document.getElementById('spanPorcentaje').textContent  = pct + '%';
        document.getElementById('progresoFill').style.width    = pct + '%';
        document.getElementById('modalSaldo').textContent      = fmtMontoJS(nuevoSaldo);

        if (nuevoSaldo <= 0 && btnAbrir) btnAbrir.style.display = 'none';

        const now   = new Date();
        const fecha = now.getDate() + ' de ' + MESES[now.getMonth()] + ' de ' + now.getFullYear();
        const card  = document.getElementById('cardHistorial');
        const lista = document.getElementById('listaPagos');
        card.style.display = 'block';
        lista.insertAdjacentHTML('afterbegin', `
          <div class="pago-hist-item">
            <div class="pago-hist-icon">✓</div>
            <div class="pago-hist-info">
              <strong>${fmtMontoJS(monto)}</strong>
              <span>${fecha}</span>
              <div><span class="pago-metodo-tag">${metodo.charAt(0).toUpperCase()+metodo.slice(1)}</span></div>
              ${nota ? '<em>' + nota + '</em>' : ''}
            </div>
          </div>
        `);
        showToast('✅ Pago registrado correctamente');
      })
      .catch(() => {
        btnConfirmar.disabled = false;
        btnConfirmar.textContent = 'Confirmar Pago';
        showToast('❌ Error de red', false);
      });
    });
  }
});