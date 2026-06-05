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

<style>
  /* CONFIGURACIÓN Y FUENTES BASE DEL ATELIER */
  body { font-family: 'Inter', sans-serif; background-color: #fcfbfa; color: #2C1810; }
  
  .nav-back { 
    color: #8B7355; 
    text-decoration: none; 
    font-size: 14px; 
    display: inline-flex;
    align-items: center; 
    gap: 8px; 
    margin-bottom: 24px; 
    transition: color .2s; 
  }
  .nav-back:hover { color: #2C1810; }

  /* ENCABEZADO DE PÁGINA */
  .det-top { 
    display: flex; 
    justify-content: space-between; 
    align-items: center; 
    margin-bottom: 32px; 
  }
  .det-top h1 { 
    font-family: 'Playfair Display', serif; 
    font-size: 38px; 
    color: #2C1810; 
    margin: 0;
    font-weight: 500;
  }
  .det-top .sub { 
    font-size: 14px; 
    color: #8B7355; 
    margin-bottom: 6px; 
  }

  /* BADGES DE ESTADO (SUPERIOR DERECHO) */
  .badge { 
    padding: 6px 16px; 
    border-radius: 20px; 
    font-size: 14px; 
    font-weight: 500; 
  }
  .badge-pendiente { background: #F5D3B3; color: #A0522D; }
  .badge-proceso   { background: #E3ECF5; color: #4682B4; }
  .badge-listo     { background: #D2E7D6; color: #558B65; }
  .badge-entregado { background: #E1D9EC; color: #6F5294; }

  /* GRILLA ASIMÉTRICA DE LA MAQUETA */
  .det-grid { 
    display: grid; 
    grid-template-columns: 1fr 360px; 
    gap: 32px; 
    align-items: start; 
  }

  /* TARJETAS BLANCAS EDITORIALES */
  .card { 
    background: #fff; 
    border: 1px solid #EDE8E0; 
    border-radius: 12px; 
    padding: 32px; 
    margin-bottom: 24px; 
    box-shadow: 0 2px 4px rgba(0,0,0,0.01);
  }
  .card h3 { 
    font-family: 'Playfair Display', serif;
    font-size: 18px; 
    color: #2C1810; 
    margin-top: 0;
    margin-bottom: 20px; 
    display: flex; 
    align-items: center; 
    gap: 10px; 
    font-weight: 500;
    text-transform: none;
    letter-spacing: normal;
  }
  .card p { 
    font-size: 15px; 
    color: #5C4A3A; 
    line-height: 1.6; 
    margin: 0;
  }

  /* SECCIÓN DE FECHAS */
  .fechas-container {
    display: flex;
    flex-direction: column;
    gap: 20px;
  }
  .fecha-block {
    padding-bottom: 16px;
    border-bottom: 1px solid #FAF8F5;
  }
  .fecha-block:last-child {
    padding-bottom: 0;
    border-bottom: none;
  }
  .fecha-block .lbl { 
    font-size: 12px; 
    color: #A69580; 
    display: block; 
    margin-bottom: 6px; 
    text-transform: uppercase;
    letter-spacing: 0.5px;
  }
  .fecha-block strong { 
    font-size: 16px; 
    color: #2C1810; 
    font-weight: 500;
  }

  /* INFORMACIÓN DEL CLIENTE */
  .cliente-box h4 { 
    font-size: 18px; 
    color: #2C1810; 
    margin: 0 0 10px 0; 
    font-weight: 600;
  }
  .cliente-box p  { 
    font-size: 14px; 
    color: #8B7355; 
    margin-bottom: 6px; 
  }
  .btn-ficha { 
    font-size: 13px; 
    color: #7D4E2F; 
    font-weight: 500; 
    text-decoration: none; 
    display: inline-block; 
    margin-top: 14px; 
    transition: color .2s;
  }
  .btn-ficha:hover { color: #2C1810; text-decoration: underline; }

  /* RESUMEN DE PAGOS (LATERAL DERECHO) */
  .pago-row { 
    display: flex; 
    justify-content: space-between; 
    font-size: 14px; 
    padding: 14px 0; 
    color: #8B7355;
    border-bottom: 1px solid #FAF8F5; 
  }
  .pago-row strong { color: #2C1810; font-weight: 600; }
  .pago-row:last-child { border-bottom: none; }
  
  .pago-row.total-row { 
    font-size: 16px;
    font-weight: 500; 
    border-top: 1px solid #EDE8E0; 
    padding-top: 20px; 
    margin-top: 10px; 
  }
  .pago-saldo { color: #A67C52; font-weight: 600; }

  /* SELECTOR DE ESTADOS INTEGRADO */
  .select-estado { 
    width: 100%; 
    padding: 14px 18px; 
    border: 1px solid #EDE8E0; 
    border-radius: 8px;
    background: #FAF8F5; 
    color: #2C1810; 
    font-size: 14px; 
    font-family: 'Inter', sans-serif;
    cursor: pointer; 
    outline: none; 
    transition: all .2s; 
  }
  .select-estado:focus { 
    border-color: #7D4E2F; 
    background: #fff;
  }

  /* HISTORIAL DE OBSERVACIONES */
  .obs-item { 
    background: #FAF8F5; 
    border: 1px solid #EDE8E0; 
    border-radius: 8px; 
    padding: 14px 18px;
    font-size: 14px; 
    color: #2C1810; 
    margin-bottom: 12px; 
    line-height: 1.5;
  }
  .obs-item .obs-fecha { 
    font-size: 11px; 
    color: #A69580; 
    margin-top: 8px; 
    text-transform: uppercase;
  }

  /* NOTIFICACIONES TOAST */
  .toast { 
    position: fixed; 
    bottom: 32px; 
    right: 32px; 
    background: #2C1810; 
    color: #FAF8F5;
    padding: 14px 24px; 
    border-radius: 8px; 
    font-size: 14px; 
    display: none; 
    z-index: 999;
    box-shadow: 0 8px 24px rgba(44,24,16,0.15); 
  }
</style>

<a href="index.php?page=agenda" class="nav-back">← Volver a Agenda</a>

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
      <p style="font-size:14px; color:#8B7355;">Sin cliente registrado</p>
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
      <p style="font-size:14px;"><?= nl2br(htmlspecialchars($enc['observaciones_encargo'])) ?></p>
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