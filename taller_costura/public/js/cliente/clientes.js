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
const inputBuscar = document.querySelector('input[name="buscar"]');
let timer;
inputBuscar?.addEventListener('input', () => {
    clearTimeout(timer);
    timer = setTimeout(() => inputBuscar.closest('form').submit(), 400);
});

// ── Toast ────────────────────────────────────────────────
function showToast(msg, ok = true) {
    let t = document.getElementById('toast-clientes');
    if (!t) {
        t = document.createElement('div');
        t.id = 'toast-clientes';
        t.style.cssText = `
            position: fixed;
            bottom: 28px;
            right: 28px;
            padding: 13px 20px;
            border-radius: 10px;
            font-family: var(--sans, sans-serif);
            font-size: .88rem;
            font-weight: 500;
            color: #fff;
            z-index: 9999;
            opacity: 0;
            transform: translateY(10px);
            transition: opacity .25s ease, transform .25s ease;
            pointer-events: none;
            max-width: 320px;
            box-shadow: 0 4px 16px rgba(0,0,0,.15);
        `;
        document.body.appendChild(t);
    }
    t.textContent = msg;
    t.style.background = ok ? '#4caf82' : '#c0544a';
    // forzar reflow para reiniciar animación
    void t.offsetWidth;
    t.style.opacity = '1';
    t.style.transform = 'translateY(0)';
    clearTimeout(t._timer);
    t._timer = setTimeout(() => {
        t.style.opacity = '0';
        t.style.transform = 'translateY(10px)';
    }, 2800);
}