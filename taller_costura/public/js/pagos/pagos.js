/* ── Estado del modal ────────────────────────────── */
let modalData = { encargoId: 0, saldoPendiente: 0 };

/* ── Tabs ─────────────────────────────────────────── */
function cambiarTab(tab, btn) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    btn.classList.add('active');
}

/* ── Modal ────────────────────────────────────────── */
function abrirModal(id, tipo, cliente, total, sena, saldo) {
    modalData = { encargoId: id, saldoPendiente: parseFloat(saldo) };

    document.getElementById('modal-tipo').textContent    = tipo;
    document.getElementById('modal-cliente').textContent = cliente;
    document.getElementById('modal-total').textContent   = formatPesos(total);
    document.getElementById('modal-sena').textContent    = formatPesos(sena);
    document.getElementById('modal-saldo').textContent   = formatPesos(saldo);

    const input = document.getElementById('modal-monto');
    input.value = '';
    input.max   = saldo;

    document.getElementById('modal-monto-hint').textContent = '';
    document.getElementById('modal-monto-hint').className   = 'hint';
    document.getElementById('btn-confirmar').disabled = false;

    document.getElementById('modalPago').classList.add('open');
    setTimeout(() => input.focus(), 80);
}

function cerrarModal() {
    document.getElementById('modalPago').classList.remove('open');
}

function cerrarModalSiFondo(e) {
    if (e.target === document.getElementById('modalPago')) cerrarModal();
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') cerrarModal();
});

/* ── Validación en tiempo real ───────────────────── */
function validarMonto(input) {
    const hint = document.getElementById('modal-monto-hint');
    const val  = parseFloat(input.value);
    const btn  = document.getElementById('btn-confirmar');

    if (isNaN(val) || val <= 0) {
        hint.textContent = 'Ingresá un monto mayor a cero.';
        hint.className   = 'hint error';
        btn.disabled = true;
    } else if (val > modalData.saldoPendiente) {
        hint.textContent = 'El monto no puede superar el saldo pendiente (' + formatPesos(modalData.saldoPendiente) + ').';
        hint.className   = 'hint error';
        btn.disabled = true;
    } else {
        hint.textContent = '';
        hint.className   = 'hint';
        btn.disabled = false;
    }
}

/* ── Envío AJAX ──────────────────────────────────── */
function enviarPago() {
    const monto = parseFloat(document.getElementById('modal-monto').value);
    if (!monto || monto <= 0 || monto > modalData.saldoPendiente) return;

    const btn     = document.getElementById('btn-confirmar');
    const spinner = document.getElementById('spinner-pago');
    btn.disabled          = true;
    spinner.style.display = 'inline-block';

    const fd = new FormData();
    fd.append('encargo_id', modalData.encargoId);
    fd.append('monto',      monto);
    const metodoPago = document.querySelector('input[name="metodo_pago"]:checked').value;
    fd.append('metodo_pago', metodoPago);

    fetch('index.php?page=pagos&accion=registrar', {
        method:  'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body:    fd
    })
    .then(r => r.json())
    .then(data => {
        cerrarModal();
        mostrarToast(data.mensaje, data.ok ? 'ok' : 'error');
       if (data.ok) {
    const filtro = document.getElementById('filtro-cliente').value;
    console.log('filtro guardado:', filtro);
    console.log('url destino:', 'index.php?page=pagos&filtro=' + encodeURIComponent(filtro));
    setTimeout(() => {
        location.href = 'index.php?page=pagos&filtro=' + encodeURIComponent(filtro);
    }, 1200);
}else {
            btn.disabled          = false;
            spinner.style.display = 'none';
        }
    })
    .catch(() => {
        cerrarModal();
        mostrarToast('Error de conexión. Intentá de nuevo.', 'error');
        btn.disabled          = false;
        spinner.style.display = 'none';
    });
}

/* ── Toasts ──────────────────────────────────────── */
function mostrarToast(msg, tipo) {
    if (tipo !== 'error') {
        mostrarToastCampana(msg);
    } else {
        const t = document.getElementById('toast');
        if (t) {
            t.textContent = msg;
            t.className = 'toast toast-error';
            void t.offsetWidth;
            t.classList.add('show');
            setTimeout(() => t.classList.remove('show'), 3200);
        }
    }
}

