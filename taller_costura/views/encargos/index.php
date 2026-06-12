<?php
require_once __DIR__ . '/../../config/config.php';
require_once BASE_PATH . '/controllers/AlertaController.php';

$alertaController = new AlertaController();
$alertaController->verificarClientasSinFicha(1);
$encargoModel = new Encargo($db->getConnection());
$todos = $encargoModel->getAll()->fetchAll(PDO::FETCH_ASSOC);

$meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
$dias  = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];
$fechaHoy = $dias[date('w')] . ', ' . date('d') . ' de ' . $meses[date('n')-1] . ' de ' . date('Y');

$activos    = array_filter($todos, fn($e) => in_array($e['estado'], ['pendiente','en_proceso','listo']));
$entregados = array_filter($todos, fn($e) => $e['estado'] === 'entregado');

$estadisticas = [
    'activos'    => count($activos),
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

<div class="ag-header">
  <div>
    <h1>Agenda de Encargos</h1>
    <p>Hoy es <?= $fechaHoy ?></p>
  </div>
  <a href="index.php?page=crear" class="btn-nuevo">+ Nuevo Encargo</a>
</div>

<div class="stats-grid">
  <div class="stat-card"><span class="stat-val"><?= $estadisticas['activos'] ?></span><p class="stat-lbl">Encargos Activos</p></div>
  <div class="stat-card"><span class="stat-val"><?= $estadisticas['en_proceso'] ?></span><p class="stat-lbl">En Proceso</p></div>
  <div class="stat-card"><span class="stat-val"><?= $estadisticas['listos'] ?></span><p class="stat-lbl">Listos</p></div>
</div>
<div class="stats-grid">
  <div class="stat-card"><p class="stat-lbl">Total Señas Recibidas</p><span class="stat-val stat-money"><?= fmtMonto($estadisticas['senas']) ?></span></div>
  <div class="stat-card"><p class="stat-lbl">Total Cobrado</p><span class="stat-val stat-money"><?= fmtMonto($estadisticas['cobrado']) ?></span></div>
  <div class="stat-card"><p class="stat-lbl">Saldo Pendiente</p><span class="stat-val stat-warn"><?= fmtMonto($estadisticas['pendiente_pago']) ?></span></div>
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
  <div class="empty-state">📋 No hay encargos activos.</div>
<?php endif; ?>

<?php if (!empty($entregados)): ?>
  <h2 class="section-title section-title-alt">Entregados Recientemente</h2>
  <?php foreach ($entregados as $enc):
    $fecha = new DateTime($enc['fecha_entrega']);
    $dia   = $fecha->format('d');
    $mes   = strtoupper(substr($meses[(int)$fecha->format('n')-1], 0, 3));
  ?>
  <a href="index.php?page=detalle-encargo&id=<?= $enc['id'] ?>" class="card-encargo card-entregado">
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
<script>setTimeout(()=>document.getElementById('toast').style.display='none', 3000);</script>
<?php endif; ?>