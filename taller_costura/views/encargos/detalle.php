<?php
require_once __DIR__ . '/../../config/config.php';

$idEncargo = (int)($_GET['id'] ?? 0);
if (!$idEncargo) { header('Location: index.php'); exit; }

$pdo = $db->getConnection();

$encargoModel = new Encargo($pdo);
$encargoModel->id = $idEncargo;
$enc = $encargoModel->getById();
if (!$enc) { header('Location: index.php'); exit; }

$cliente = null;
if (!empty($enc['cliente_id'])) {
    $stmt = $pdo->prepare("SELECT * FROM cliente WHERE id = ? LIMIT 1");
    $stmt->execute([$enc['cliente_id']]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC);
}

$stmtObs = $pdo->prepare("SELECT * FROM observacion WHERE encargo_id = ? ORDER BY fecha ASC");
$stmtObs->execute([$idEncargo]);
$observaciones = $stmtObs->fetchAll(PDO::FETCH_ASSOC);

// Historial de pagos — tabla pago (si no existe devuelve array vacío)
$historialPagos = [];
try {
    $stmtPagos = $pdo->prepare("SELECT * FROM pago WHERE encargo_id = ? ORDER BY created_at DESC");
    $stmtPagos->execute([$idEncargo]);
    $historialPagos = $stmtPagos->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) { /* tabla aún no creada */ }

$meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
function fmtFecha($dateStr, $meses) {
    if (!$dateStr) return '—';
    $d = new DateTime($dateStr);
    return $d->format('d') . ' de ' . $meses[(int)$d->format('n')-1] . ' de ' . $d->format('Y');
}
function fmtMonto($n) { return '$' . number_format((float)$n, 0, ',', '.'); }

$estadoLabel = ['pendiente'=>'Pendiente','en_proceso'=>'En Proceso','listo'=>'Listo','entregado'=>'Entregado'];
$totalPagado = (float)$enc['sena'];
$saldo       = (float)$enc['monto_total'] - $totalPagado;
$porcentaje  = $enc['monto_total'] > 0 ? round(($totalPagado / $enc['monto_total']) * 100) : 0;
$badgeClass  = 'badge-' . ($enc['estado'] === 'en_proceso' ? 'proceso' : $enc['estado']);
$badgeTxt    = $estadoLabel[$enc['estado']] ?? ucfirst($enc['estado']);
?>

<a href="index.php" class="nav-back">← Volver a Agenda</a>

<div class="det-top">
  <div>
    <div class="sub">Encargo #<?= $enc['id'] ?></div>
    <h1><?= htmlspecialchars($enc['tipo']) ?></h1>
  </div>
  <span class="badge <?= $badgeClass ?>" id="badgeEstado"><?= $badgeTxt ?></span>
</div>

