
<?php $baseUrl = dirname($_SERVER['SCRIPT_NAME']); ?>

<a href="<?= $baseUrl ?>/index.php?page=agenda" class="nav-back">← Volver a Agenda</a>
<h1 class="page-title">Nuevo Encargo</h1>

<?php if (!empty($errorCrear)): ?>
  <div class="alert-error">
    <span>⚠️</span> 
    <span>Hubo un error al guardar el encargo. Por favor, revisá los campos obligatorios.</span>
  </div>
<?php endif; ?>

<form method="POST" action="<?= $baseUrl ?>/index.php?page=nuevo-encargo" class="form-wrap">

  <!-- Información del Cliente -->
  <div class="form-section">
    <h2>Información del Cliente</h2>
    <div class="form-group">
      <label for="cliente_id">Cliente</label>
      <select name="cliente_id" id="cliente_id" class="form-control custom-select">
        <option value="">Seleccionar cliente...</option>
        <?php foreach ($clientes as $cli): ?>
          <option value="<?= $cli->getId() ?>"
            <?= (isset($_POST['cliente_id']) && $_POST['cliente_id'] == $cli->getId()) ? 'selected' : '' ?>>
            <?= htmlspecialchars($cli->getNombre()) ?>
          </option>
        <?php endforeach; ?>
      </select>
      <p class="form-hint">¿Cliente nuevo? <a href="index.php?page=clientes&action=nuevo" class="link-hint">Registrar cliente primero</a></p>
    </div>
  </div>

  <!-- Detalles del Encargo -->
  <div class="form-section">
    <h2>Detalles del Encargo</h2>

    <div class="form-group">
      <label for="tipo">Tipo de Prenda</label>
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

    <div class="form-group form-group-top">
      <label for="fecha_entrega">Fecha de Entrega</label>
      <input type="date" name="fecha_entrega" id="fecha_entrega" class="form-control" required
             value="<?= htmlspecialchars($_POST['fecha_entrega'] ?? '') ?>">
    </div>
  </div>

  <!-- Información de Pago (Ajustado a image_9731be.png) -->
  <div class="form-section">
    <h2>Información de Pago</h2>

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

  <!-- Acciones Finales con Icono de Guardar -->
  <div class="form-actions">
    <a href="<?= $baseUrl ?>/index.php?page=agenda" class="btn-cancel">Cancelar</a>
    <button type="submit" class="btn-submit">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path><polyline points="17 21 17 13 7 13 7 21"></polyline><polyline points="7 3 7 8 15 8"></polyline></svg>
      Guardar Encargo
    </button>
  </div>

</form>