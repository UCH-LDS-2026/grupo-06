<?php
require_once __DIR__ . '/../../config/config.php';

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

<style>
  .ag-header { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:32px; }
  .ag-header h1 { font-family:'Playfair Display',serif; font-size:32px; color:#2C1810; }
  .ag-header p  { color:#8B7355; font-size:14px; margin-top:4px; }
  .btn-nuevo { background:#7D4E2F; color:#fff; padding:12px 22px; border-radius:8px; text-decoration:none;
               font-size:14px; font-weight:500; display:inline-flex; align-items:center; gap:8px; transition:background .2s; }
  .btn-nuevo:hover { background:#5C3A23; }

  .stats-grid { display:grid; grid-template-columns:repeat(3,1fr); gap:16px; margin-bottom:20px; }
  .stat-card  { background:#fff; border:1px solid #EDE8E0; border-radius:12px; padding:22px 24px; }
  .stat-val   { font-family:'Playfair Display',serif; font-size:28px; color:#2C1810; display:block; }
  .stat-lbl   { font-size:12px; color:#8B7355; margin-top:2px; }
  .stat-money { color:#7D4E2F !important; }
  .stat-warn  { color:#A67C52 !important; }

  .section-title { font-family:'Playfair Display',serif; font-size:22px; color:#2C1810; margin:32px 0 16px; }

  .card-encargo { background:#fff; border:1px solid #EDE8E0; border-radius:12px; padding:18px 20px;
                  display:flex; align-items:center; gap:20px; margin-bottom:12px;
                  text-decoration:none; color:inherit; transition:box-shadow .15s; cursor:pointer; }
  .card-encargo:hover { box-shadow:0 4px 16px rgba(0,0,0,.07); }

  .card-fecha { text-align:center; min-width:52px; padding-right:20px; border-right:1px solid #EDE8E0; }
  .card-fecha .dia { font-family:'Playfair Display',serif; font-size:24px; color:#2C1810; display:block; line-height:1; }
  .card-fecha .mes { font-size:11px; color:#8B7355; text-transform:uppercase; }
  .card-fecha .atrasado { font-size:10px; color:#C0392B; font-weight:600; display:block; margin-top:3px; }
  .card-fecha .hoy { font-size:10px; color:#E67E22; font-weight:600; display:block; margin-top:3px; }

  .card-info { flex:1; min-width:0; }
  .card-info h3 { font-size:15px; color:#2C1810; margin-bottom:2px; }
  .card-info .cliente { font-size:13px; color:#7D4E2F; margin-bottom:4px; }
  .card-info .desc { font-size:13px; color:#5C4A3A; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
  .card-obs { background:#FAF8F5; border:1px solid #EDE8E0; border-radius:6px; padding:7px 10px;
              font-size:12px; color:#5C4A3A; margin-top:8px; display:flex; align-items:flex-start; gap:6px; }
  .card-montos { font-size:12px; color:#8B7355; margin-top:6px; }
  .card-montos strong { color:#2C1810; }
  .card-montos .pend { color:#7D4E2F; font-weight:600; }

  .badge { padding:3px 11px; border-radius:20px; font-size:11px; font-weight:500; white-space:nowrap; }
  .badge-pendiente { background:#F3E5F5; color:#6A1B9A; }
  .badge-proceso   { background:#FFF8E1; color:#F57F17; }
  .badge-listo     { background:#E8F5E9; color:#2E7D32; }
  .badge-entregado { background:#E3F2FD; color:#1565C0; }

  .empty-state { background:#fff; border:1px dashed #EDE8E0; border-radius:12px; padding:40px;
                 text-align:center; color:#8B7355; }

  .toast { position:fixed; bottom:24px; right:24px; background:#2C1810; color:#FAF8F5;
           padding:12px 22px; border-radius:8px; font-size:13px; display:none; z-index:999;
           box-shadow:0 4px 12px rgba(0,0,0,.15); }
</style>

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
  <h2 class="section-title" style="color:#8B7355;">Entregados Recientemente</h2>
  <?php foreach ($entregados as $enc):
    $fecha = new DateTime($enc['fecha_entrega']);
    $dia   = $fecha->format('d');
    $mes   = strtoupper(substr($meses[(int)$fecha->format('n')-1], 0, 3));
  ?>
  <a href="index.php?page=detalle-encargo&id=<?= $enc['id'] ?>" class="card-encargo" style="opacity:.75;">
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
<div id="toast" class="toast" style="display:block;">✅ Encargo creado correctamente</div>
<script>setTimeout(()=>document.getElementById('toast').style.display='none', 3000);</script>
<?php endif; ?>