function mostrarToastCampana(msg) {
    const toast = document.getElementById('toast-campana');
    const msgEl = document.getElementById('toast-campana-msg');
    if (!toast || !msgEl) return;

    msgEl.textContent = msg;
    toast.style.display = 'flex';
    void toast.offsetWidth;
    toast.classList.add('visible');

    setTimeout(() => {
        toast.classList.remove('visible');
        setTimeout(() => toast.style.display = 'none', 300);
    }, 3500);
}

/* ── Helper formato ──────────────────────────────── */
function formatPesos(n) {
    return '$' + Number(n).toLocaleString('es-AR');
}

/* ── Calendario filtro ───────────────────────────── */
function toggleCalendarioPago() {
    const picker = document.getElementById('pago-date-picker');
    picker.classList.toggle('visible');
}

document.addEventListener('click', function(e) {
    const picker = document.getElementById('pago-date-picker');
    const btn    = document.getElementById('pago-cal-btn');
    if (picker && !picker.contains(e.target) && e.target !== btn && !btn.contains(e.target)) {
        picker.classList.remove('visible');
    }
});

/* ── Filtros ─────────────────────────────────────── */
function limpiarFiltros() {
    document.getElementById('filtro-cliente').value = '';
    sessionStorage.removeItem('pagos_filtro');
    document.getElementById('filtro-desde').value   = '';
    document.getElementById('filtro-hasta').value   = '';
    document.getElementById('pago-limpiar-btn').style.display = 'none';
    document.getElementById('pago-date-picker').classList.remove('visible');

    // Desactivar filtro sin retirar si está activo
    const btnSinRetirar = document.getElementById('btn-sin-retirar');
    if (btnSinRetirar && btnSinRetirar.classList.contains('active')) {
        btnSinRetirar.classList.remove('active');
        document.querySelectorAll('#tab-cuentas .historial-item').forEach(card => {
            card.style.display = 'flex';
        });
        const pagVieja = document.getElementById('pag-tab-cuentas');
        if (pagVieja) pagVieja.remove();
        setTimeout(() => iniciarPaginacion('tab-cuentas'), 50);
    }

    filtrarHistorial();
}

function filtrarHistorial() {
    sessionStorage.setItem('pagos_filtro', document.getElementById('filtro-cliente').value);

    const inputCliente = document.getElementById('filtro-cliente');
    const inputDesde   = document.getElementById('filtro-desde');
    const inputHasta   = document.getElementById('filtro-hasta');

    if (!inputCliente) return;

    const textoBusqueda = inputCliente.value.toLowerCase();
    const desde = inputDesde ? inputDesde.value : '';
    const hasta  = inputHasta ? inputHasta.value : '';
    const sinRetirarActivo = document.getElementById('btn-sin-retirar')?.classList.contains('active');

    const limpiarBtn = document.getElementById('pago-limpiar-btn');
    if (limpiarBtn) {
        limpiarBtn.style.display = (textoBusqueda || desde || hasta || sinRetirarActivo) ? 'inline-flex' : 'none';
    }

    const items = document.querySelectorAll('.historial-item');
    if (items.length === 0) return;

    items.forEach(card => {
        const cliente = card.dataset.cliente || '';
        const tipo    = card.dataset.tipo    || '';
        const fecha   = card.dataset.fecha   || '';
        const tieneBadge = card.querySelector('.badge-sin-retirar');

        const coincideTexto = cliente.includes(textoBusqueda) || tipo.includes(textoBusqueda);

        let coincideFecha = true;
        if (desde && fecha < desde) coincideFecha = false;
        if (hasta && fecha > hasta) coincideFecha = false;

        // Si filtro sin retirar activo, solo mostrar los que tienen badge
        const coincideSinRetirar = !sinRetirarActivo || tieneBadge;

        card.style.display = coincideTexto && coincideFecha && coincideSinRetirar ? 'flex' : 'none';
    });
}

/* ── Paginación ──────────────────────────────────── */
const ITEMS_POR_PAGINA = 5;

