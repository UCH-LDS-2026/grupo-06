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

    $baseUrl = BASE_URL;
    $error   = isset($_GET['error']);
    ?>

    <a href="<?= $baseUrl ?>/index.php?page=detalle-encargo&id=<?= $idEncargo ?>" class="nav-back">← Volver al Detalle</a>
    <h1 class="page-title">Editar Encargo #<?= $idEncargo ?></h1>

    <?php if ($error): ?>
    <div class="alert-error">
        <span>⚠️</span>
        <span>Completá los campos obligatorios: Tipo de prenda y Fecha de entrega.</span>
    </div>
    <?php endif; ?>

    <form method="POST" action="<?= $baseUrl ?>/index.php?page=editar-encargo" class="form-wrap">
    <input type="hidden" name="id" value="<?= $idEncargo ?>">

    <div class="form-section">
        <h2>Información del Cliente</h2>
        <div class="form-group">
        <label for="cliente_id">Cliente <span style="color:#8B7355;font-weight:300">(opcional)</span></label>
        <select name="cliente_id" id="cliente_id" class="form-control custom-select">
            <option value="">Sin cliente...</option>
            <?php foreach ($clientes as $cli): ?>
            <option value="<?= $cli['id'] ?>" <?= ($enc['cliente_id'] == $cli['id']) ? 'selected' : '' ?>>
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
    </div>

    <div class="form-section">
        <h2>Estado y Entrega</h2>
        
        <div class="form-group">
        <label for="estado">Estado</label>
        <select name="estado" id="estado" class="form-control custom-select">
            <option value="pendiente"  <?= $enc['estado']==='pendiente'  ? 'selected':'' ?>>Pendiente</option>
            <option value="en_proceso" <?= $enc['estado']==='en_proceso' ? 'selected':'' ?>>En Proceso</option>
            <option value="listo"      <?= $enc['estado']==='listo'      ? 'selected':'' ?>>Listo</option>
            <option value="entregado"  <?= $enc['estado']==='entregado'  ? 'selected':'' ?>>Entregado</option>
        </select>
        </div>

        <div class="form-group">
        <label for="fecha_entrega">Fecha de Entrega *</label>
        <input type="date" name="fecha_entrega" id="fecha_entrega" class="form-control" required
                value="<?= htmlspecialchars($enc['fecha_entrega']) ?>">
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
                value="<?= htmlspecialchars($enc['monto_total'] ?? '0') ?>">
        </div>
        </div>

        <div class="form-group">
        <label for="sena">Seña / Total Pagado</label>
        <div class="input-with-prefix">
            <span class="input-prefix">$</span>
            <input type="number" name="sena" id="sena" class="form-control"
                placeholder="0" min="0" step="0.01"
                value="<?= htmlspecialchars($enc['sena'] ?? '0') ?>">
        </div>
        </div>
    </div>

    <div class="form-actions">
        <a href="<?= $baseUrl ?>/index.php?page=detalle-encargo&id=<?= $idEncargo ?>" class="btn-cancel">Cancelar</a>
        <button type="submit" class="btn-submit">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M19 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11l5 5v11a2 2 0 0 1-2 2z"></path>
            <polyline points="17 21 17 13 7 13 7 21"></polyline>
            <polyline points="7 3 7 8 15 8"></polyline>
        </svg>
        Guardar Cambios
        </button>
    </div>
    </form>