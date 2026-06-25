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

$stmtPagos = $pdo->prepare(
    "SELECT id, monto, metodo, nota, fecha 
     FROM pago 
     WHERE encargo_id = ? 
     ORDER BY fecha ASC"
);
$stmtPagos->execute([$idEncargo]);
$historialPagos = $stmtPagos->fetchAll(PDO::FETCH_ASSOC);

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

<?php 
$origen = $_GET['origen'] ?? 'agenda';
$filtro = $_GET['filtro'] ?? '';
?>
<a href="<?= $origen === 'pagos' ? 'index.php?page=pagos&filtro=' . urlencode($filtro) : 'index.php' ?>" class="nav-back">
    ← Volver a <?= $origen === 'pagos' ? 'Pagos' : 'Agenda' ?>
</a>

<div class="det-top">
  <div>
    <div class="sub">Encargo #<?= $enc['id'] ?></div>
    <h1><?= htmlspecialchars($enc['tipo']) ?></h1>
  </div>
  <div style="display: flex; align-items: center; gap: 12px;">
    <a href="index.php?page=editar-encargo&id=<?= $enc['id'] ?>" class="btn-cancel" style="display: inline-flex; align-items: center; gap: 6px; padding: 0.6rem 1.2rem; border-radius: 999px; font-size: 0.85rem; text-decoration: none;">      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
      Editar Encargo
    </a>
    <button onclick="eliminarEncargo(<?= $enc['id'] ?>, <?= $totalPagado ?>)" class="btn-cancel" style="display: inline-flex; align-items: center; gap: 6px; padding: 0.6rem 1.2rem; border-radius: 999px; font-size: 0.85rem; color: #b05040; border-color: rgba(176,80,64,0.2); background: transparent; cursor: pointer;">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path><path d="M10 11v6"></path><path d="M14 11v6"></path><path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"></path></svg>
      Eliminar Encargo
    </button>
    <span class="badge <?= $badgeClass ?>" id="badgeEstado"><?= $badgeTxt ?></span>
  </div>
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
        <?php if ($cliente['telefono']): ?><p><?= htmlspecialchars($cliente['telefono']) ?></p><?php endif; ?>
        <?php if ($cliente['email']): ?><p><?= htmlspecialchars($cliente['email']) ?></p><?php endif; ?>
        <a href="index.php?page=ficha-cliente&id=<?= $cliente['id'] ?>" class="btn-ficha">Ver ficha completa →</a>
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

    <div class="card">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="margin: 0;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
          Descripción
        </h3>
      </div>
      <?php if (!empty($enc['descripcion'])): ?>
        <p><?= nl2br(htmlspecialchars($enc['descripcion'])) ?></p>
      <?php else: ?>
        <p class="info-text">Sin descripción registrada para este encargo.</p>
      <?php endif; ?>
    </div>

    <div class="card">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
        <h3 style="margin: 0;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="8" x2="12" y2="12"></line><line x1="12" y1="16" x2="12.01" y2="16"></line></svg>
          Observaciones Especiales
        </h3>
      </div>

      <?php if (!empty($enc['observaciones_encargo'])): ?>
        <div class="obs-especial-box" id="obsEspecialBox">
          <?= nl2br(htmlspecialchars($enc['observaciones_encargo'])) ?>
        </div>
        <div style="display: flex; justify-content: flex-end; margin-top: 12px;">
          <button onclick="eliminarObservacionEspecial(<?= $enc['id'] ?>)" class="btn-cancel" style="font-size: 0.78rem; padding: 6px 12px; color: #b05040; border-color: rgba(176,80,64,0.2); background: transparent; cursor: pointer; border-radius: 999px">
            Eliminar observación
          </button>
        </div>
      <?php else: ?>
        <p class="info-text" id="obsEspecialVacio">Sin observaciones especiales para este encargo.</p>
      <?php endif; ?>
    </div>

    <div class="card" id="cardHistorialObs">
      <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 16px;">
        <h3 style="margin:0;">📝 Historial de Observaciones</h3>
        <button onclick="abrirFormObs()" class="btn-ficha" style="margin-top:0; cursor:pointer; border:none; background:none; font-size:1.2rem; font-weight:700; color: var(--accent);" title="Agregar observación">+</button>
      </div>

      <div id="formNuevaObs" style="display:none; margin-bottom:16px;">
        <textarea id="inputNuevaObs" class="form-control" rows="2" placeholder="Escribí una nueva observación..."></textarea>
        <div style="display:flex; gap:8px; margin-top:8px; justify-content:flex-end;">
          <button onclick="cancelarFormObs()" class="btn-cancel" style="font-size:0.82rem; padding:5px 12px;">Cancelar</button>
          <button onclick="guardarNuevaObs()" class="btn-submit" style="font-size:0.82rem; padding:5px 12px;">Guardar</button>
        </div>
      </div>

      <div id="listaHistorialObs">
        <?php if (!empty($observaciones)): ?>
          <?php foreach ($observaciones as $obs): ?>
            <div class="obs-item" id="obs-<?= $obs['id'] ?>" style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px;">
              <div>
                <?= htmlspecialchars($obs['detalle']) ?>
                <div class="obs-fecha"><?= date('d/m/Y H:i', strtotime($obs['fecha'])) ?></div>
              </div>
              <button onclick="eliminarObsHistorial(<?= $obs['id'] ?>)" title="Eliminar"
                style="background:none;border:none;cursor:pointer;color:#b05040;font-size:1rem;flex-shrink:0;padding:2px 6px;">✕</button>
            </div>
          <?php endforeach; ?>
        <?php else: ?>
          <p class="info-text" id="sinObsMsg">Sin observaciones registradas.</p>
        <?php endif; ?>
      </div>
    </div>

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
          <div class="progreso-fill" id="progresoFill" style="width:<?= $porcentaje ?>%" data-total="<?= (float)$enc['monto_total'] ?>"></div>
        </div>
      </div>

      <?php if ($saldo > 0): ?>
      <button class="btn-registrar-pago" id="btnAbrirPago" style="margin-top:16px; width:100%;">
        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
        Registrar Pago
      </button>
      <?php endif; ?>

    </div>

    <div class="card" id="cardHistorial" style="<?= empty($historialPagos) ? 'display:none' : '' ?>; overflow:hidden;">
    <div style="display:flex; justify-content:space-between; align-items:center; cursor:pointer;"
         onclick="toggleHistorialPagos()">
        <h3 style="margin:0;">Historial de Pagos</h3>
        <span id="historial-toggle-icon" style="font-size:1.2rem; color:var(--texto-ter);">↓</span>
    </div>
    
   <div id="historial-pagos-lista" style="display:none; margin-top:16px;">
    <?php foreach ($historialPagos as $p): ?>
    <div class="pago-hist-item" id="pago-<?= $p['id'] ?>">
        <div class="pago-hist-icon">✓</div>
        <div class="pago-hist-info">
            <strong><?= fmtMonto($p['monto']) ?></strong>
            <span><?= fmtFecha($p['fecha'], $meses) ?></span>
            <div><span class="pago-metodo-tag"><?= ucfirst($p['metodo']) ?></span></div>
            <?php if (!empty($p['nota'])): ?><em><?= htmlspecialchars($p['nota']) ?></em><?php endif; ?>
        </div>
        <button onclick="eliminarPago(<?= $p['id'] ?>, <?= $p['monto'] ?>, <?= $idEncargo ?>)"
            style="background:none;border:none;cursor:pointer;color:#b05040;font-size:1rem;flex-shrink:0;padding:2px 6px;">✕</button>
    </div>
    <?php endforeach; ?>
