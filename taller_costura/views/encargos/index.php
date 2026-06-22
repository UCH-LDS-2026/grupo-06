<?php
require_once __DIR__ . '/../../config/config.php';
require_once BASE_PATH . '/controllers/AlertaController.php';

$alertaController = new AlertaController();
$alertaController->verificarClientasSinFicha(1);

$encargoModel = new Encargo($db->getConnection());

$stmtClientes = $db->getConnection()->query("SELECT id, nombre FROM cliente ORDER BY nombre");
$clientesModal = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);

$errorCrear = isset($_GET['error']);

$busqueda = isset($_GET['q']) ? trim($_GET['q']) : '';

// Para estadísticas siempre se usan TODOS los encargos.
$todos = ($busqueda !== '')
    ? $encargoModel->buscar($busqueda)->fetchAll(PDO::FETCH_ASSOC)
    : $encargoModel->getAll()->fetchAll(PDO::FETCH_ASSOC);

$meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
$dias  = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];
$fechaHoy = $dias[date('w')] . ', ' . date('d') . ' de ' . $meses[date('n')-1] . ' de ' . date('Y');

if ($busqueda !== '') {
    // Si hay búsqueda, se mantiene el comportamiento de mostrar coincidencias filtradas en PHP.
    $activos    = array_filter($todos, fn($e) => in_array($e['estado'], ['pendiente','en_proceso','listo']));
    $entregados = array_filter($todos, fn($e) => $e['estado'] === 'entregado');
} else {
    // Sin búsqueda: solo los últimos 5 activos y los últimos 2 entregados, ya limitados por SQL.
    $activos    = $encargoModel->getUltimosActivos(5)->fetchAll(PDO::FETCH_ASSOC);
    $entregados = $encargoModel->getUltimosEntregados(2)->fetchAll(PDO::FETCH_ASSOC);
}

$estadisticas = [
    'activos'    => count(array_filter($todos, fn($e) => in_array($e['estado'], ['pendiente','en_proceso','listo']))),
    'en_proceso' => count(array_filter($todos, fn($e) => $e['estado'] === 'en_proceso')),
    'listos'     => count(array_filter($todos, fn($e) => $e['estado'] === 'listo')),
    'senas'      => array_sum(array_column($todos, 'sena')),
    'cobrado'    => array_sum(array_column($todos, 'monto_total')),
    'pendiente_pago' => array_sum(array_column($todos, 'monto_total')) - array_sum(array_column($todos, 'sena')),
];

function fmtMonto($n) { return '$' . number_format($n, 0, ',', '.'); }
function estadoBadge($estado) {
    $map = [
        'pendiente'  => ['label'=>'Pendiente',  'class'=>'badge-pendiente'],
        'en_proceso' => ['label'=>'En Proceso', 'class'=>'badge-proceso'],
        'listo'      => ['label'=>'Listo',      'class'=>'badge-listo'],
        'entregado'  => ['label'=>'Entregado',  'class'=>'badge-entregado'],
    ];
    $d = $map[$estado] ?? ['label'=>ucfirst($estado),'class'=>'badge-pendiente'];
    return "<span class=\"badge {$d['class']}\">{$d['label']}</span>";
}
?>

<div class="page-top">
    <div>
        <h1>Agenda de Encargos</h1>
        <p>Hoy es <?= $fechaHoy ?></p>
    </div>
    <a href="#" class="btn-nuevo" onclick="abrirModalEncargo(); return false;">+ Nuevo Encargo</a>
</div>

