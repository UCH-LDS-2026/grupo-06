
<?php $baseUrl = dirname($_SERVER['SCRIPT_NAME']); ?>
<style>
  /* NAVEGACIÓN Y TÍTULOS */
  .nav-back { color: #8B7355; text-decoration: none; font-size: 14px; display: inline-flex;
              align-items: center; gap: 8px; margin-bottom: 24px; transition: color .2s; }
  .nav-back:hover { color: #2C1810; }

  .page-title { font-family: 'Playfair Display', serif; font-size: 36px; color: #2C1810; margin-bottom: 32px; font-weight: 500; }

  /* CONTENEDORES DE SECCIÓN SEGÚN FIGMA */
  .form-wrap { max-width: 800px; display: flex; flex-direction: column; gap: 24px; padding-bottom: 60px; }
  .form-section { background: #fff; border: 1px solid #EDE8E0; border-radius: 12px; padding: 32px; }
  .form-section h2 { font-family: 'Playfair Display', serif; font-size: 20px; color: #2C1810; margin-bottom: 28px; font-weight: 500; }

  /* ESTILO DE INPUTS Y LABELS */
  .form-group { margin-bottom: 20px; }
  .form-group:last-child { margin-bottom: 0; }
  .form-group label { display: block; font-size: 13px; font-weight: 500; color: #8B7355; margin-bottom: 10px; }
  
  .form-control { width: 100%; padding: 14px 18px; border: 1px solid #EDE8E0; border-radius: 8px;
                  background: #fff; color: #2C1810; font-size: 14px; font-family: 'Inter', sans-serif;
                  outline: none; transition: all .2s ease; }
  .form-control:focus { border-color: #7D4E2F; box-shadow: 0 0 0 4px rgba(125, 78, 47, 0.05); }
  .form-control::placeholder { color: #A69580; opacity: 0.6; }
  
  textarea.form-control { resize: vertical; min-height: 100px; }
  
  /* AYUDAS Y HINTS */
  .form-hint { font-size: 12px; color: #8B7355; margin-top: 10px; }
  .link-hint { color: #7D4E2F; font-weight: 500; text-decoration: none; }
  .link-hint:hover { text-decoration: underline; }

  /* FILAS DINÁMICAS */
  .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }

  /* BOTONES DE ACCIÓN FINALES */
  .form-actions { display: flex; justify-content: flex-end; align-items: center; gap: 20px; margin-top: 10px; }
  
  .btn-submit { background: #7D4E2F; color: #fff; padding: 14px 32px; border: none; border-radius: 8px;
                font-size: 15px; font-weight: 500; cursor: pointer; transition: background .2s; 
                display: flex; align-items: center; gap: 10px; }
  .btn-submit:hover { background: #5C3A23; }
  
  .btn-cancel { background: #FAF8F5; color: #8B7355; padding: 14px 28px; border: 1px solid #EDE8E0; 
                border-radius: 8px; text-decoration: none; font-size: 14px; transition: all .2s; }
  .btn-cancel:hover { background: #F0EDE9; color: #2C1810; }

  /* ALERTAS */
  .alert-error { background: #FFF5F5; border: 1px solid #FED7D7; color: #C53030;
                 padding: 16px; border-radius: 8px; font-size: 14px; margin-bottom: 24px; display: flex; gap: 10px; }
</style>

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
      <select name="cliente_id" id="cliente_id" class="form-control" style="appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%20fill%3D%22none%22%20stroke%3D%22%238B7355%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 15px center; background-size: 18px;">
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

    <div class="form-group" style="margin-top: 28px;">
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
      <div style="position: relative;">
        <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #8B7355; font-size: 14px;">$</span>
        <input type="number" name="monto_total" id="monto_total" class="form-control" style="padding-left: 35px;"
               placeholder="0" min="0" step="0.01"
               value="<?= htmlspecialchars($_POST['monto_total'] ?? '') ?>">
      </div>
    </div>

    <div class="form-row">
      <div class="form-group">
        <label for="sena">Seña Inicial</label>
        <div style="position: relative;">
          <span style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: #8B7355; font-size: 14px;">$</span>
          <input type="number" name="sena" id="sena" class="form-control" style="padding-left: 35px;"
                 placeholder="0" min="0" step="0.01"
                 value="<?= htmlspecialchars($_POST['sena'] ?? '') ?>">
        </div>
      </div>
      
      <div class="form-group">
        <label for="metodo_pago">Método de Pago</label>
        <select name="metodo_pago" id="metodo_pago" class="form-control" style="appearance: none; background-image: url('data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A%2F%2Fwww.w3.org%2F2000%2Fsvg%22%20width%3D%2224%22%20height%3D%2224%22%20viewBox%3D%220%200%2024%2024%20fill%3D%22none%22%20stroke%3D%22%238B7355%22%20stroke-width%3D%222%22%20stroke-linecap%3D%22round%22%20stroke-linejoin%3D%22round%22%3E%3Cpolyline%20points%3D%226%209%2012%2015%2018%209%22%3E%3C%2Fpolyline%3E%3C%2Fsvg%3E'); background-repeat: no-repeat; background-position: right 15px center; background-size: 18px;">
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