</div>

<div class="modal-overlay" id="modalPago" style="display:none" onclick="if(event.target===this) cerrarModalPago()">
  <div class="modal-box">
    <div class="modal-header" style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
      <h3 style="margin:0;">Registrar Pago</h3>
      <button onclick="cerrarModalPago()" style="background:none;border:none;cursor:pointer;font-size:1.3rem;color:var(--texto-sec);">&times;</button>
    </div>

    <div style="background:var(--bg-input,#f8f5f1); border-radius:10px; padding:12px 16px; margin-bottom:20px; font-size:0.87rem; line-height:1.7;">
      <strong><?= htmlspecialchars($enc['tipo']) ?></strong><br>
      <?php if ($cliente): ?><span><?= htmlspecialchars($cliente['nombre']) ?></span><br><?php endif; ?>
      Total: <strong><?= fmtMonto($enc['monto_total']) ?></strong> &nbsp;|&nbsp;
      Pagado: <strong><?= fmtMonto($totalPagado) ?></strong><br>
      Saldo pendiente: <strong id="modalSaldo" style="color:#b05040;"><?= fmtMonto($saldo) ?></strong>
    </div>

    <div class="form-group" style="margin-bottom:8px;">
      <label>Monto a registrar</label>
      <input type="number" id="inputMonto" class="form-control" placeholder="Ej: 3000" min="1" step="1" oninput="validarMontoDetalle(this)">
      <div id="inputMontoHint" style="font-size:0.8rem; margin-top:5px; min-height:18px; color:#b05040;"></div>
    </div>

    <div class="form-group" style="margin-bottom:20px;">
      <label>Método de pago</label>
      <div style="display:flex; gap:10px; margin-top:8px; flex-wrap:wrap;">
        <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-size:0.87rem;">
          <input type="radio" name="detalle_metodo_pago" value="efectivo" checked> Efectivo
        </label>
        <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-size:0.87rem;">
          <input type="radio" name="detalle_metodo_pago" value="transferencia"> Transferencia
        </label>
        <label style="display:flex; align-items:center; gap:6px; cursor:pointer; font-size:0.87rem;">
          <input type="radio" name="detalle_metodo_pago" value="tarjeta"> Tarjeta
        </label>
      </div>
    </div>

    <div class="form-group" style="margin-bottom:24px;">
      <label>Nota (opcional)</label>
      <input type="text" id="inputNota" class="form-control" placeholder="Ej: Seña 50%">
    </div>

    <div class="modal-actions">
      <button class="btn-cancel" onclick="cerrarModalPago()">Cancelar</button>
      <button class="btn-submit" id="btnConfirmarPagoDetalle">
        Confirmar Pago
        <span id="spinnerPago" style="display:none; margin-left:6px;">⏳</span>
      </button>
    </div>
  </div>