function iniciarPaginacion(tabId) {
    const tab = document.getElementById(tabId);
    if (!tab) return;

    const items = tab.querySelectorAll('.encargo-card');
    const total = items.length;

    if (total <= ITEMS_POR_PAGINA) return;

    let paginaActual = 1;
    const totalPaginas = Math.ceil(total / ITEMS_POR_PAGINA);

    const paginacion = document.createElement('div');
    paginacion.className = 'enc-paginacion';
    paginacion.id = 'pag-' + tabId;
    tab.appendChild(paginacion);

    function mostrarPagina(pagina) {
        paginaActual = pagina;
        items.forEach((item, i) => {
            const desde = (pagina - 1) * ITEMS_POR_PAGINA;
            const hasta = desde + ITEMS_POR_PAGINA;
            item.style.display = (i >= desde && i < hasta) ? 'flex' : 'none';
        });
        paginacion.innerHTML = `
            <button class="enc-pag-btn" onclick="cambiarPagina('${tabId}', ${pagina - 1})"
                ${pagina === 1 ? 'disabled' : ''}>‹</button>
            <span class="enc-pag-info">Página ${pagina} de ${totalPaginas}</span>
            <button class="enc-pag-btn" onclick="cambiarPagina('${tabId}', ${pagina + 1})"
                ${pagina === totalPaginas ? 'disabled' : ''}>›</button>
        `;
    }

    mostrarPagina(1);
}

function cambiarPagina(tabId, pagina) {
    const tab = document.getElementById(tabId);
    const items = tab.querySelectorAll('.encargo-card');
    const totalPaginas = Math.ceil(items.length / ITEMS_POR_PAGINA);

    if (pagina < 1 || pagina > totalPaginas) return;

    items.forEach((item, i) => {
        const desde = (pagina - 1) * ITEMS_POR_PAGINA;
        const hasta = desde + ITEMS_POR_PAGINA;
        item.style.display = (i >= desde && i < hasta) ? 'flex' : 'none';
    });

    const paginacion = document.getElementById('pag-' + tabId);
    paginacion.innerHTML = `
        <button class="enc-pag-btn" onclick="cambiarPagina('${tabId}', ${pagina - 1})"
            ${pagina === 1 ? 'disabled' : ''}>‹</button>
        <span class="enc-pag-info">Página ${pagina} de ${totalPaginas}</span>
        <button class="enc-pag-btn" onclick="cambiarPagina('${tabId}', ${pagina + 1})"
            ${pagina === totalPaginas ? 'disabled' : ''}>›</button>
    `;
}

/* ── Init ────────────────────────────────────────── */
document.addEventListener('DOMContentLoaded', () => {
    const params = new URLSearchParams(window.location.search);
    const filtroUrl = params.get('filtro');
    if (filtroUrl) {
        document.getElementById('filtro-cliente').value = filtroUrl;
    }
    setTimeout(() => {
        iniciarPaginacion('tab-cuentas');
        iniciarPaginacion('tab-historial');
        if (filtroUrl) filtrarHistorial();
    }, 150);
});
/* ── Filtro sin retirar ──────────────────────────── */
function toggleSinRetirar(btn) {
    btn.classList.toggle('active');

    // Asegurarse de estar en el tab de cuentas
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.getElementById('tab-cuentas').classList.add('active');
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelector('.tab-btn[onclick*="cuentas"]').classList.add('active');

    // Filtrar primero
    filtrarHistorial();

    // Re-iniciar paginación respetando el display actual
    const pagVieja = document.getElementById('pag-tab-cuentas');
    if (pagVieja) pagVieja.remove();

    setTimeout(() => {
        const tab = document.getElementById('tab-cuentas');
        const itemsVisibles = Array.from(tab.querySelectorAll('.encargo-card'))
            .filter(card => card.style.display !== 'none');

        if (itemsVisibles.length <= ITEMS_POR_PAGINA) return;

        const totalPaginas = Math.ceil(itemsVisibles.length / ITEMS_POR_PAGINA);
        const paginacion = document.createElement('div');
        paginacion.className = 'enc-paginacion';
        paginacion.id = 'pag-tab-cuentas';
        tab.appendChild(paginacion);

        function mostrarPaginaFiltrada(pagina) {
            itemsVisibles.forEach((item, i) => {
                const desde = (pagina - 1) * ITEMS_POR_PAGINA;
                const hasta = desde + ITEMS_POR_PAGINA;
                item.style.display = (i >= desde && i < hasta) ? 'flex' : 'none';
            });
            paginacion.innerHTML = `
                <button class="enc-pag-btn" onclick="cambiarPagina('tab-cuentas', ${pagina - 1})"
                    ${pagina === 1 ? 'disabled' : ''}>‹</button>
                <span class="enc-pag-info">Página ${pagina} de ${totalPaginas}</span>
                <button class="enc-pag-btn" onclick="cambiarPagina('tab-cuentas', ${pagina + 1})"
                    ${pagina === totalPaginas ? 'disabled' : ''}>›</button>
            `;
        }

        mostrarPaginaFiltrada(1);
    }, 50);
}