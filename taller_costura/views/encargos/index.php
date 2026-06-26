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

$todos = ($busqueda !== '')
    ? $encargoModel->buscar($busqueda)->fetchAll(PDO::FETCH_ASSOC)
    : $encargoModel->getAll()->fetchAll(PDO::FETCH_ASSOC);

$meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
$dias  = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];
$fechaHoy = $dias[date('w')] . ', ' . date('d') . ' de ' . $meses[date('n')-1] . ' de ' . date('Y');

if ($busqueda !== '') {
    $activos         = array_filter($todos, fn($e) => in_array($e['estado'], ['pendiente','en_proceso','listo']));
    $todosEntregados = array_filter($todos, fn($e) => $e['estado'] === 'entregado');
} else {
    $activos         = $encargoModel->getUltimosActivos()->fetchAll(PDO::FETCH_ASSOC);
    $todosEntregados = $encargoModel->getTodosEntregados()->fetchAll(PDO::FETCH_ASSOC);
}

$estadisticas = [
    'activos'        => count(array_filter($todos, fn($e) => in_array($e['estado'], ['pendiente','en_proceso','listo']))),
    'pendiente'      => count(array_filter($todos, fn($e) => $e['estado'] === 'pendiente')),
    'en_proceso'     => count(array_filter($todos, fn($e) => $e['estado'] === 'en_proceso')),
    'listos'         => count(array_filter($todos, fn($e) => $e['estado'] === 'listo')),
    'senas'          => array_sum(array_column($todos, 'sena')),
    'cobrado'        => array_sum(array_column($todos, 'monto_total')),
    'pendiente_pago' => array_sum(array_column($todos, 'monto_total')) - array_sum(array_column($todos, 'sena')),
];

$sinCliente = count(array_filter($todos, fn($e) => empty($e['cliente_nombre'])));

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

<!-- ENCABEZADO -->
<div class="page-top">
    <div>
        <h1>Agenda de Encargos</h1>
    </div>
    <a href="#" class="btn-nuevo" onclick="abrirModalEncargo(); return false;">+ Nuevo Encargo</a>
</div>

<?php if ($sinCliente > 0): ?>
<div class="alerta-sin-cliente" onclick="filtrarSinCliente()" style="cursor:pointer;" title="Ver encargos sin cliente">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" width="16" height="16"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
  <?= $sinCliente === 1 ? 'Hay 1 encargo sin cliente asignado.' : "Hay {$sinCliente} encargos sin cliente asignado." ?>
  &nbsp;— <strong>Ver</strong>
</div>
<?php endif; ?>