<div class="stats-grid">
  <div class="stat-card stat-card--activos">
    <div class="stat-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
    </div>
    <div class="stat-text">
      <span class="stat-val"><?= $estadisticas['activos'] ?></span>
      <p class="stat-lbl">Encargos Activos</p>
    </div>
  </div>
  <div class="stat-card stat-card--proceso">
    <div class="stat-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
    </div>
    <div class="stat-text">
      <span class="stat-val"><?= $estadisticas['en_proceso'] ?></span>
      <p class="stat-lbl">En Proceso</p>
    </div>
  </div>
  <div class="stat-card stat-card--proceso2">
    <div class="stat-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 1l4 4-4 4"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><path d="M7 23l-4-4 4-4"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
    </div>
    <div class="stat-text">
      <span class="stat-val"><?= $estadisticas['en_proceso'] ?></span>
      <p class="stat-lbl">En Proceso</p>
    </div>
  </div>
  <div class="stat-card stat-card--listos">
    <div class="stat-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/></svg>
    </div>
    <div class="stat-text">
      <span class="stat-val"><?= $estadisticas['listos'] ?></span>
      <p class="stat-lbl">Listos</p>
    </div>
  </div>
</div>

<div class="ag-buscador-bar">
  <form method="GET" action="index.php" class="toolbar-form">
    <input type="hidden" name="page" value="agenda">
    <div class="toolbar">
      <div class="search-wrap">
        <span class="material-symbols-outlined search-icon">search</span>
        <input type="text" name="q" placeholder="Buscar encargo, cliente o teléfono..." value="<?= htmlspecialchars($busqueda) ?>">
      </div>
      <?php if ($busqueda !== ''): ?>
        <a href="index.php?page=agenda" class="filtro-btn">✕ Limpiar</a>
      <?php endif; ?>
    </div>
  </form>
</div>

<h2 class="section-title">Próximas Entregas</h2>

<?php if (!empty($activos)): ?>
  <?php foreach ($activos as $enc):
    $fecha = new DateTime($enc['fecha_entrega']);
    $hoy   = new DateTime('today');
    $diff  = (int)$hoy->diff($fecha)->format('%r%a');
    $dia   = $fecha->format('d');
    $mes   = strtoupper(substr($meses[(int)$fecha->format('n')-1], 0, 3));
    $saldo = $enc['monto_total'] - $enc['sena'];
  ?>
  <a href="index.php?page=detalle-encargo&id=<?= $enc['id'] ?>" class="card-encargo">
    <div class="card-fecha">
      <span class="dia"><?= $dia ?></span>
      <span class="mes"><?= $mes ?></span>
      <?php if ($diff < 0): ?><span class="atrasado">Atrasado <?= abs($diff) ?>d</span>
      <?php elseif ($diff === 0): ?><span class="hoy">¡Hoy!</span><?php endif; ?>
    </div>
    <div class="card-info">
      <h3><?= htmlspecialchars($enc['tipo']) ?></h3>
      <p class="cliente"><?= htmlspecialchars($enc['cliente_nombre'] ?? 'Sin cliente') ?></p>
      <?php if (!empty($enc['descripcion'])): ?>
        <p class="desc"><?= htmlspecialchars($enc['descripcion']) ?></p>
      <?php endif; ?>
      <?php if (!empty($enc['observaciones_encargo'])): ?>
        <div class="card-obs">ℹ️ <?= htmlspecialchars($enc['observaciones_encargo']) ?></div>
      <?php endif; ?>
      <p class="card-montos">
        Total: <strong><?= fmtMonto($enc['monto_total']) ?></strong>
        &nbsp;·&nbsp; Seña: <strong><?= fmtMonto($enc['sena']) ?></strong>
        &nbsp;·&nbsp; Pendiente: <span class="pend"><?= fmtMonto($saldo) ?></span>
      </p>
    </div>
    <div><?= estadoBadge($enc['estado']) ?></div>
  </a>
  <?php endforeach; ?>
<?php else: ?>
  <div class="empty-state"> No hay encargos activos.</div>
<?php endif; ?>

