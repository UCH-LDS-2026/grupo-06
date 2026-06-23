<?php
require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Encargo.php';

$db = Database::getInstance()->getConnection();

$idEncargo = (int)($_GET['id'] ?? 0);
if (!$idEncargo) { header('Location: ' . BASE_URL . '/index.php'); exit; }

$encargoModel = new Encargo($db);
$encargoModel->id = $idEncargo;
$enc = $encargoModel->getById();
if (!$enc) { header('Location: ' . BASE_URL . '/index.php'); exit; }

$stmtClientes = $db->query("SELECT id, nombre FROM cliente ORDER BY nombre");
$clientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);

$clienteSeleccionado = null;
foreach ($clientes as $c) {
    if ($c['id'] == $enc['cliente_id']) { $clienteSeleccionado = $c['nombre']; break; }
}

$baseUrl = BASE_URL;
$error   = isset($_GET['error']);
?>

<a href="<?= $baseUrl ?>/index.php?page=detalle-encargo&id=<?= $idEncargo ?>" class="nav-back">← Volver al Detalle</a>

<div class="det-top">
  <div>
    <div class="sub">Encargo #<?= $idEncargo ?></div>
    <h1>Editar Encargo</h1>
  </div>
</div>

<?php if ($error): ?>
<div class="alerta alerta-err">
    Completá los campos obligatorios: Tipo de prenda y Fecha de entrega.
</div>
<?php endif; ?>

<form method="POST" action="<?= $baseUrl ?>/index.php?page=editar-encargo">
<input type="hidden" name="id" value="<?= $idEncargo ?>">