<div class="det-grid">

  <!-- ── Columna izquierda ── -->
  <div>
    <?php if ($cliente): ?>
    <div class="card">
      <h3>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
        Cliente
      </h3>
      <div class="cliente-box">
        <h4><?= htmlspecialchars($cliente['nombre']) ?></h4>
        <?php if ($cliente['telefono']): ?><p><?= htmlspecialchars($cliente['telefono']) ?></p><?php endif; ?>
        <?php if ($cliente['email']): ?><p><?= htmlspecialchars($cliente['email']) ?></p><?php endif; ?>
        <a href="index.php?page=clientes&action=ver&id=<?= $cliente['id'] ?>" class="btn-ficha">Ver ficha completa →</a>
      </div>
    </div>
    <?php else: ?>
    <div class="card">
      <h3>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
        Cliente
      </h3>
      <p class="info-text">Sin cliente registrado</p>
    </div>
    <?php endif; ?>

    <?php if (!empty($enc['descripcion'])): ?>
    <div class="card">
      <h3>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
        Descripción
      </h3>
      <p><?= nl2br(htmlspecialchars($enc['descripcion'])) ?></p>
    </div>
    <?php endif; ?>

    <?php if (!empty($enc['observaciones_encargo'])): ?>
    <div class="card">
      <h3>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
        Observaciones Especiales
      </h3>
      <div class="obs-especial-box">
        <?= nl2br(htmlspecialchars($enc['observaciones_encargo'])) ?>
      </div>
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

    <!-- Estado del Encargo — 4 botones visuales -->
    <div class="card">
      <h3>Estado del Encargo</h3>
      <div class="estado-grid" id="estadoGrid"
           data-id="<?= $enc['id'] ?>"
           data-current="<?= $enc['estado'] ?>">
        <button class="estado-btn <?= $enc['estado']==='pendiente'  ? 'activo':'' ?>" data-estado="pendiente">
          <span class="estado-dot dot-pendiente"></span>Pendiente
        </button>
        <button class="estado-btn <?= $enc['estado']==='en_proceso' ? 'activo':'' ?>" data-estado="en_proceso">
          <span class="estado-dot dot-proceso"></span>En Proceso
        </button>
        <button class="estado-btn <?= $enc['estado']==='listo'      ? 'activo':'' ?>" data-estado="listo">
          <span class="estado-dot dot-listo"></span>Listo
        </button>
        <button class="estado-btn <?= $enc['estado']==='entregado'  ? 'activo':'' ?>" data-estado="entregado">
          <span class="estado-dot dot-entregado"></span>Entregado
        </button>
      </div>
    </div>
  </div>

  <!-- ── Columna derecha ── -->
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

    <!-- Resumen de Pagos -->
    <div class="card">
      <h3>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
        Resumen de Pagos
      </h3>
      <div class="pago-row">
        <span>Precio Total</span>
        <strong><?= fmtMonto($enc['monto_total']) ?></strong>
      </div>
      <div class="pago-row">
        <span>Total Pagado</span>
        <span id="spanTotalPagado" style="color:#558B65;font-weight:600"><?= fmtMonto($totalPagado) ?></span>
      </div>
      <div class="pago-row total-row">
        <span>Saldo Pendiente</span>
        <span class="pago-saldo" id="spanSaldo"><?= fmtMonto($saldo) ?></span>
      </div>
      <div class="progreso-wrap">
        <div class="progreso-label">
          <span>Progreso de pago</span>
          <span id="spanPorcentaje"><?= $porcentaje ?>%</span>
        </div>
        <div class="progreso-bar">
          <div class="progreso-fill" id="progresoFill" style="width:<?= $porcentaje ?>%"></div>
        </div>
      </div>
      <?php if ($saldo > 0): ?>
      <button class="btn-registrar-pago" id="btnAbrirPago">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        Registrar Pago
      </button>
      <?php endif; ?>
    </div>

    <!-- Historial de Pagos -->
    <div class="card" id="cardHistorial"<?= empty($historialPagos) ? ' style="display:none"' : '' ?>>
      <h3>Historial de Pagos</h3>
      <div id="listaPagos">
        <?php foreach ($historialPagos as $p): ?>
        <div class="pago-hist-item">
          <div class="pago-hist-icon">✓</div>
          <div class="pago-hist-info">
            <strong><?= fmtMonto($p['monto']) ?></strong>
            <span><?= fmtFecha($p['created_at'], $meses) ?></span>
            <span class="pago-metodo-tag"><?= ucfirst($p['metodo']) ?></span>
            <?php if (!empty($p['nota'])): ?><em><?= htmlspecialchars($p['nota']) ?></em><?php endif; ?>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

</div>

<!-- Modal registrar pago -->
<div class="modal-overlay" id="modalPago" style="display:none">
  <div class="modal-box">
    <h3>Registrar Pago</h3>
    <p class="modal-sub">Saldo pendiente: <strong id="modalSaldo"><?= fmtMonto($saldo) ?></strong></p>
    <div class="form-group" style="margin-bottom:16px">
      <label>Monto</label>
      <input type="number" id="inputMonto" class="form-control" placeholder="0" min="1" step="1">
    </div>
    <div class="form-group" style="margin-bottom:16px">
      <label>Método de pago</label>
      <select id="selectMetodo" class="form-control">
        <option value="efectivo">Efectivo</option>
        <option value="transferencia">Transferencia</option>
        <option value="tarjeta">Tarjeta</option>
        <option value="otro">Otro</option>
      </select>
    </div>
    <div class="form-group" style="margin-bottom:24px">
      <label>Nota (opcional)</label>
      <input type="text" id="inputNota" class="form-control" placeholder="Ej: Seña 50%">
    </div>
    <div class="modal-actions">
      <button class="btn-cancel" id="btnCerrarModal">Cancelar</button>
      <button class="btn-submit" id="btnConfirmarPago">Confirmar Pago</button>
    </div>
  </div>
</div>

<div id="toast" class="toast"></div>

<script>
const ENCARGO_ID  = <?= (int)$enc['id'] ?>;
const MONTO_TOTAL = <?= (float)$enc['monto_total'] ?>;
const MESES = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];

function fmtMontoJS(n) {
  return '$' + Math.round(n).toLocaleString('es-AR');
}
function showToast(msg, ok) {
  const t = document.getElementById('toast');
  t.textContent = msg;
  t.style.background = (ok === false) ? '#C53030' : '#2C1810';
  t.style.display = 'block';
  setTimeout(() => t.style.display = 'none', 2800);
}