<div class="stats-grid">
 
  <!-- FECHA CARD -->
  <div class="stat-card stat-card--fecha">
    <div class="fecha-card-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" width="22" height="22">
        <rect x="3" y="4" width="18" height="18" rx="2"/>
        <line x1="16" y1="2" x2="16" y2="6"/>
        <line x1="8" y1="2" x2="8" y2="6"/>
        <line x1="3" y1="10" x2="21" y2="10"/>
      </svg>
      <span>HOY</span>
    </div>
    <div class="fecha-card-dia"><?= date('d') ?></div>
    <div class="fecha-card-mes"><?= strtoupper($meses[date('n')-1]) ?> <?= date('Y') ?></div>
    <div class="fecha-card-dow"><?= ucfirst($dias[date('w')]) ?></div>
  </div>
 
  <!-- ACTIVOS -->
  <div class="stat-card stat-card--activos" onclick="filtrarPorEstadoCard(['pendiente','en_proceso','listo'])" style="cursor:pointer;">
    <div class="stat-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
    </div>
    <div class="stat-text">
      <span class="stat-val"><?= $estadisticas['activos'] ?></span>
      <p class="stat-lbl">Encargos activos</p>
      <span class="stat-sub">Para entregar</span>
    </div>
  </div>
 
  <!-- PENDIENTES -->
  <div class="stat-card stat-card--proceso" onclick="filtrarPorEstadoCard(['pendiente'])" style="cursor:pointer;">
    <div class="stat-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
    </div>
    <div class="stat-text">
      <span class="stat-val"><?= $estadisticas['pendiente'] ?></span>
      <p class="stat-lbl">Pendientes</p>
      <span class="stat-sub">Para entregar</span>
    </div>
  </div>
 
  <!-- EN PROCESO -->
  <div class="stat-card stat-card--proceso2" onclick="filtrarPorEstadoCard(['en_proceso'])" style="cursor:pointer;">
    <div class="stat-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 1l4 4-4 4"/><path d="M3 11V9a4 4 0 0 1 4-4h14"/><path d="M7 23l-4-4 4-4"/><path d="M21 13v2a4 4 0 0 1-4 4H3"/></svg>
    </div>
    <div class="stat-text">
      <span class="stat-val"><?= $estadisticas['en_proceso'] ?></span>
      <p class="stat-lbl">En proceso</p>
      <span class="stat-sub">En confección</span>
    </div>
  </div>
 
  <!-- LISTOS -->
  <div class="stat-card stat-card--listos" onclick="filtrarPorEstadoCard(['listo'])" style="cursor:pointer;">
    <div class="stat-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9 12l2 2 4-4"/></svg>
    </div>
    <div class="stat-text">
      <span class="stat-val"><?= $estadisticas['listos'] ?></span>
      <p class="stat-lbl">Listos</p>
      <span class="stat-sub">Para retirar</span>
    </div>
  </div>
 
</div>

<!-- BUSCADOR + TABS en la misma fila -->
<div class="ag-buscador-bar">
  <div class="toolbar">
    <div class="search-wrap">
      <span class="material-symbols-outlined search-icon">search</span>
      <input type="text" id="enc-q" placeholder="Buscar encargo, cliente o fecha..." oninput="filtrarEncargos()">
      <button type="button" class="search-cal-btn" id="enc-cal-btn" title="Filtrar por rango de fechas" onclick="toggleCalendarioEnc()">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
      </button>
      <div class="enc-date-picker" id="enc-date-picker">
        <label>Desde</label>
        <input type="date" id="enc-desde" onchange="filtrarEncargos()">
        <label>Hasta</label>
        <input type="date" id="enc-hasta" onchange="filtrarEncargos()">
      </div>
    </div>

    <!-- TABS -->
    <div class="enc-tabs">
      <button type="button" class="enc-tab-btn active" id="tab-btn-activos" onclick="switchTabEnc('activos')">
        Próximas Entregas
        <span class="tab-count"><?= $estadisticas['activos'] ?></span>
      </button>
      <button type="button" class="enc-tab-btn" id="tab-btn-entregados" onclick="switchTabEnc('entregados')">
        Entregados
        <span class="tab-count"><?= count($todosEntregados) ?></span>
      </button>
    </div>

    <button type="button" class="filtro-btn" id="enc-limpiar-btn" style="display:none;" onclick="limpiarFiltrosEnc()">✕ Limpiar</button>
  </div>
</div>