<?php if (!empty($entregados)): ?>
  <h2 class="section-title section-title-alt">Últimos Entregados</h2>
  <?php foreach ($entregados as $enc):
    $fecha = new DateTime($enc['fecha_entrega']);
    $dia   = $fecha->format('d');
    $mes   = strtoupper(substr($meses[(int)$fecha->format('n')-1], 0, 3));
  ?>
  <a href="index.php?page=detalle-encargo&id=<?= $enc['id'] ?>" class="card-encargo card-entregado card-bloqueada">
    <div class="card-fecha">
      <span class="dia"><?= $dia ?></span>
      <span class="mes"><?= $mes ?></span>
    </div>
    <div class="card-info">
      <h3><?= htmlspecialchars($enc['tipo']) ?></h3>
      <p class="cliente"><?= htmlspecialchars($enc['cliente_nombre'] ?? 'Sin cliente') ?></p>
    </div>
    <div><?= estadoBadge($enc['estado']) ?></div>
  </a>
  <?php endforeach; ?>
<?php endif; ?>

<?php if (isset($_GET['nuevo'])): ?>
<div id="toast" class="toast show">✅ Encargo creado correctamente</div>
<script>setTimeout(()=>document.getElementById('toast').classList.remove('show'), 3000);</script>
<?php endif; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/cliente/homeCliente.css">

<div class="modal-overlay" id="modalEncargo">
    <div class="modal modal-encargo">
        <button class="modal-close" type="button" onclick="cerrarModalEncargo()">✕</button>
        <div class="modal-header">
            <h2>Nuevo Encargo</h2>
            <p>Completá los datos para registrar un nuevo encargo</p>
        </div>

        <?php if ($errorCrear): ?>
          <div class="alerta alerta-err" style="margin-bottom:16px;">
            Completá los campos obligatorios: Tipo de prenda, Fecha de entrega y Seña inicial (mayor a $0).
          </div>
        <?php endif; ?>

        <form method="POST" action="index.php?page=crear">
          <div class="modal-encargo-grid">

            <div class="modal-encargo-col">
                <div class="seccion-label" style="display:flex; justify-content:space-between; align-items:center;">
                    Cliente
                    <a href="index.php?page=clientes" target="_blank" title="Agregar nuevo cliente"
                       style="text-transform:none; letter-spacing:0; font-weight:700; font-size:1rem; color:var(--acento-2); text-decoration:none; line-height:1;">+</a>
                </div>
                <div class="form-group">
                    <div class="cliente-autocomplete" style="position:relative;">
                        <input type="text" id="clienteBusqueda" autocomplete="off"
                               placeholder="Escribí para buscar un cliente..." value="">
                        <input type="hidden" name="cliente_id" id="cliente_id" value="">
                        <div id="clienteLista" class="cliente-lista"
                             style="display:none; position:absolute; top:100%; left:0; right:0; background:#fff; border:1px solid var(--borde); border-radius:var(--r-md); max-height:200px; overflow-y:auto; z-index:20; box-shadow:0 4px 12px rgba(0,0,0,0.08); margin-top:4px;"></div>
                    </div>
                </div>

                <hr class="divider">

                <div class="seccion-label">Detalles del Encargo</div>
                <div class="form-group">
                    <label>Tipo de Prenda <span class="req">*</span></label>
                    <input type="text" name="tipo" required placeholder="Ej: Vestido de fiesta, Pantalón, Camisa...">
                </div>
                <div class="form-group">
                    <label>Descripción</label>
                    <input type="text" name="descripcion" placeholder="Descripción detallada del encargo...">
                </div>
                <div class="form-group">
                    <label>Observaciones Especiales</label>
                    <input type="text" name="observaciones_encargo" placeholder="Detalles importantes, preferencias del cliente...">
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Fecha de Entrega <span class="req">*</span></label>
                    <input type="date" name="fecha_entrega" required>
                </div>
            </div>

            <div class="modal-encargo-col">
                <div class="seccion-label">Información de Pago</div>
                <div class="form-group">
                    <label>Precio Total</label>
                    <div class="input-cm" style="text-align:left;">
                        <input type="number" name="monto_total" placeholder="0" min="0" step="0.01" style="padding-left:24px;">
                        <span style="left:12px; right:auto;">$</span>
                    </div>
                </div>
                <div class="form-group">
                    <label>Seña Inicial <span class="req">*</span></label>
                    <div class="input-cm" style="text-align:left;">
                        <input type="number" name="sena" required placeholder="0" min="0.01" step="0.01" style="padding-left:24px;">
                        <span style="left:12px; right:auto;">$</span>
                    </div>
                </div>
                <div class="form-group" style="margin-bottom:0;">
                    <label>Método de Pago</label>
                    <select name="metodo_pago" style="width:100%; padding:0.62rem 0.95rem; border:1px solid var(--borde); border-radius:var(--r-md); background:var(--bg-input); font-family:var(--sans); font-size:0.86rem; color:var(--texto-pri);">
                        <option value="efectivo">Efectivo</option>
                        <option value="transferencia">Transferencia</option>
                        <option value="tarjeta">Tarjeta</option>
                    </select>
                </div>
            </div>

          </div>

            <div class="modal-footer">
                <button type="button" class="btn-cancelar" onclick="cerrarModalEncargo()">Cancelar</button>
                <button type="submit" class="btn-guardar">+ Guardar Encargo</button>
            </div>
        </form>
    </div>
