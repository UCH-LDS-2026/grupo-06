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