<!-- TAB: PRÓXIMAS ENTREGAS -->
<div class="enc-tab-panel active" id="tab-panel-activos">
  <div id="enc-cards-container">
  <?php if (!empty($activos)): ?>
    <?php foreach ($activos as $enc):
      $fecha = new DateTime($enc['fecha_entrega']);
      $hoy   = new DateTime('today');
      $diff  = (int)$hoy->diff($fecha)->format('%r%a');
      $dia   = $fecha->format('d');
      $mes   = strtoupper(substr($meses[(int)$fecha->format('n')-1], 0, 3));
      $saldo = $enc['monto_total'] - $enc['sena'];
      $esHoy = $diff === 0 ? ' card-es-hoy' : '';
    ?>
    <a href="index.php?page=detalle-encargo&id=<?= $enc['id'] ?>"
       class="card-encargo<?= $esHoy ?>"
       data-estado="<?= $enc['estado'] ?>"
       data-fecha="<?= $enc['fecha_entrega'] ?>"
       data-cliente="<?= strtolower(htmlspecialchars($enc['cliente_nombre'] ?? '')) ?>"
       data-sin-cliente="<?= empty($enc['cliente_nombre']) ? '1' : '0' ?>">
      <div class="card-fecha">
        <span class="dia"><?= $dia ?></span>
        <span class="mes"><?= $mes ?></span>
        <?php if ($diff < 0): ?>
          <span class="atrasado">Atrasado <?= abs($diff) ?>d</span>
        <?php elseif ($diff === 0): ?>
          <span class="hoy">¡Hoy!</span>
        <?php endif; ?>
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
      <div style="display:flex; flex-direction:column; align-items:flex-end; gap:6px;">
        <?= estadoBadge($enc['estado']) ?>
        <?php if (empty($enc['cliente_nombre'])): ?>
          <span class="badge-sin-cliente">⚠</span>
        <?php endif; ?>
      </div>
    </a>
    <?php endforeach; ?>
  <?php else: ?>
    <div class="empty-state">No hay encargos activos.</div>
  <?php endif; ?>
  </div>

  <div class="enc-paginacion" id="enc-paginacion">
    <button class="enc-pag-btn" id="enc-pag-prev" onclick="cambiarPaginaEnc(-1)">&#8592;</button>
    <span class="enc-pag-info" id="enc-pag-info"></span>
    <button class="enc-pag-btn" id="enc-pag-next" onclick="cambiarPaginaEnc(1)">&#8594;</button>
  </div>
</div>

<!-- TAB: ENTREGADOS -->
<div class="enc-tab-panel" id="tab-panel-entregados">
  <?php if (!empty($todosEntregados)): ?>
    <?php foreach ($todosEntregados as $enc):
      $fecha = new DateTime($enc['fecha_entrega']);
      $dia   = $fecha->format('d');
      $mes   = strtoupper(substr($meses[(int)$fecha->format('n')-1], 0, 3));
      $saldo = $enc['monto_total'] - $enc['sena'];
    ?>
    <a href="index.php?page=detalle-encargo&id=<?= $enc['id'] ?>"
       class="card-encargo card-entregado"
       data-estado="entregado"
       data-fecha="<?= $enc['fecha_entrega'] ?>"
       data-cliente="<?= strtolower(htmlspecialchars($enc['cliente_nombre'] ?? '')) ?>">
      <div class="card-fecha">
        <span class="dia"><?= $dia ?></span>
        <span class="mes"><?= $mes ?></span>
      </div>
      <div class="card-info">
        <h3><?= htmlspecialchars($enc['tipo']) ?></h3>
        <p class="cliente"><?= htmlspecialchars($enc['cliente_nombre'] ?? 'Sin cliente') ?></p>
        <?php if (!empty($enc['descripcion'])): ?>
          <p class="desc"><?= htmlspecialchars($enc['descripcion']) ?></p>
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
    <div class="empty-state">No hay encargos entregados aún.</div>
  <?php endif; ?>
</div>

<?php if (isset($_GET['nuevo'])): ?>
<div id="toast" class="toast show">✅ Encargo creado correctamente</div>
<script>setTimeout(()=>document.getElementById('toast').classList.remove('show'), 3000);</script>
<?php endif; ?>

<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/cliente/homeCliente.css">

