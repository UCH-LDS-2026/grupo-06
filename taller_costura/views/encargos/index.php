<?php
require_once BASE_PATH . '/config/config.php';
require_once BASE_PATH . '/controllers/AuthController.php';
AuthController::requiereLogin();

require_once BASE_PATH . '/controllers/AgendaController.php';
$database = new Database();
$db = $database->getConnection();
$agendaCtrl = new AgendaController($db);
$datos = $agendaCtrl->getDatosAgenda();
$stats    = $datos['stats'];
$proximas = $datos['proximas'];
$recientes = $datos['recientes'];

$meses = ['','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
function etiquetaDias(int $dias, string $estado): string {
    if ($estado === 'entregado') return '';
    if ($dias < 0)  return '<span class="badge-fecha atrasado">Atrasado ' . abs($dias) . 'd</span>';
    if ($dias === 0) return '<span class="badge-fecha hoy">Hoy</span>';
    if ($dias === 1) return '<span class="badge-fecha manana">Mañana</span>';
    return '<span class="badge-fecha futuro">En ' . $dias . 'd</span>';
}
function badgeEstado(string $estado): string {
    $map = [
        'pendiente'  => ['label' => 'Pendiente',  'class' => 'estado-pendiente'],
        'en_proceso' => ['label' => 'En Proceso',  'class' => 'estado-proceso'],
        'listo'      => ['label' => 'Listo',        'class' => 'estado-listo'],
        'entregado'  => ['label' => 'Entregado',    'class' => 'estado-entregado'],
    ];
    $e = $map[$estado] ?? ['label' => $estado, 'class' => ''];
    return '<span class="badge-estado ' . $e['class'] . '">' . $e['label'] . '</span>';
}

require_once BASE_PATH . '/views/layout/header.php';
?>

<style>
    /* ── Stats ── */
    .stats-grid { display: grid; grid-template-columns: repeat(4,1fr); gap: 16px; margin-bottom: 36px; }
    .stat-card { background:#fff; border:1px solid #EDE8E0; border-radius:12px; padding:20px 24px; }
    .stat-card .num { font-family:'Playfair Display',serif; font-size:28px; color:#2C1810; }
    .stat-card .lbl { font-size:13px; color:#8B7355; margin-top:4px; }

    /* ── Section title ── */
    .section-title { font-family:'Playfair Display',serif; font-size:22px; color:#2C1810; margin-bottom:20px; }

    /* ── Encargo card ── */
    .encargo-card { background:#fff; border:1px solid #EDE8E0; border-radius:14px; padding:20px 24px; margin-bottom:14px; display:flex; gap:20px; align-items:flex-start; }
    .encargo-fecha { min-width:48px; text-align:center; }
    .encargo-fecha .dia { font-family:'Playfair Display',serif; font-size:26px; line-height:1; color:#2C1810; }
    .encargo-fecha .mes { font-size:11px; text-transform:uppercase; letter-spacing:.05em; color:#8B7355; }
    .encargo-body { flex:1; }
    .encargo-top { display:flex; align-items:center; gap:10px; justify-content:space-between; margin-bottom:4px; }
    .encargo-tipo { font-family:'Playfair Display',serif; font-size:17px; color:#2C1810; }
    .encargo-cliente { font-size:13px; color:#8B7355; margin-bottom:6px; }
    .encargo-desc { font-size:13px; color:#5C4A3A; margin-bottom:10px; }
    .encargo-obs { background:#FAF8F5; border-radius:8px; padding:10px 14px; font-size:13px; color:#5C4A3A; display:flex; gap:8px; align-items:flex-start; margin-bottom:10px; }
    .encargo-obs svg { flex-shrink:0; margin-top:1px; }
    .encargo-montos { font-size:13px; color:#5C4A3A; display:flex; gap:16px; }
    .encargo-montos .pendiente-monto { color:#C0392B; font-weight:500; }

    /* ── Badges ── */
    .badge-fecha { font-size:11px; font-weight:500; padding:2px 8px; border-radius:20px; }
    .badge-fecha.atrasado { background:#FDECEA; color:#C0392B; }
    .badge-fecha.hoy      { background:#FFF3CD; color:#856404; }
    .badge-fecha.manana   { background:#E8F4FD; color:#1565C0; }
    .badge-fecha.futuro   { background:#F0F0F0; color:#5C4A3A; }
    .badge-estado { font-size:12px; font-weight:500; padding:3px 12px; border-radius:20px; }
    .estado-pendiente { background:#FFF3CD; color:#856404; }
    .estado-proceso   { background:#E3F2FD; color:#1565C0; }
    .estado-listo     { background:#E8F5E9; color:#2E7D32; }
    .estado-entregado { background:#EDE8E0; color:#5C4A3A; }

    /* ── Recientes ── */
    .reciente-row { display:flex; justify-content:space-between; align-items:center; padding:14px 0; border-bottom:1px solid #EDE8E0; }
    .reciente-row:last-child { border-bottom:none; }
    .reciente-info .tipo { font-size:14px; color:#2C1810; }
    .reciente-info .cliente { font-size:12px; color:#8B7355; }
    .reciente-right { text-align:right; font-size:12px; color:#8B7355; }

    /* ── Nuevo Encargo btn ── */
    .page-header-row { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:8px; }
    .btn-nuevo { background:#7D4E2F; color:#fff; border:none; padding:10px 20px; border-radius:10px; font-size:14px; font-family:'Inter',sans-serif; cursor:pointer; text-decoration:none; display:inline-flex; align-items:center; gap:6px; }
    .btn-nuevo:hover { background:#6B4027; }
    .fecha-hoy-sub { font-size:13px; color:#8B7355; margin-bottom:28px; }
</style>

<?php
$diasSemana = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];
$hoyFmt = 'Hoy es ' . $diasSemana[date('w')] . ', ' . date('j') . ' de ' . ['','enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'][date('n')] . ' de ' . date('Y');
?>

<div class="page-header-row">
    <div>
        <h1 style="font-family:'Playfair Display',serif;font-size:32px;color:#2C1810;">Agenda de Encargos</h1>
        <p class="fecha-hoy-sub"><?= $hoyFmt ?></p>
    </div>
    <a href="/ProyectoFinal/grupo-06/taller_costura/index.php?page=encargos&action=crear" class="btn-nuevo">+ Nuevo Encargo</a>
</div>

<!-- Stats -->
<div class="stats-grid">
    <div class="stat-card"><div class="num"><?= $stats['activos'] ?></div><div class="lbl">Encargos Activos</div></div>
    <div class="stat-card"><div class="num"><?= $stats['en_proceso'] ?></div><div class="lbl">En Proceso</div></div>
    <div class="stat-card"><div class="num"><?= $stats['listos'] ?></div><div class="lbl">Listos</div></div>
    <div class="stat-card"><div class="num">$<?= number_format($stats['saldo_pendiente'], 0, ',', '.') ?></div><div class="lbl">Saldo Pendiente</div></div>
</div>

<!-- Próximas entregas -->
<h2 class="section-title">Próximas Entregas</h2>

<?php if (empty($proximas)): ?>
    <p style="color:#8B7355;font-size:14px;">No hay encargos pendientes.</p>
<?php else: ?>
    <?php foreach ($proximas as $e):
        $dt = new DateTime($e['fecha_entrega']);
        $diaNum = $dt->format('j');
        $mesNum = (int)$dt->format('n');
    ?>
    <div class="encargo-card">
        <div class="encargo-fecha">
            <div class="dia"><?= $diaNum ?></div>
            <div class="mes"><?= $meses[$mesNum] ?></div>
            <?= etiquetaDias($e['dias_diff'], $e['estado']) ?>
        </div>
        <div class="encargo-body">
            <div class="encargo-top">
                <span class="encargo-tipo"><?= htmlspecialchars($e['tipo']) ?></span>
                <?= badgeEstado($e['estado']) ?>
            </div>
            <div class="encargo-cliente"><?= htmlspecialchars($e['cliente_nombre'] ?? '—') ?></div>
            <?php if (!empty($e['descripcion'])): ?>
                <div class="encargo-desc"><?= htmlspecialchars($e['descripcion']) ?></div>
            <?php endif; ?>
            <?php foreach ($e['observaciones'] as $obs): ?>
            <div class="encargo-obs">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none"><circle cx="12" cy="12" r="10" stroke="#8B7355" stroke-width="1.5"/><path d="M12 8v4M12 16h.01" stroke="#8B7355" stroke-width="1.5" stroke-linecap="round"/></svg>
                <?= htmlspecialchars($obs['texto']) ?>
            </div>
            <?php endforeach; ?>
            <div class="encargo-montos">
                <span>Total: $<?= number_format($e['monto_total'], 0, ',', '.') ?></span>
                <span>Seña: $<?= number_format($e['sena'], 0, ',', '.') ?></span>
                <span class="pendiente-monto">Pendiente: $<?= number_format($e['saldo'], 0, ',', '.') ?></span>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Entregados recientemente -->
<?php if (!empty($recientes)): ?>
<h2 class="section-title" style="margin-top:36px;">Entregados Recientemente</h2>
<div style="background:#fff;border:1px solid #EDE8E0;border-radius:14px;padding:4px 24px;">
    <?php foreach ($recientes as $e): ?>
    <div class="reciente-row">
        <div class="reciente-info">
            <div class="tipo"><?= htmlspecialchars($e['tipo']) ?></div>
            <div class="cliente"><?= htmlspecialchars($e['cliente_nombre'] ?? '—') ?></div>
        </div>
        <div class="reciente-right">
            Entregado <?= (new DateTime($e['fecha_entrega']))->format('j/n/Y') ?>
            <br><?= badgeEstado('entregado') ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

</div><!-- /content-area -->
</div><!-- /main-wrapper -->
</body>
</html>
 