// ── Cambio de estado ──────────────────────────────────────
const badgeClasses = {
  pendiente:  'badge-pendiente',
  en_proceso: 'badge-proceso',
  listo:      'badge-listo',
  entregado:  'badge-entregado'
};
const badgeLabels = {
  pendiente: 'Pendiente', en_proceso: 'En Proceso', listo: 'Listo', entregado: 'Entregado'
};

document.getElementById('estadoGrid').addEventListener('click', function(e) {
  const btn = e.target.closest('.estado-btn');
  if (!btn) return;
  const nuevoEstado = btn.dataset.estado;
  const id = this.dataset.id;

  document.querySelectorAll('.estado-btn').forEach(b => b.classList.remove('activo'));
  btn.classList.add('activo');

  const badge = document.getElementById('badgeEstado');
  badge.className = 'badge ' + (badgeClasses[nuevoEstado] || '');
  badge.textContent = badgeLabels[nuevoEstado] || nuevoEstado;

  fetch('index.php?page=actualizar-estado-encargo', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({id, estado: nuevoEstado})
  })
  .then(r => r.json())
  .then(d => showToast(d.ok ? '✅ Estado actualizado' : '❌ Error al actualizar', d.ok))
  .catch(() => showToast('❌ Error de red', false));
});

// ── Modal de pago ─────────────────────────────────────────
const modal    = document.getElementById('modalPago');
const btnAbrir = document.getElementById('btnAbrirPago');
const btnCerrar  = document.getElementById('btnCerrarModal');
const btnConfirmar = document.getElementById('btnConfirmarPago');

if (btnAbrir)    btnAbrir.addEventListener('click', () => modal.style.display = 'flex');
if (btnCerrar)   btnCerrar.addEventListener('click', () => modal.style.display = 'none');
modal.addEventListener('click', e => { if (e.target === modal) modal.style.display = 'none'; });

if (btnConfirmar) {
  btnConfirmar.addEventListener('click', () => {
    const monto  = parseFloat(document.getElementById('inputMonto').value);
    const metodo = document.getElementById('selectMetodo').value;
    const nota   = document.getElementById('inputNota').value.trim();

    if (!monto || monto <= 0) { showToast('❌ Ingresá un monto válido', false); return; }

    btnConfirmar.disabled = true;
    btnConfirmar.textContent = 'Guardando…';

    fetch('index.php?page=registrar-pago-detalle', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({encargo_id: ENCARGO_ID, monto, metodo, nota})
    })
    .then(r => r.json())
    .then(d => {
      btnConfirmar.disabled = false;
      btnConfirmar.textContent = 'Confirmar Pago';
      if (!d.ok) { showToast('❌ ' + (d.mensaje || 'Error'), false); return; }

      modal.style.display = 'none';
      document.getElementById('inputMonto').value = '';
      document.getElementById('inputNota').value  = '';

      const nuevoPagado = parseFloat(d.nueva_sena);
      const nuevoSaldo  = MONTO_TOTAL - nuevoPagado;
      const pct = MONTO_TOTAL > 0 ? Math.round((nuevoPagado / MONTO_TOTAL) * 100) : 0;

      document.getElementById('spanTotalPagado').textContent = fmtMontoJS(nuevoPagado);
      document.getElementById('spanSaldo').textContent       = fmtMontoJS(nuevoSaldo);
      document.getElementById('spanPorcentaje').textContent  = pct + '%';
      document.getElementById('progresoFill').style.width    = pct + '%';
      document.getElementById('modalSaldo').textContent      = fmtMontoJS(nuevoSaldo);

      if (nuevoSaldo <= 0 && btnAbrir) btnAbrir.style.display = 'none';

      const now   = new Date();
      const fecha = now.getDate() + ' de ' + MESES[now.getMonth()] + ' de ' + now.getFullYear();
      const card  = document.getElementById('cardHistorial');
      const lista = document.getElementById('listaPagos');
      card.style.display = 'block';
      lista.insertAdjacentHTML('afterbegin', `
        <div class="pago-hist-item">
          <div class="pago-hist-icon">✓</div>
          <div class="pago-hist-info">
            <strong>${fmtMontoJS(monto)}</strong>
            <span>${fecha}</span>
            <span class="pago-metodo-tag">${metodo.charAt(0).toUpperCase()+metodo.slice(1)}</span>
            ${nota ? '<em>' + nota + '</em>' : ''}
          </div>
        </div>
      `);

      showToast('✅ Pago registrado correctamente');
    })
    .catch(() => {
      btnConfirmar.disabled = false;
      btnConfirmar.textContent = 'Confirmar Pago';
      showToast('❌ Error de red', false);
    });
  });
}
</script>