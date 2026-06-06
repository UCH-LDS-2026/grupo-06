<?php
require_once BASE_PATH . '/models/Cliente.php';
require_once BASE_PATH . '/models/FichaCliente.php';
 
$clientes = Cliente::getAll();
 
$conFicha = [];
$sinFicha = [];
foreach ($clientes as $c) {
    $ficha = FichaCliente::getByClienteId($c->getId());
    if ($ficha !== null) $conFicha[] = $c;
    else $sinFicha[] = $c;
}
 
$filtro   = $_GET['filtro'] ?? 'todas';
$busqueda = trim($_GET['buscar'] ?? '');
 
if ($busqueda !== '') {
    $clientes = Cliente::buscar($busqueda);
} elseif ($filtro === 'con_ficha') {
    $clientes = $conFicha;
} elseif ($filtro === 'sin_ficha') {
    $clientes = $sinFicha;
}
 
$exito = $_SESSION['exito_cliente'] ?? null;
$error = $_SESSION['error_cliente'] ?? null;
unset($_SESSION['exito_cliente'], $_SESSION['error_cliente']);
?>
 
<style>
    :root {
        --crema:        #FAF8F5;
        --blanco:       #FFFFFF;
        --cafe:         #2C1810;
        --cafe-mid:     #5C4A3A;
        --cafe-light:   #8B7355;
        --marron:       #7D4E2F;
        --borde:        #EDE8E0;
        --alerta-bg:    #FEF9EE;
        --alerta-borde: #E8D5A3;
    }
 
    .page-top {
        display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 28px;
    }
    .page-top h1 { font-family: 'Playfair Display', serif; font-size: 2rem; font-weight: 400; line-height: 1.1; }
    .page-top p  { font-size: .85rem; color: var(--cafe-light); margin-top: 4px; }
 
    .btn-nuevo {
        display: flex; align-items: center; gap: 6px;
        background: var(--marron); color: #fff; border: none;
        padding: .65rem 1.3rem; border-radius: 8px;
        font-size: .88rem; font-weight: 500; cursor: pointer;
        text-decoration: none; transition: opacity .2s; white-space: nowrap;
    }
    .btn-nuevo:hover { opacity: .88; }
 
    .alerta-ficha {
        background: var(--alerta-bg); border: 1px solid var(--alerta-borde);
        border-radius: 8px; padding: .8rem 1.1rem; font-size: .85rem;
        color: #7A5C1E; margin-bottom: 20px; display: flex; align-items: center; gap: 8px;
    }
    .alerta-ficha a { color: var(--marron); font-weight: 500; text-decoration: underline; }
 
    .alerta { padding: .75rem 1rem; border-radius: 8px; font-size: .85rem; margin-bottom: 20px; }
    .alerta-ok  { background: #f0faf4; border: 1px solid #b6dfc4; color: #2e6b45; }
    .alerta-err { background: #fdf1f1; border: 1px solid #e8c4c4; color: #b94040; }
 
    .toolbar { display: flex; align-items: center; gap: 12px; margin-bottom: 24px; flex-wrap: wrap; }
 
    .search-wrap { position: relative; flex: 1; min-width: 200px; }
    .search-wrap input {
        width: 100%; padding: .6rem 1rem .6rem 2.4rem;
        border: 1px solid var(--borde); border-radius: 8px;
        background: var(--blanco); font-size: .88rem; color: var(--cafe);
        outline: none; transition: border-color .2s; font-family: 'Inter', sans-serif;
    }
    .search-wrap input:focus { border-color: var(--marron); }
    .search-wrap input::placeholder { color: var(--cafe-light); }
    .search-icon { position: absolute; left: .8rem; top: 50%; transform: translateY(-50%); color: var(--cafe-light); pointer-events: none; }
 
    .filtros { display: flex; gap: 4px; background: var(--blanco); border: 1px solid var(--borde); border-radius: 8px; padding: 4px; }
    .filtro-btn {
        padding: .4rem .9rem; border-radius: 6px; border: none;
        background: transparent; font-size: .83rem; color: var(--cafe-mid);
        cursor: pointer; transition: all .2s; text-decoration: none; white-space: nowrap;
        font-family: 'Inter', sans-serif;
    }
    .filtro-btn:hover { background: var(--crema); }
    .filtro-btn.activo { background: var(--cafe); color: #fff; }
    .filtro-badge {
        display: inline-flex; align-items: center; justify-content: center;
        background: var(--marron); color: #fff; font-size: .7rem;
        width: 18px; height: 18px; border-radius: 50%; margin-left: 4px;
    }
    .filtro-btn.activo .filtro-badge { background: rgba(255,255,255,.3); }
 
    .clientes-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 16px; }
 
    .cliente-card {
        background: var(--blanco); border: 1px solid var(--borde);
        border-radius: 12px; padding: 20px; transition: box-shadow .2s, transform .2s;
    }
    .cliente-card:hover { box-shadow: 0 4px 20px rgba(44,24,16,.08); transform: translateY(-1px); }
 
    .cliente-card-top { display: flex; align-items: center; gap: 12px; margin-bottom: 14px; }
    .cliente-avatar {
        width: 42px; height: 42px; border-radius: 50%; background: var(--marron); color: #fff;
        display: flex; align-items: center; justify-content: center;
        font-family: 'Playfair Display', serif; font-size: 15px; font-weight: 500; flex-shrink: 0;
    }
    .cliente-nombre { font-family: 'Playfair Display', serif; font-size: 1.05rem; font-weight: 500; color: var(--cafe); }
    .cliente-datos { display: flex; flex-direction: column; gap: 4px; margin-bottom: 14px; }
    .cliente-dato { display: flex; align-items: center; gap: 6px; font-size: .82rem; color: var(--cafe-mid); }
    .cliente-tags { display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 16px; }
    .tag { font-size: .75rem; padding: .25rem .7rem; border-radius: 20px; background: var(--crema); color: var(--cafe-mid); border: 1px solid var(--borde); }
    .tag.verde   { background: #f0faf4; color: #2e6b45; border-color: #b6dfc4; }
    .tag.naranja { background: var(--alerta-bg); color: #7A5C1E; border-color: var(--alerta-borde); }
 
    .btn-perfil {
        display: flex; align-items: center; justify-content: space-between;
        width: 100%; padding: .6rem 1rem; background: var(--crema);
        border: 1px solid var(--borde); border-radius: 8px; font-size: .85rem;
        color: var(--cafe-mid); text-decoration: none; transition: all .2s;
        font-family: 'Inter', sans-serif;
    }
    .btn-perfil:hover { background: var(--marron); color: #fff; border-color: var(--marron); }
 
    .empty { text-align: center; padding: 4rem 2rem; color: var(--cafe-light); }
    .empty-icon { font-size: 2.5rem; margin-bottom: 1rem; }
 
    /* MODAL */
    .modal-overlay {
        display: none; position: fixed; inset: 0; background: rgba(44,24,16,.4);
        z-index: 100; align-items: flex-start; justify-content: center;
        padding: 40px 20px; overflow-y: auto;
    }
    .modal-overlay.visible { display: flex; }
    .modal { background: var(--blanco); border-radius: 12px; width: 100%; max-width: 640px; padding: 32px; position: relative; margin: auto; }
    .modal-header { margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid var(--borde); }
    .modal-header h2 { font-family: 'Playfair Display', serif; font-size: 1.5rem; font-weight: 400; }
    .modal-header p { font-size: .85rem; color: var(--cafe-light); margin-top: 4px; }
    .modal-close { position: absolute; top: 20px; right: 20px; background: none; border: none; font-size: 1.1rem; color: var(--cafe-light); cursor: pointer; width: 32px; height: 32px; display: flex; align-items: center; justify-content: center; border-radius: 6px; transition: background .2s; }
    .modal-close:hover { background: var(--crema); }
    .seccion-label { font-size: .72rem; font-weight: 600; letter-spacing: .1em; text-transform: uppercase; color: var(--cafe-light); margin-bottom: 14px; }
    .form-group { margin-bottom: 16px; }
    .form-group label { display: block; font-size: .83rem; font-weight: 500; color: var(--cafe-mid); margin-bottom: 6px; }
    .form-group label .req { color: var(--marron); }
    .form-group input { width: 100%; padding: .6rem .9rem; border: 1px solid var(--borde); border-radius: 8px; background: var(--crema); font-size: .9rem; color: var(--cafe); outline: none; transition: border-color .2s; font-family: 'Inter', sans-serif; }
    .form-group input:focus { border-color: var(--marron); background: #fff; }
    .form-group input::placeholder { color: #bbb; }
    .form-row   { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .form-row-3 { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; }
    .input-cm { position: relative; }
    .input-cm input { padding-right: 2.5rem; }
    .input-cm span { position: absolute; right: .9rem; top: 50%; transform: translateY(-50%); font-size: .8rem; color: var(--cafe-light); pointer-events: none; }
    .seccion-medidas-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; }
    .opcional-tag { font-size: .75rem; color: var(--cafe-light); }
    .modal-footer { display: flex; justify-content: flex-end; gap: 10px; margin-top: 28px; padding-top: 20px; border-top: 1px solid var(--borde); }
    .btn-cancelar { padding: .65rem 1.2rem; background: transparent; border: 1px solid var(--borde); border-radius: 8px; font-size: .88rem; color: var(--cafe-mid); cursor: pointer; font-family: 'Inter', sans-serif; }
    .btn-cancelar:hover { border-color: var(--cafe-mid); }
    .btn-guardar { display: flex; align-items: center; gap: 6px; padding: .65rem 1.4rem; background: var(--marron); border: none; border-radius: 8px; font-size: .88rem; font-weight: 500; color: #fff; cursor: pointer; font-family: 'Inter', sans-serif; }
    .btn-guardar:hover { opacity: .88; }
    .divider { border: none; border-top: 1px solid var(--borde); margin: 24px 0; }
</style>
 
<?php if ($exito): ?>
    <div class="alerta alerta-ok"><?= htmlspecialchars($exito) ?></div>
<?php endif; ?>
<?php if ($error): ?>
    <div class="alerta alerta-err"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>
 
<div class="page-top">
    <div>
        <h1>Clientes</h1>
        <p>Gestión de clientas y fichas de medidas</p>
    </div>
    <a href="#" class="btn-nuevo" onclick="abrirModal()">+ Nueva Cliente</a>
</div>
 
<?php if (count($sinFicha) > 0): ?>
    <div class="alerta-ficha">
        ⚠️ <strong><?= count($sinFicha) ?> <?= count($sinFicha) === 1 ? 'clienta' : 'clientas' ?> sin ficha de medidas.</strong>
        <a href="?page=clientes&filtro=sin_ficha">Ver las clientas</a>
    </div>
<?php endif; ?>
 
<form method="GET" action="">
    <input type="hidden" name="page" value="clientes">
    <div class="toolbar">
        <div class="search-wrap">
            <span class="search-icon">🔍</span>
            <input type="text" name="buscar" value="<?= htmlspecialchars($busqueda) ?>" placeholder="Buscar por nombre o teléfono...">
        </div>
        <div class="filtros">
            <a href="?page=clientes&filtro=todas"     class="filtro-btn <?= $filtro === 'todas'     ? 'activo' : '' ?>">Todas</a>
            <a href="?page=clientes&filtro=con_ficha" class="filtro-btn <?= $filtro === 'con_ficha' ? 'activo' : '' ?>">Con ficha</a>
            <a href="?page=clientes&filtro=sin_ficha" class="filtro-btn <?= $filtro === 'sin_ficha' ? 'activo' : '' ?>">
                Sin ficha
                <?php if (count($sinFicha) > 0): ?>
                    <span class="filtro-badge"><?= count($sinFicha) ?></span>
                <?php endif; ?>
            </a>
        </div>
    </div>
</form>
 
<?php if (empty($clientes)): ?>
    <div class="empty">
        <div class="empty-icon">👤</div>
        <p>No hay clientas registradas todavía.</p>
    </div>
<?php else: ?>
    <div class="clientes-grid">
        <?php foreach ($clientes as $c):
            $ficha      = FichaCliente::getByClienteId($c->getId());
            $iniciales  = implode('', array_map(fn($p) => strtoupper($p[0]), array_slice(explode(' ', $c->getNombre()), 0, 2)));
            $tieneFicha = $ficha !== null;
            $desde      = date('M Y', strtotime($c->getCreatedAt()));
        ?>
        <div class="cliente-card">
            <div class="cliente-card-top">
                <div class="cliente-avatar"><?= htmlspecialchars($iniciales) ?></div>
                <div><div class="cliente-nombre"><?= htmlspecialchars($c->getNombre()) ?></div></div>
            </div>
            <div class="cliente-datos">
                <?php if ($c->getTelefono()): ?>
                    <span class="cliente-dato">📞 <?= htmlspecialchars($c->getTelefono()) ?></span>
                <?php endif; ?>
                <?php if ($c->getEmail()): ?>
                    <span class="cliente-dato">✉️ <?= htmlspecialchars($c->getEmail()) ?></span>
                <?php endif; ?>
            </div>
            <div class="cliente-tags">
                <?php if ($tieneFicha): ?>
                    <span class="tag verde">📐 Medidas registradas</span>
                <?php else: ?>
                    <span class="tag naranja">Sin ficha de medidas</span>
                <?php endif; ?>
                <span class="tag">👤 desde <?= $desde ?></span>
            </div>
            <a href="/grupo-06/taller_costura/index.php?page=ficha-cliente&id=<?= $c->getId() ?>" class="btn-perfil">
                Ver perfil <span>›</span>
            </a>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
 
<!-- MODAL NUEVA CLIENTE -->
<div class="modal-overlay" id="modalNueva">
    <div class="modal">
        <button class="modal-close" onclick="cerrarModal()">✕</button>
        <div class="modal-header">
            <h2>Nuevo Cliente</h2>
            <p>Completá los datos para registrar una nueva clienta</p>
        </div>
        <form action="/grupo-06/taller_costura/controllers/ClienteController.php" method="POST">
            <input type="hidden" name="accion" value="registrar">
            <div class="seccion-label">Datos Personales</div>
            <div class="form-group">
                <label>Nombre completo <span class="req">*</span></label>
                <input type="text" name="nombre" placeholder="Ej: María González" required>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Teléfono</label>
                    <input type="text" name="telefono" placeholder="11-2345-6789">
                </div>
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" placeholder="nombre@email.com">
                </div>
            </div>
            <hr class="divider">
            <div class="seccion-medidas-header">
                <div class="seccion-label" style="margin-bottom:0">Ficha de Medidas</div>
                <span class="opcional-tag">Opcional — se puede completar luego</span>
            </div>
            <div class="form-row-3" style="margin-bottom:12px">
                <div class="form-group">
                    <label>Contorno de Busto</label>
                    <div class="input-cm"><input type="number" name="contorno_pecho" placeholder="—" step="0.5" min="0"><span>cm</span></div>
                </div>
                <div class="form-group">
                    <label>Contorno de Cintura</label>
                    <div class="input-cm"><input type="number" name="contorno_cintura" placeholder="—" step="0.5" min="0"><span>cm</span></div>
                </div>
                <div class="form-group">
                    <label>Contorno de Cadera</label>
                    <div class="input-cm"><input type="number" name="contorno_cadera" placeholder="—" step="0.5" min="0"><span>cm</span></div>
                </div>
            </div>
            <div class="form-row-3">
                <div class="form-group">
                    <label>Largo de Espalda</label>
                    <div class="input-cm"><input type="number" name="largo_espalda" placeholder="—" step="0.5" min="0"><span>cm</span></div>
                </div>
                <div class="form-group">
                    <label>Largo de Manga</label>
                    <div class="input-cm"><input type="number" name="largo_manga" placeholder="—" step="0.5" min="0"><span>cm</span></div>
                </div>
                <div class="form-group">
                    <label>Largo de Pantalón</label>
                    <div class="input-cm"><input type="number" name="largo_pantalon" placeholder="—" step="0.5" min="0"><span>cm</span></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-cancelar" onclick="cerrarModal()">Cancelar</button>
                <button type="submit" class="btn-guardar">+ Registrar Cliente</button>
            </div>
        </form>
    </div>
</div>
 
<script>
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
</script>