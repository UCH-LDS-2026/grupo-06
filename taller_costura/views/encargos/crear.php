<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Encargo.php';

$baseUrl    = dirname($_SERVER['SCRIPT_NAME']);
$errorCrear = isset($_GET['error']);

$db   = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT id, nombre FROM cliente ORDER BY nombre");
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$clienteSeleccionado = null;
if (!empty($_POST['cliente_id'])) {
    foreach ($clientes as $c) {
        if ($c['id'] == $_POST['cliente_id']) { $clienteSeleccionado = $c['nombre']; break; }
    }
}
?>

<a href="<?= $baseUrl ?>/index.php" class="nav-back">← Volver a Agenda</a>

<div class="det-top">
  <div>
    <div class="sub">Encargo</div>
    <h1>Nuevo Encargo</h1>
  </div>
</div>

<?php if ($errorCrear): ?>
  <div class="alert-error">
    <span>⚠️</span>
    <span>Completá los campos obligatorios: Tipo de prenda y Fecha de entrega.</span>
  </div>
<?php endif; ?>

<form method="POST" action="<?= $baseUrl ?>/index.php?page=crear">
  <div class="det-grid">

    <div>
      <div class="card">
        <div style="display:flex; justify-content:space-between; align-items:center;">
          <h3 style="margin-bottom:0;">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
            Cliente
          </h3>
          <a href="<?= $baseUrl ?>/index.php?page=clientes" class="btn-ficha"
             style="margin-top:0; text-decoration:none; font-size:1.2rem; font-weight:700; color: var(--accent); line-height:1;"
             title="Agregar nuevo cliente">+!!!!!!!</a>
        </div>

        <div class="form-group" style="margin-top:20px; margin-bottom:0;">
          <div class="cliente-autocomplete" style="position:relative;">
            <input type="text" id="clienteBusqueda" class="form-control" autocomplete="off"
                   placeholder="Escribí para buscar un cliente..."
                   value="<?= htmlspecialchars($clienteSeleccionado ?? '') ?>">
            <input type="hidden" name="cliente_id" id="cliente_id" value="<?= htmlspecialchars($_POST['cliente_id'] ?? '') ?>">
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
                 value="<?= htmlspecialchars($_POST['tipo'] ?? '') ?>">
        </div>

        <div class="form-group">
          <label for="descripcion">Descripción</label>
          <textarea name="descripcion" id="descripcion" class="form-control"
                    placeholder="Descripción detallada del encargo..."><?= htmlspecialchars($_POST['descripcion'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
          <label for="observaciones_encargo">Observaciones Especiales</label>
          <textarea name="observaciones_encargo" id="observaciones_encargo" class="form-control"
                    placeholder="Detalles importantes, preferencias del cliente..."><?= htmlspecialchars($_POST['observaciones_encargo'] ?? '') ?></textarea>
        </div>

        <div class="form-group" style="margin-bottom:0;">
          <label for="fecha_entrega">Fecha de Entrega *</label>
          <input type="date" name="fecha_entrega" id="fecha_entrega" class="form-control" required
                 value="<?= htmlspecialchars($_POST['fecha_entrega'] ?? '') ?>">
        </div>
      </div>
    </div>

    <div>
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
                   value="<?= htmlspecialchars($_POST['monto_total'] ?? '') ?>">
          </div>
        </div>

        <div class="form-group">
          <label for="sena">Seña Inicial</label>
          <div class="input-with-prefix">
            <span class="input-prefix">$</span>
            <input type="number" name="sena" id="sena" class="form-control"
                   placeholder="0" min="0" step="0.01"
                   value="<?= htmlspecialchars($_POST['sena'] ?? '') ?>">
          </div>
        </div>

        <div class="form-group" style="margin-bottom:0;">
          <label for="metodo_pago">Método de Pago</label>
          <select name="metodo_pago" id="metodo_pago" class="form-control custom-select">
            <option value="efectivo">Efectivo</option>
            <option value="transferencia">Transferencia</option>
            <option value="tarjeta">Tarjeta</option>
          </select>
        </div>
      </div>

      <div class="card">
        <div class="form-actions" style="margin:0; justify-content:space-between;">
          <a href="<?= $baseUrl ?>/index.php" class="btn-cancel">Cancelar</a>
          <button type="submit" class="btn-submit">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
              <polyline points="17 21 17 13 7 13 7 21"></polyline>
              <polyline points="7 3 7 8 15 8"></polyline>
            </svg>
            Guardar
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

<script>
const CLIENTES = <?= json_encode($clientes, JSON_UNESCAPED_UNICODE) ?>;

const inputBusqueda = document.getElementById('clienteBusqueda');
const inputHidden    = document.getElementById('cliente_id');
const listaEl        = document.getElementById('clienteLista');

function renderListaClientes(filtro) {
  const texto = filtro.trim().toLowerCase();
  const filtrados = texto === '' ? CLIENTES : CLIENTES.filter(c => c.nombre.toLowerCase().includes(texto));
  let html = '<div class="cliente-opcion vacia" data-id="">Sin cliente...</div>';
  html += filtrados.length
    ? filtrados.map(c => `<div class="cliente-opcion" data-id="${c.id}" data-nombre="${c.nombre.replace(/"/g,'&quot;')}">${c.nombre}</div>`).join('')
    : '<div class="cliente-opcion vacia">Sin resultados</div>';
  listaEl.innerHTML = html;
  listaEl.style.display = 'block';
}

inputBusqueda.addEventListener('input', () => {
  inputHidden.value = '';
  renderListaClientes(inputBusqueda.value);
});
inputBusqueda.addEventListener('focus', () => renderListaClientes(inputBusqueda.value));

listaEl.addEventListener('click', (e) => {
  const opcion = e.target.closest('.cliente-opcion');
  if (!opcion) return;
  if (opcion.dataset.id) {
    inputHidden.value = opcion.dataset.id;
    inputBusqueda.value = opcion.dataset.nombre;
  } else {
    inputHidden.value = '';
    inputBusqueda.value = '';
  }
  listaEl.style.display = 'none';
});

document.addEventListener('click', (e) => {
  if (!e.target.closest('.cliente-autocomplete')) listaEl.style.display = 'none';
});
</script>