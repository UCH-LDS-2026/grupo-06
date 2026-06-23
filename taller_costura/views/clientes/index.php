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

<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/cliente/homeCliente.css">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />

<div class="page-top">
    <div>
        <h1>Clientes</h1>
        <p>Gestión de clientes y fichas de medidas</p>
    </div>
    <a href="#" class="btn-nuevo" onclick="abrirModal()">+ Nuevo Cliente</a>
</div>

<?php if (count($sinFicha) > 0): ?>
  <div class="alerta-urgente">
    <span>
        <span class="material-symbols-outlined" style="font-size:25px; vertical-align:-4px; color:#A98B76; margin-right:6px;">warning</span>
        <strong><?= count($sinFicha) ?> clienta<?= count($sinFicha) === 1 ? '' : 's' ?> sin ficha de medidas.</strong>
    </span>
    <a href="?page=clientes&filtro=sin_ficha">Ver los clientes</a>
</div>
<?php endif; ?>

<form method="GET" action="" class="toolbar-form">
    <input type="hidden" name="page" value="clientes">
    
    <div class="toolbar">
        <div class="search-wrap">
            <span class="material-symbols-outlined search-icon">search</span>
            <input type="text" name="buscar" value="<?= htmlspecialchars($busqueda) ?>" placeholder="Buscar por nombre o teléfono...">
        </div>
        
        <div class="filtros">
            <a href="?page=clientes&filtro=todas" class="filtro-btn <?= $filtro === 'todas' ? 'activo' : '' ?>">Todas</a>
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
            
            $nombre     = trim($c->getNombre());
            $partes     = explode(' ', $nombre);
            $iniciales  = implode('', array_map(function($p) {
                return !empty($p) ? strtoupper($p[0]) : '';
            }, array_slice($partes, 0, 2)));

            $tieneFicha = $ficha !== null;
            $desde      = date('M Y', strtotime($c->getCreatedAt()));
        ?>
            <div class="cliente-card">
    <div class="card-header">
        <div class="avatar-marco">
            <div class="avatar-iniciales"><?= htmlspecialchars($iniciales) ?></div>
        </div>
        <div class="cliente-nombre"><?= htmlspecialchars($c->getNombre()) ?></div>
    </div>

    <div class="cliente-datos">
        <span class="dato"><span class="material-symbols-outlined">call</span> <?= htmlspecialchars($c->getTelefono()) ?></span>
        <span class="dato"><span class="material-symbols-outlined">mail</span> <?= htmlspecialchars($c->getEmail()) ?></span>
    </div>

   <div class="cliente-tags">
    <?php if ($tieneFicha): ?>
        <span class="tag verde">
    <span class="material-symbols-outlined">check_circle</span> Con ficha
    </span>
    <?php else: ?>
        <span class="tag naranja"><span class="material-symbols-outlined">disabled_by_default</span> Sin ficha</span>
    <?php endif; ?>
    <span class="tag"><span class="material-symbols-outlined">person</span> desde <?= $desde ?></span>
</div>
    <a href="?page=ficha-cliente&id=<?= $c->getId() ?>" class="btn-perfil">
        Ver perfil <span class="material-symbols-outlined">chevron_right</span>
    </a>
</div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="modal-overlay" id="modalNueva">
    <div class="modal">
        <button class="modal-close" onclick="cerrarModal()">✕</button>
        <div class="modal-header">
            <h2>Nuevo Cliente</h2>
            <p>Completá los datos para registrar una nueva clienta</p>
        </div>
        <form action="<?= BASE_URL ?>/index.php" method="POST">
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

<script src="<?= BASE_URL ?>/public/js/cliente/clientes.js"></script>

<?php if ($error): ?>
<script>
document.addEventListener('DOMContentLoaded', () => showToast(<?= json_encode($error) ?>, false));
</script>
<?php endif; ?>
<?php if ($exito): ?>
<script>
document.addEventListener('DOMContentLoaded', () => showToast(<?= json_encode($exito) ?>));
</script>
<?php endif; ?>