</div>

<script>
const CLIENTES_MODAL = <?= json_encode($clientesModal, JSON_UNESCAPED_UNICODE) ?>;

function abrirModalEncargo() {
    document.getElementById('modalEncargo').classList.add('visible');
    document.body.style.overflow = 'hidden';
}
function cerrarModalEncargo() {
    document.getElementById('modalEncargo').classList.remove('visible');
    document.body.style.overflow = '';
}
document.addEventListener('keydown', e => { if (e.key === 'Escape') cerrarModalEncargo(); });
document.getElementById('modalEncargo').addEventListener('click', function(e) {
    if (e.target === this) cerrarModalEncargo();
});

<?php if ($errorCrear): ?>
document.addEventListener('DOMContentLoaded', abrirModalEncargo);
<?php endif; ?>

const inputBusquedaEnc = document.getElementById('clienteBusqueda');
const inputHiddenEnc   = document.getElementById('cliente_id');
const listaElEnc       = document.getElementById('clienteLista');

function renderListaClientesEnc(filtro) {
    const texto = filtro.trim().toLowerCase();
    const filtrados = texto === '' ? CLIENTES_MODAL : CLIENTES_MODAL.filter(c => c.nombre.toLowerCase().includes(texto));
    let html = '<div class="cliente-opcion vacia" data-id="">Sin cliente...</div>';
    html += filtrados.length
        ? filtrados.map(c => `<div class="cliente-opcion" data-id="${c.id}" data-nombre="${c.nombre.replace(/"/g,'&quot;')}">${c.nombre}</div>`).join('')
        : '<div class="cliente-opcion vacia">Sin resultados</div>';
    listaElEnc.innerHTML = html;
    listaElEnc.style.display = 'block';
}

inputBusquedaEnc.addEventListener('input', () => {
    inputHiddenEnc.value = '';
    renderListaClientesEnc(inputBusquedaEnc.value);
});
inputBusquedaEnc.addEventListener('focus', () => renderListaClientesEnc(inputBusquedaEnc.value));

listaElEnc.addEventListener('click', (e) => {
    const opcion = e.target.closest('.cliente-opcion');
    if (!opcion) return;
    if (opcion.dataset.id) {
        inputHiddenEnc.value = opcion.dataset.id;
        inputBusquedaEnc.value = opcion.dataset.nombre;
    } else {
        inputHiddenEnc.value = '';
        inputBusquedaEnc.value = '';
    }
    listaElEnc.style.display = 'none';
});

document.addEventListener('click', (e) => {
    if (!e.target.closest('.cliente-autocomplete')) listaElEnc.style.display = 'none';
});
</script>

<style>
  .cliente-opcion { padding: 10px 12px; cursor: pointer; font-size: 0.92rem; }
  .cliente-opcion:hover { background: var(--bg-hover); }
  .cliente-opcion.vacia { color: var(--texto-ter); cursor: default; }
</style>