<div class="det-grid">

  <div>
    <div class="card" style="overflow: visible;">
      <div style="display:flex; justify-content:space-between; align-items:center;">
        <h3 style="margin-bottom:0;">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
          Cliente
        </h3>
      </div>

      <div class="form-group" style="margin-top:20px; margin-bottom:0;">
        <div class="cliente-autocomplete" style="position:relative;">
          <input type="text" id="clienteBusqueda" class="form-control" autocomplete="off"
                 placeholder="Escribí para buscar un cliente..."
                 value="<?= htmlspecialchars($clienteSeleccionado ?? '') ?>">
          <input type="hidden" name="cliente_id" id="cliente_id" value="<?= htmlspecialchars($enc['cliente_id'] ?? '') ?>">
          <div id="clienteLista" class="cliente-lista"
               style="display:none; position:absolute; top:100%; left:0; right:0; background:#fff; border:1px solid #e3d8cc; border-radius:var(--r-m); max-height:220px; overflow-y:auto; z-index:20; box-shadow:0 4px 12px rgba(0,0,0,0.08); margin-top:4px;"></div>
        </div>
      </div>
    </div>

    <div class="card">
      <h3>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline><line x1="16" y1="13" x2="8" y2="13"></line><line x1="16" y1="17" x2="8" y2="17"></line></svg>
        Detalles del Encargo
      </h3>

      <div class="form-group">
        <label for="tipo">Tipo de Prenda *</label>
        <input type="text" name="tipo" id="tipo" class="form-control" required
               placeholder="Ej: Vestido de fiesta, Pantalón, Camisa..."
               value="<?= htmlspecialchars($enc['tipo']) ?>">
      </div>

      <div class="form-group">
        <label for="descripcion">Descripción</label>
        <textarea name="descripcion" id="descripcion" class="form-control"
                  placeholder="Descripción detallada del encargo..."><?= htmlspecialchars($enc['descripcion'] ?? '') ?></textarea>
      </div>

      <div class="form-group">
        <label for="observaciones_encargo">Observaciones Especiales</label>
        <textarea name="observaciones_encargo" id="observaciones_encargo" class="form-control"
                  placeholder="Detalles importantes, preferencias del cliente..."><?= htmlspecialchars($enc['observaciones_encargo'] ?? '') ?></textarea>
      </div>

      <div class="form-group" style="margin-bottom:0;">
        <label for="fecha_entrega">Fecha de Entrega *</label>
        <input type="date" name="fecha_entrega" id="fecha_entrega" class="form-control" required
       min="<?= date('Y-m-d', strtotime($enc['created_at'])) ?>"
               value="<?= htmlspecialchars($enc['fecha_entrega']) ?>">
      </div>
    </div>
  </div>

  <div>
    <div class="card">
      <h3>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="9"></circle><polyline points="9 12 12 15 16 10"></polyline></svg>
        Estado
      </h3>
      <div class="form-group" style="margin-bottom:0;">
        <select name="estado" id="estado" class="form-control custom-select">
          <option value="pendiente"  <?= $enc['estado']==='pendiente'  ? 'selected':'' ?>>Pendiente</option>
          <option value="en_proceso" <?= $enc['estado']==='en_proceso' ? 'selected':'' ?>>En Proceso</option>
          <option value="listo"      <?= $enc['estado']==='listo'      ? 'selected':'' ?>>Listo</option>
          <option value="entregado"  <?= $enc['estado']==='entregado'  ? 'selected':'' ?>>Entregado</option>
        </select>
      </div>
    </div>

    <div class="card">
      <h3>
        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"></line><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path></svg>
        Información de Pago
      </h3>

      <div class="form-group">
        <label for="monto_total">Precio Total</label>
        <div class="input-with-prefix">
          <span class="input-prefix">$</span>
          <input type="number" name="monto_total" id="monto_total" class="form-control"
                 placeholder="0" min="0" step="0.01"
                 value="<?= htmlspecialchars($enc['monto_total'] ?? '0') ?>">
        </div>
      </div>

      <div class="form-group" style="margin-bottom:0;">
        <label for="sena">Seña / Total Pagado</label>
        <div class="input-with-prefix">
          <span class="input-prefix">$</span>
          <input type="number" name="sena" id="sena" class="form-control"
                 placeholder="0" min="0" step="0.01"
                 value="<?= htmlspecialchars($enc['sena'] ?? '0') ?>">
        </div>
      </div>
    </div>

    <div class="card">
      <div id="editar-error" style="display:none; margin-bottom:16px; padding:10px 14px; background:#fff3f3; color:#b05040; border:1px solid rgba(176,80,64,0.25); border-radius:10px; font-size:0.86rem;"></div>
      <div class="form-actions" style="margin:0; justify-content:space-between;">
        <a href="<?= $baseUrl ?>/index.php?page=detalle-encargo&id=<?= $idEncargo ?>" class="btn-cancel">Cancelar</a>
        <button type="submit" class="btn-submit">
          <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
            <polyline points="17 21 17 13 7 13 7 21"></polyline>
            <polyline points="7 3 7 8 15 8"></polyline>
          </svg>
          Guardar Cambios
        </button>
      </div>
    </div>
  </div>

</div>
</form>

<style>
  .cliente-opcion { padding: 10px 12px; cursor: pointer; font-size: 0.92rem; }
  .cliente-opcion:hover { background: #FAF3EA; }
  .cliente-opcion.vacia { color: #8B7355; cursor: default; }
</style>

<script src="<?= BASE_URL ?>/public/js/encargos/encargos.js"></script>
<script>
const CLIENTES = <?= json_encode($clientes, JSON_UNESCAPED_UNICODE) ?>;
document.addEventListener('DOMContentLoaded', () => {
  initClienteAutocomplete(CLIENTES);

  document.querySelector('form').addEventListener('submit', function(e) {
    const total = parseFloat(document.getElementById('monto_total').value) || 0;
    const sena  = parseFloat(document.getElementById('sena').value) || 0;
    const errorDiv = document.getElementById('editar-error');

    const errores = [];
    if (total > 0 && sena > total) {
      errores.push('La seña / total pagado no puede superar el precio total.');
    }

    if (errores.length > 0) {
      e.preventDefault();
      errorDiv.innerHTML = errores.map(e => `• ${e}`).join('<br>');
      errorDiv.style.display = 'block';
      errorDiv.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    } else {
      errorDiv.style.display = 'none';
    }
  });
});
</script>