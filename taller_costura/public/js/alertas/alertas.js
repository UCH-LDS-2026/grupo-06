
function marcarLeida(id, btn) {
    fetch('index.php?page=alertas&accion=marcar&id=' + id, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            const card = document.getElementById('alerta-' + id);
            card.classList.remove('no-leida');
            card.classList.add('leida');
            const dot = card.querySelector('.dot-unread');
            if (dot) dot.remove();
            btn.remove();
            actualizarBadge(-1);
            mostrarToast('Alerta marcada como leída');
        }
    })
    .catch(() => mostrarToast('Error al actualizar', true));
}
 
function marcarTodas() {
    fetch('index.php?page=alertas&accion=marcar_todas', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            document.querySelectorAll('.alerta-card').forEach(card => {
                card.classList.remove('no-leida');
                card.classList.add('leida');
                const dot = card.querySelector('.dot-unread');
                if (dot) dot.remove();
                const btn = card.querySelector('.btn-marcar');
                if (btn) btn.remove();
            });
            actualizarBadge(0);
            mostrarToast('Todas las alertas marcadas como leídas');
        }
    })
    .catch(() => mostrarToast('Error al actualizar', true));
}
 
function mostrarToast(msg, esError = false) {
    if (!esError) {
        mostrarToastCampana(msg);
    } else {
        const t = document.getElementById('toast');
        if (!t) return;
        t.textContent = msg;
        t.className = 'toast toast-error';
        void t.offsetWidth;
        t.classList.add('show');
        setTimeout(() => t.classList.remove('show'), 3200);
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
 
function actualizarBadge(cambio) {
    const badge = document.querySelector('.floating-alerts .badge');
    if (!badge) return;
    if (cambio === 0) { badge.remove(); return; }
    const nuevo = (parseInt(badge.textContent) || 0) + cambio;
    if (nuevo <= 0) badge.remove();
    else badge.textContent = nuevo;
}
 
/* ── Paginación alertas ──────────────────────────── */
const ALERTAS_POR_PAGINA = 6;
 
function iniciarPaginacionAlertas() {
    const lista = document.getElementById('lista-alertas');
    if (!lista) return;
 
    const items = lista.querySelectorAll('.alerta-card');
    const total = items.length;
 
    if (total <= ALERTAS_POR_PAGINA) return;
 
    const totalPaginas = Math.ceil(total / ALERTAS_POR_PAGINA);
 
    const paginacion = document.createElement('div');
    paginacion.className = 'enc-paginacion';
    paginacion.id = 'pag-alertas';
    lista.after(paginacion);
 
    function mostrarPagina(pagina) {
        items.forEach((item, i) => {
            const desde = (pagina - 1) * ALERTAS_POR_PAGINA;
            const hasta = desde + ALERTAS_POR_PAGINA;
            item.style.display = (i >= desde && i < hasta) ? 'flex' : 'none';
        });
        paginacion.innerHTML = `
            <button class="enc-pag-btn" onclick="cambiarPaginaAlertas(${pagina - 1})"
                ${pagina === 1 ? 'disabled' : ''}>‹</button>
            <span class="enc-pag-info">Página ${pagina} de ${totalPaginas}</span>
            <button class="enc-pag-btn" onclick="cambiarPaginaAlertas(${pagina + 1})"
                ${pagina === totalPaginas ? 'disabled' : ''}>›</button>
        `;
    }
 
    mostrarPagina(1);
}
 
function cambiarPaginaAlertas(pagina) {
    const lista = document.getElementById('lista-alertas');
    const items = lista.querySelectorAll('.alerta-card');
    const totalPaginas = Math.ceil(items.length / ALERTAS_POR_PAGINA);
 
    if (pagina < 1 || pagina > totalPaginas) return;
 
    items.forEach((item, i) => {
        const desde = (pagina - 1) * ALERTAS_POR_PAGINA;
        const hasta = desde + ALERTAS_POR_PAGINA;
        item.style.display = (i >= desde && i < hasta) ? 'flex' : 'none';
    });
 
    const paginacion = document.getElementById('pag-alertas');
    paginacion.innerHTML = `
        <button class="enc-pag-btn" onclick="cambiarPaginaAlertas(${pagina - 1})"
            ${pagina === 1 ? 'disabled' : ''}>‹</button>
        <span class="enc-pag-info">Página ${pagina} de ${totalPaginas}</span>
        <button class="enc-pag-btn" onclick="cambiarPaginaAlertas(${pagina + 1})"
            ${pagina === totalPaginas ? 'disabled' : ''}>›</button>
    `;
}
 
document.addEventListener('DOMContentLoaded', () => {
    setTimeout(() => iniciarPaginacionAlertas(), 100);
});
 

function pollAlertas() {
    fetch('controllers/ajax_alertas.php?accion=contar')
        .then(r => r.json())
        .then(data => {
            if (!data.ok) return;
            const campana = document.querySelector('.floating-alerts');
            if (!campana) return;

            let badge = campana.querySelector('.badge');
            if (data.total > 0) {
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'badge';
                    campana.querySelector('.campana-btn').appendChild(badge);
                }
                badge.textContent = data.total;
            } else {
                if (badge) badge.remove();
            }
        })
        .catch(() => {}); // silencioso
}

// Arranca el polling cada 30 segundos
setInterval(pollAlertas, 30000);