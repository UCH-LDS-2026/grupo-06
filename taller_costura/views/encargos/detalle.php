<?php
require_once __DIR__ . '/../../config/config.php';
// AuthController::requiereLogin();

$idEncargo = (int)($_GET['id'] ?? 0);
if (!$idEncargo) { header('Location: index.php'); exit; }

$encargoModel = new Encargo($db->getConnection());
$encargoModel->id = $idEncargo;
$enc = $encargoModel->getById();

if (!$enc) { header('Location: index.php'); exit; }

// Datos del cliente completo si existe
$cliente = null;
if (!empty($enc['cliente_id'])) {
    $pdo  = $db->getConnection();
    $stmt = $pdo->prepare("SELECT * FROM cliente WHERE id = ? LIMIT 1");
    $stmt->execute([$enc['cliente_id']]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Observaciones del encargo
$pdo  = $db->getConnection();
$stmtObs = $pdo->prepare("SELECT * FROM observacion WHERE encargo_id = ? ORDER BY fecha ASC");
$stmtObs->execute([$idEncargo]);
$observaciones = $stmtObs->fetchAll(PDO::FETCH_ASSOC);

$meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
function fmtFecha($dateStr, $meses) {
    if (!$dateStr) return '—';
    $d = new DateTime($dateStr);
    return $d->format('d') . ' de ' . $meses[(int)$d->format('n')-1] . ' de ' . $d->format('Y');
}
function fmtMonto($n) { return '$' . number_format((float)$n, 0, ',', '.'); }

$estadoLabel = ['pendiente'=>'Pendiente','en_proceso'=>'En Proceso','listo'=>'Listo','entregado'=>'Entregado'];
$saldo = $enc['monto_total'] - $enc['sena'];
?>

<a href="index.php" class="nav-back">← Volver a Agenda</a>

<div class="det-top">
  <div>
    <div class="sub">Encargo #<?= $enc['id'] ?></div>
    <h1><?= htmlspecialchars($enc['tipo']) ?></h1>
  </div>
  <?php
    $badgeClass = 'badge-' . ($enc['estado'] === 'en_proceso' ? 'proceso' : $enc['estado']);
    $badgeTxt   = $estadoLabel[$enc['estado']] ?? ucfirst($enc['estado']);
  ?>
  <span class="badge <?= $badgeClass ?>"><?= $badgeTxt ?></span>
</div>

<div class="det-grid">

  <div>
    <?php if ($cliente): ?>
    <div class="card">
      <h3>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
        Cliente
      </h3>
      <div class="cliente-box">
        <h4><?= htmlspecialchars($cliente['nombre']) ?></h4>
        <?php if ($cliente['telefono']): ?><p>📞 <?= htmlspecialchars($cliente['telefono']) ?></p><?php endif; ?>
        <?php if ($cliente['email']): ?><p>✉️ <?= htmlspecialchars($cliente['email']) ?></p><?php endif; ?>
        <a href="index.php?page=clientes&action=ver&id=<?= $cliente['id'] ?>" class="btn-ficha">Ver ficha completa →</a>
      </div>
    </div>
    <?php else: ?>
    <div class="card">
      <h3>👤 Cliente</h3>
      <p class="info-text">Sin cliente registrado</p>
    </div>
    <?php endif; ?>

    <?php if (!empty($enc['descripcion'])): ?>
    <div class="card">
      <h3>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line><polyline points="10 9 9 9 8 9"></polyline></svg>
        Descripción
      </h3>
      <p><?= nl2br(htmlspecialchars($enc['descripcion'])) ?></p>
    </div>
    <?php endif; ?>

    <?php if (!empty($enc['observaciones_encargo'])): ?>
    <div class="card">
      <h3>💬 Observaciones Especiales</h3>
      <p class="info-text"><?= nl2br(htmlspecialchars($enc['observaciones_encargo'])) ?></p>
    </div>
    <?php endif; ?>

    <?php if (!empty($observaciones)): ?>
    <div class="card">
      <h3>📝 Historial de Observaciones</h3>
      <?php foreach ($observaciones as $obs): ?>
        <div class="obs-item">
          <?= htmlspecialchars($obs['detalle']) ?>
          <div class="obs-fecha"><?= date('d/m/Y H:i', strtotime($obs['fecha'])) ?></div>
        </div>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>

    <div class="card">
      <h3>Estado del Encargo</h3>
      <select id="selectEstado" class="select-estado" data-id="<?= $enc['id'] ?>">
        <option value="pendiente"  <?= $enc['estado']==='pendiente'  ? 'selected':'' ?>>⏳ Pendiente</option>
        <option value="en_proceso" <?= $enc['estado']==='en_proceso' ? 'selected':'' ?>>✂️ En Proceso</option>
        <option value="listo"      <?= $enc['estado']==='listo'      ? 'selected':'' ?>>✅ Listo</option>
        <option value="entregado"  <?= $enc['estado']==='entregado'  ? 'selected':'' ?>>📦 Entregado</option>
      </select>
    </div>
  </div>

  <div>
    <div class="card">
      <h3>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect><line x1="16" y1="2" x2="16" y2="6"></line><line x1="8" y1="2" x2="8" y2="6"></line><line x1="3" y1="10" x2="21" y2="10"></line></svg>
        Fechas
      </h3>
      <div class="fechas-container">
        <div class="fecha-block">
          <span class="lbl">Fecha de Encargo</span>
          <strong><?= fmtFecha($enc['created_at'], $meses) ?></strong>
        </div>
        <div class="fecha-block">
          <span class="lbl">Fecha de Entrega</span>
          <strong><?= fmtFecha($enc['fecha_entrega'], $meses) ?></strong>
        </div>
      </div>
    </div>

    <div class="card">
      <h3>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
        Resumen de Pagos
      </h3>
      <div class="pago-row"><span>Precio Total</span><strong><?= fmtMonto($enc['monto_total']) ?></strong></div>
      <div class="pago-row"><span>Total Pagado</span><span><?= fmtMonto($enc['sena']) ?></span></div>
      <div class="pago-row total-row">
        <span>Saldo Pendiente</span>
        <span class="pago-saldo"><?= fmtMonto($saldo) ?></span>
      </div>
    </div>
  </div>

</div>

<div id="toast" class="toast"></div>

<script>
document.getElementById('selectEstado').addEventListener('change', function() {
  const sel   = this;
  const id    = sel.getAttribute('data-id');
  const estado = sel.value;
  sel.disabled = true;

  fetch('index.php?page=actualizar-estado-encargo', {
    method: 'POST',
    headers: {'Content-Type':'application/json'},
    body: JSON.stringify({id, estado})
  })
  .then(r => r.json())
  .then(d => {
    sel.disabled = false;
    const t = document.getElementById('toast');
    t.textContent = d.ok ? '✅ Estado actualizado correctamente' : '❌ Error al actualizar';
    t.style.display = 'block';
    setTimeout(() => t.style.display='none', 2500);
  })
  .catch(() => { sel.disabled = false; alert('Error de red'); });
});
</script>