</div>

<div id="toast" class="toast"></div>

<script src="<?= BASE_URL ?>/public/js/encargos/encargos.js"></script>
<script>
const SALDO_PENDIENTE_DETALLE = <?= $saldo ?>;

document.addEventListener('DOMContentLoaded', () => {
  const fill = document.getElementById('progresoFill');
  if (fill) fill.dataset.total = <?= (float)$enc['monto_total'] ?>;
});

function cerrarModalPago() {
  document.getElementById('modalPago').style.display = 'none';
  document.getElementById('inputMonto').value = '';
  document.getElementById('inputNota').value  = '';
  document.getElementById('inputMontoHint').textContent = '';
  document.getElementById('btnConfirmarPagoDetalle').disabled = false;
  const efectivo = document.querySelector('input[name="detalle_metodo_pago"][value="efectivo"]');
  if (efectivo) efectivo.checked = true;
}

function validarMontoDetalle(input) {
  const hint = document.getElementById('inputMontoHint');
  const btn  = document.getElementById('btnConfirmarPago');
  const val  = parseFloat(input.value);
  const saldoActual = parseFloat(document.getElementById('progresoFill').dataset.total)
                    - parseFloat(document.getElementById('spanTotalPagado').textContent.replace(/[^0-9]/g,''));

  if (isNaN(val) || val <= 0) {
    hint.textContent = 'Ingresá un monto mayor a cero.';
    btn.disabled = true;
  } else if (val > SALDO_PENDIENTE_DETALLE) {
    hint.textContent = 'El monto no puede superar el saldo pendiente.';
    btn.disabled = true;
  } else {
    hint.textContent = '';
    btn.disabled = false;
  }
}
function toggleHistorialPagos() {
    const lista = document.getElementById('historial-pagos-lista');
    const icon  = document.getElementById('historial-toggle-icon');
    if (lista.style.display === 'none') {
        lista.style.display = 'block';
        icon.textContent = '↑';
    } else {
        lista.style.display = 'none';
        icon.textContent = '↓';
    }
}
function eliminarPago(pagoId, monto, encargoId) {
    if (!confirm('¿Eliminár este pago de $' + Number(monto).toLocaleString('es-AR') + '?')) return;
    fetch('index.php?page=eliminar-pago', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ pago_id: pagoId, encargo_id: encargoId, monto: monto })
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            document.getElementById('pago-' + pagoId).remove();
            setTimeout(() => location.reload(), 800);
        } else {
            alert(data.mensaje || 'Error al eliminar');
        }
    })
    .catch(() => alert('Error de conexión'));
}
</script>