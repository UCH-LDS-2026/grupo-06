// ── Estado global ─────────────────────────────────────────
let estadoActual = {
    buscar:  '',
    filtro:  'todas',
    pagina:  1
};

// ── Modal ─────────────────────────────────────────────────
function abrirModal() {
    document.getElementById('modalNueva').classList.add('visible');
    document.body.style.overflow = 'hidden';
}
function cerrarModal() {
    document.getElementById('modalNueva').classList.remove('visible');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarModal(); });
document.getElementById('modalNueva').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
});

// ── AJAX principal ────────────────────────────────────────
function cargarClientes(params = {}) {
    Object.assign(estadoActual, params);

    const qs = new URLSearchParams({
        buscar: estadoActual.buscar,
        filtro: estadoActual.filtro,
        pagina: estadoActual.pagina,
    }).toString();

    const grid = document.getElementById('clientes-container');
    if (grid) grid.style.opacity = '0.5';

    fetch(`controllers/ajax_clientes.php?${qs}`)
        .then(r => r.json())
        .then(data => {
            if (!data.ok) return;
            if (grid) {
                grid.innerHTML = data.html;
                grid.style.opacity = '1';
            }
        })
        .catch(() => {
            if (grid) grid.style.opacity = '1';
        });
}

// ── Buscador con debounce ─────────────────────────────────
let timer;
const inputBuscar = document.querySelector('input[name="buscar"]');
inputBuscar?.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(() => {
        cargarClientes({ buscar: inputBuscar.value.trim(), pagina: 1 });
    }, 350);
});

// ── Filtros ───────────────────────────────────────────────
document.querySelectorAll('.filtro-btn').forEach(btn => {
    btn.addEventListener('click', e => {
        e.preventDefault();
        document.querySelectorAll('.filtro-btn').forEach(b => b.classList.remove('activo'));
        btn.classList.add('activo');
        const url = new URL(btn.href, window.location.href);
        const filtro = url.searchParams.get('filtro') ?? 'todas';
        cargarClientes({ filtro, pagina: 1 });
    });
});

// ── Paginación ────────────────────────────────────────────
function cambiarPagina(p) {
    cargarClientes({ pagina: p });
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

// ── Toast ─────────────────────────────────────────────────
function showToast(msg, ok = true) {
    let t = document.getElementById('toast-clientes');
    if (!t) {
        t = document.createElement('div');
        t.id = 'toast-clientes';
        t.style.cssText = `
            position: fixed; bottom: 28px; right: 28px;
            padding: 13px 20px; border-radius: 10px;
            font-family: var(--sans, sans-serif); font-size: .88rem;
            font-weight: 500; color: #fff; z-index: 9999;
            opacity: 0; transform: translateY(10px);
            transition: opacity .25s ease, transform .25s ease;
            pointer-events: none; max-width: 320px;
            box-shadow: 0 4px 16px rgba(0,0,0,.15);
        `;
        document.body.appendChild(t);
    }
    t.textContent = msg;
    t.style.background = ok ? '#4caf82' : '#c0544a';
    void t.offsetWidth;
    t.style.opacity = '1';
    t.style.transform = 'translateY(0)';
    clearTimeout(t._timer);
    t._timer = setTimeout(() => {
        t.style.opacity = '0';
        t.style.transform = 'translateY(10px)';
    }, 2800);
}