<!-- MODAL NUEVO ENCARGO -->
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
        <div id="modal-error-encargo" style="display:none; margin-bottom:16px; padding:10px 14px; background:#fff3f3; color:#b05040; border:1px solid rgba(176,80,64,0.25); border-radius:10px; font-size:0.86rem;"></div>

        <form method="POST" action="index.php?page=crear">
          <div class="modal-encargo-grid">
            <div class="modal-encargo-col">
                <div class="seccion-label" style="display:flex; justify-content:space-between; align-items:center;">
                    Cliente
                    <a href="index.php?page=clientes" target="_blank"
                       style="text-transform:none; letter-spacing:0; font-weight:700; font-size:0.75rem; color:var(--acento-2); text-decoration:none; line-height:1;">+ Nuevo</a>
                </div>
                <div class="form-group">
                    <div class="cliente-autocomplete" style="position:relative;">
                        <input type="text" id="clienteBusqueda" autocomplete="off" placeholder="Escribí para buscar un cliente..." value="">
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
                    <input type="date" name="fecha_entrega" id="modal_fecha_entrega" min="<?= date('Y-m-d') ?>" required>
                </div>
            </div>
            <div class="modal-encargo-col">
                <div class="seccion-label">Información de Pago</div>
                <div class="form-group">
                    <label>Precio Total</label>
                    <div class="input-cm" style="text-align:left;">
                        <input type="number" name="monto_total" id="modal_monto_total" placeholder="1000" min="1000" step="1" style="padding-left:24px;">
                        <span style="left:12px; right:auto;">$</span>
                    </div>
                </div>
                <div class="form-group">
                    <label>Seña Inicial <span class="req">*</span></label>
                    <div class="input-cm" style="text-align:left;">
                        <input type="number" name="sena" id="modal_sena" required placeholder="0" min="1" step="1" style="padding-left:24px;">
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
              <button type="button" class="btn-guardar" onclick="validarYGuardarEncargo()">+ Guardar Encargo</button>
          </div>
        </form>
    </div>
</div>

<script src="<?= BASE_URL ?>/public/js/encargos/encargos.js"></script>
<script>
const CLIENTES_MODAL = <?= json_encode($clientesModal, JSON_UNESCAPED_UNICODE) ?>;

function switchTabEnc(tab) {
  document.querySelectorAll('.enc-tab-btn').forEach(b => b.classList.remove('active'));
  document.querySelectorAll('.enc-tab-panel').forEach(p => p.classList.remove('active'));
  document.getElementById('tab-btn-' + tab).classList.add('active');
  document.getElementById('tab-panel-' + tab).classList.add('active');
}

function validarYGuardarEncargo() {
  const tipo     = document.querySelector('[name="tipo"]');
  const fecha    = document.getElementById('modal_fecha_entrega');
  const total    = document.getElementById('modal_monto_total');
  const sena     = document.getElementById('modal_sena');
  const errorDiv = document.getElementById('modal-error-encargo');
  const hoy      = new Date(); hoy.setHours(0,0,0,0);
  const errores  = [];

  if (!tipo || !tipo.value.trim()) errores.push('El tipo de prenda es obligatorio.');

  if (!fecha.value) {
    errores.push('La fecha de entrega es obligatoria.');
  } else if (new Date(fecha.value + 'T00:00:00') < hoy) {
    errores.push('La fecha de entrega no puede ser anterior a hoy.');
  }

  const montoVal = parseFloat(total.value);
  const senaVal  = parseFloat(sena.value);

  if (total.value !== '' && !isNaN(montoVal) && montoVal < 1000)
    errores.push('El precio total debe ser al menos $1.000.');

  if (!sena.value || isNaN(senaVal) || senaVal < 1) {
    errores.push('La seña inicial es obligatoria y debe ser mayor a $0.');
  } else if (total.value !== '' && !isNaN(montoVal) && montoVal >= 1000 && senaVal > montoVal) {
    errores.push('La seña no puede superar el precio total.');
  } else if ((total.value === '' || isNaN(montoVal)) && senaVal <= 1000) {
    errores.push('La seña no puede ser menor o igual al precio total mínimo ($1.000). Completá el precio total primero.');
  }

  if (errores.length > 0) {
    errorDiv.innerHTML = errores.map(e => `• ${e}`).join('<br>');
    errorDiv.style.display = 'block';
    errorDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    return;
  }

  errorDiv.style.display = 'none';
  tipo.closest('form').submit();
}

document.addEventListener('DOMContentLoaded', () => {
    initClienteAutocomplete(CLIENTES_MODAL);
    <?php if ($errorCrear): ?>
    abrirModalEncargo();
    <?php endif; ?>
});
</script>
