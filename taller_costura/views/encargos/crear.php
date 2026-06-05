<?php
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Encargo.php';

$baseUrl = dirname($_SERVER['SCRIPT_NAME']);
$errorCrear = null;


// Reemplazar el array hardcodeado por:
$db = Database::getInstance()->getConnection();
$stmt = $db->query("SELECT id, nombre FROM cliente ORDER BY nombre");
$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<a href="<?= $baseUrl ?>/index.php" class="nav-back">← Volver a Agenda</a>
<h1 class="page-title">Nuevo Encargo</h1>

<?php if ($errorCrear): ?>
  <div class="alert-error">
    <span>⚠️</span>
    <span>Completá los campos obligatorios: Tipo de prenda y Fecha de entrega.</span>
  </div>
<?php endif; ?>

<form method="POST" action="<?= $baseUrl ?>/index.php?page=crear" class="form-wrap">

  <div class="form-section">
    <h2>Información del Cliente</h2>
    <div class="form-group">
      <label for="cliente_id">Cliente <span style="color:#8B7355;font-weight:300">(opcional)</span></label>
      <select name="cliente_id" id="cliente_id" class="form-control custom-select">
        <option value="">Sin cliente...</option>
        <?php foreach ($clientes as $cli): ?>
          <option value="<?= $cli['id'] ?>"
            <?= (isset($_POST['cliente_id']) && $_POST['cliente_id'] == $cli['id']) ? 'selected' : '' ?>>
            <?= htmlspecialchars($cli['nombre']) ?>
          </option>
        <?php endforeach; ?>
      </select>
    </div>
  </div>

  <div class="form-section">
    <h2>Detalles del Encargo</h2>

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

    <div class="form-group">
      <label for="fecha_entrega">Fecha de Entrega *</label>
      <input type="date" name="fecha_entrega" id="fecha_entrega" class="form-control" required
             value="<?= htmlspecialchars($_POST['fecha_entrega'] ?? '') ?>">
    </div>
  </div>

  <div class="form-section">
    <h2>Información de Pago <span style="color:#8B7355;font-weight:300;font-size:0.85em">(opcional)</span></h2>

    <div class="form-group">
      <label for="monto_total">Precio Total</label>
      <div class="input-with-prefix">
        <span class="input-prefix">$</span>
        <input type="number" name="monto_total" id="monto_total" class="form-control"
               placeholder="0" min="0" step="0.01"
               value="<?= htmlspecialchars($_POST['monto_total'] ?? '') ?>">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="sena">Seña Inicial</label>
        <div class="input-with-prefix">
          <span class="input-prefix">$</span>
          <input type="number" name="sena" id="sena" class="form-control"
                 placeholder="0" min="0" step="0.01"
                 value="<?= htmlspecialchars($_POST['sena'] ?? '') ?>">
        </div>
      </div>

      <div class="form-group">
        <label for="metodo_pago">Método de Pago</label>
        <select name="metodo_pago" id="metodo_pago" class="form-control custom-select">
          <option value="efectivo">Efectivo</option>
          <option value="transferencia">Transferencia</option>
          <option value="tarjeta">Tarjeta</option>
        </select>
      </div>
    </div>
  </div>

  <div class="form-actions">
    <a href="<?= $baseUrl ?>/index.php" class="btn-cancel">Cancelar</a>
    <button type="submit" class="btn-submit">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
        <polyline points="17 21 17 13 7 13 7 21"></polyline>
        <polyline points="7 3 7 8 15 8"></polyline>
      </svg>
      Guardar Encargo
    </button>
  </div>

</form>