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

$origen = $_GET['origen'] ?? 'agenda';
$filtro = $_GET['filtro'] ?? '';

$inicialesCliente = '';
if ($cliente) {
    $partes = explode(' ', trim($cliente['nombre']));
    $inicialesCliente = implode('', array_map(fn($p) => !empty($p) ? strtoupper($p[0]) : '', array_slice($partes, 0, 2)));
}

$dotColor = [
    'pendiente' => '#f0b800',
    'proceso'   => '#1ac880',
    'listo'     => '#7850e0',
    'entregado' => '#e050a0',
];
$currentDot = $dotColor[$enc['estado'] === 'en_proceso' ? 'proceso' : $enc['estado']] ?? '#aaa';
$estiloListo = $enc['estado'] === 'listo' ? ' box-shadow:0 0 6px rgba(120,80,220,0.5)' : '';
?>

<link rel="stylesheet" href="<?= BASE_URL ?>/public/css/encargos/detalle_encargo.css">

<a href="<?= $origen === 'pagos' ? 'index.php?page=pagos&filtro=' . urlencode($filtro) : 'index.php' ?>" class="nav-back">
    ← Volver a <?= $origen === 'pagos' ? 'Pagos' : 'Agenda' ?>
</a>

<!-- ── HEADER ── -->
<div class="det-top">
    <div>
        <div class="sub">Encargo #<?= $enc['id'] ?></div>
        <h1><?= htmlspecialchars($enc['tipo']) ?></h1>
    </div>
    <div class="det-top-acciones">
        <a href="index.php?page=editar-encargo&id=<?= $enc['id'] ?>" class="btn-det-editar">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path>
                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4z"></path>
            </svg>
            Editar
        </a>
        <button onclick="eliminarEncargo(<?= $enc['id'] ?>, <?= $totalPagado ?>)" class="btn-det-eliminar">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="3 6 5 6 21 6"></polyline>
                <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"></path>
                <path d="M10 11v6M14 11v6"></path>
                <path d="M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"></path>
            </svg>
            Eliminar
        </button>

        <span class="badge-estado-grande <?= $badgeClass ?>" id="badgeEstado">
            <span class="estado-dot-badge" style="background:<?= $currentDot ?>;<?= $estiloListo ?>"></span>
            <?= $badgeTxt ?>
        </span>
    </div>
</div>

<!-- ── GRID ── -->
<div class="det-grid">

    <!-- ── COLUMNA IZQUIERDA ── -->
    <div>

        <!-- Cliente -->
        <div class="det-card">
            <div class="det-card-titulo">
                <h3>
                    <div class="det-icono icono-cliente">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                    Cliente
                </h3>
            </div>
            <?php if ($cliente): ?>
                <div class="det-cliente">
                    <div class="det-cliente-avatar"><?= htmlspecialchars($inicialesCliente) ?></div>
                    <div class="det-cliente-info">
                        <h4><?= htmlspecialchars($cliente['nombre']) ?></h4>
                        <?php if ($cliente['telefono']): ?>
                            <p><?= htmlspecialchars($cliente['telefono']) ?></p>
                        <?php endif; ?>
                        <?php if ($cliente['email']): ?>
                            <p><?= htmlspecialchars($cliente['email']) ?></p>
                        <?php endif; ?>
                        <a href="index.php?page=ficha-cliente&id=<?= $cliente['id'] ?>" class="det-cliente-link">
                            Ver ficha completa →
                        </a>
                    </div>
                </div>
            <?php else: ?>
                <p class="det-sin-datos">Sin cliente registrado</p>
            <?php endif; ?>
        </div>

        <!-- Descripción -->
        <div class="det-card">
            <div class="det-card-titulo">
                <h3>
                    <div class="det-icono icono-descripcion">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path>
                            <polyline points="14 2 14 8 20 8"></polyline>
                            <line x1="16" y1="13" x2="8" y2="13"></line>
                            <line x1="16" y1="17" x2="8" y2="17"></line>
                        </svg>
                    </div>
                    Descripción
                </h3>
            </div>
            <?php if (!empty($enc['descripcion'])): ?>
                <p class="det-texto"><?= nl2br(htmlspecialchars($enc['descripcion'])) ?></p>
            <?php else: ?>
                <p class="det-sin-datos">Sin descripción registrada.</p>
            <?php endif; ?>
        </div>

        <!-- Observaciones Especiales -->
        <div class="det-card">
            <div class="det-card-titulo">
                <h3>
                    <div class="det-icono icono-obs-esp">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10"></circle>
                            <line x1="12" y1="8" x2="12" y2="12"></line>
                            <line x1="12" y1="16" x2="12.01" y2="16"></line>
                        </svg>
                    </div>
                    Observaciones Especiales
                </h3>
            </div>
            <?php if (!empty($enc['observaciones_encargo'])): ?>
                <div class="det-obs-especial" id="obsEspecialBox">
                    <?= nl2br(htmlspecialchars($enc['observaciones_encargo'])) ?>
                </div>
                <button onclick="eliminarObservacionEspecial(<?= $enc['id'] ?>)" class="btn-eliminar-obs">
                    Eliminar observación
                </button>
            <?php else: ?>
                <p class="det-sin-datos" id="obsEspecialVacio">Sin observaciones especiales.</p>
            <?php endif; ?>
        </div>

        <!-- Historial de Observaciones -->
        <div class="det-card" id="cardHistorialObs">
            <div class="det-card-titulo">
                <h3>
                    <div class="det-icono icono-obs-hist">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"></path>
                        </svg>
                    </div>
                    Historial de Observaciones
                </h3>
                <button onclick="abrirFormObs()" class="det-card-accion">+ Agregar</button>
            </div>

            <div id="formNuevaObs" class="det-obs-form det-obs-form--hidden">
                <textarea id="inputNuevaObs" rows="2" placeholder="Escribí una nueva observación..."></textarea>
                <div class="det-obs-form-acciones">
                    <button onclick="cancelarFormObs()" class="btn-modal-cancelar btn-obs-sm">Cancelar</button>
                    <button onclick="guardarNuevaObs()" class="btn-modal-confirmar btn-obs-sm">Guardar</button>
                </div>
            </div>

            <div id="listaHistorialObs">
                <?php if (!empty($observaciones)): ?>
                    <?php foreach ($observaciones as $obs): ?>
                        <div class="det-obs-item" id="obs-<?= $obs['id'] ?>">
                            <div class="det-obs-item-texto">
                                <?= htmlspecialchars($obs['detalle']) ?>
                                <div class="det-obs-fecha"><?= date('d/m/Y H:i', strtotime($obs['fecha'])) ?></div>
                            </div>
                            <button onclick="eliminarObsHistorial(<?= $obs['id'] ?>)" class="btn-obs-eliminar" title="Eliminar">✕</button>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="det-sin-datos" id="sinObsMsg">Sin observaciones registradas.</p>
                <?php endif; ?>
            </div>
        </div>

        <!-- Estado -->
        <div class="det-card">
            <div class="det-card-titulo">
                <h3>
                    <div class="det-icono icono-estado">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <polyline points="9 11 12 14 22 4"></polyline>
                            <path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"></path>
                        </svg>
                    </div>
                    Estado del Encargo
                </h3>
            </div>
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

    </div><!-- /col izquierda -->

    <!-- ── COLUMNA DERECHA ── -->
    <div>

        <!-- Fechas -->
        <div class="det-card-sm">
            <div class="det-card-titulo">
                <h3>
                    <div class="det-icono icono-fechas">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="4" width="18" height="18" rx="2"></rect>
                            <line x1="16" y1="2" x2="16" y2="6"></line>
                            <line x1="8" y1="2" x2="8" y2="6"></line>
                            <line x1="3" y1="10" x2="21" y2="10"></line>
                        </svg>
                    </div>
                    Fechas
                </h3>
            </div>
            <div class="det-fecha-item">
                <span class="det-fecha-label">Fecha de Encargo</span>
                <span class="det-fecha-valor"><?= fmtFecha($enc['created_at'], $meses) ?></span>
            </div>
            <div class="det-fecha-item">
                <span class="det-fecha-label">Fecha de Entrega</span>
                <span class="det-fecha-valor"><?= fmtFecha($enc['fecha_entrega'], $meses) ?></span>
            </div>
        </div>

        <!-- Resumen Pagos -->
        <div class="det-card-sm">
            <div class="det-card-titulo">
                <h3>
                    <div class="det-icono icono-pagos">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="1" x2="12" y2="23"></line>
                            <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                        </svg>
                    </div>
                    Resumen de Pagos
                </h3>
            </div>
            <div class="det-pago-row">
                <span>Precio Total</span>
                <strong><?= fmtMonto($enc['monto_total']) ?></strong>
            </div>
            <div class="det-pago-row">
                <span>Total Pagado</span>
                <span id="spanTotalPagado" class="span-pagado"><?= fmtMonto($totalPagado) ?></span>
            </div>
            <div class="det-pago-row total">
                <span>Saldo Pendiente</span>
                <span class="det-saldo" id="spanSaldo"><?= fmtMonto($saldo) ?></span>
            </div>
            <div class="det-progreso-wrap">
                <div class="det-progreso-labels">
                    <span>Progreso de pago</span>
                    <span class="det-progreso-pct" id="spanPorcentaje"><?= $porcentaje ?>%</span>
                </div>
                <div class="det-progreso-bar">
                    <div class="det-progreso-fill" id="progresoFill"
                         style="width:<?= $porcentaje ?>%"
                         data-total="<?= (float)$enc['monto_total'] ?>"></div>
                </div>
            </div>
            <?php if ($saldo > 0): ?>
                <button class="btn-registrar-pago" id="btnAbrirPago">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    Registrar Pago
                </button>
            <?php endif; ?>
        </div>

        <!-- Historial Pagos -->
        <div class="det-card-sm det-card-sm--hidden" id="cardHistorial" <?= !empty($historialPagos) ? 'style="display:block;"' : '' ?>>
            <div class="det-historial-toggle" onclick="toggleHistorialPagos()">
                <h3>Historial de Pagos</h3>
                <span class="det-historial-icon" id="historial-toggle-icon">↓</span>
            </div>
            <div id="historial-pagos-lista" class="historial-pagos-lista--hidden">
                <?php foreach ($historialPagos as $p): ?>
                    <div class="det-pago-hist-item" id="pago-<?= $p['id'] ?>">
                        <div class="det-pago-hist-icon">✓</div>
                        <div class="det-pago-hist-info">
                            <strong><?= fmtMonto($p['monto']) ?></strong>
                            <span><?= fmtFecha($p['fecha'], $meses) ?></span>
                            <span class="det-metodo-tag"><?= ucfirst($p['metodo']) ?></span>
                            <?php if (!empty($p['nota'])): ?>
                                <span class="det-pago-nota"><?= htmlspecialchars($p['nota']) ?></span>
                            <?php endif; ?>
                        </div>
                        <button onclick="eliminarPago(<?= $p['id'] ?>, <?= $p['monto'] ?>, <?= $idEncargo ?>)"
                                class="btn-obs-eliminar" title="Eliminar pago">✕</button>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

    </div><!-- /col derecha -->

</div><!-- /det-grid -->

<!-- ── Modal Registrar Pago ── -->
<div class="modal-overlay modal-overlay--hidden" id="modalPago" onclick="if(event.target===this) cerrarModalPago()">
    <div class="modal-box">
        <div class="modal-header-det">
            <h3>Registrar Pago</h3>
            <button onclick="cerrarModalPago()" class="modal-close-det">&times;</button>
        </div>
        <div class="modal-encargo-resumen">
            <strong><?= htmlspecialchars($enc['tipo']) ?></strong><br>
            <?php if ($cliente): ?><span><?= htmlspecialchars($cliente['nombre']) ?></span><br><?php endif; ?>
            Total: <strong><?= fmtMonto($enc['monto_total']) ?></strong> &nbsp;·&nbsp;
            Pagado: <strong><?= fmtMonto($totalPagado) ?></strong><br>
            Saldo: <span class="modal-saldo-val" id="modalSaldo"><?= fmtMonto($saldo) ?></span>
        </div>
        <div class="modal-form-group">
            <label>Monto a registrar</label>
            <input type="number" id="inputMonto" placeholder="Ej: 3000" min="1" step="1" oninput="validarMontoDetalle(this)">
            <div class="modal-hint" id="inputMontoHint"></div>
        </div>
        <div class="modal-form-group">
            <label>Método de pago</label>
            <div class="modal-metodos">
                <label class="modal-metodo-opcion"><input type="radio" name="detalle_metodo_pago" value="efectivo" checked> Efectivo</label>
                <label class="modal-metodo-opcion"><input type="radio" name="detalle_metodo_pago" value="transferencia"> Transferencia</label>
                <label class="modal-metodo-opcion"><input type="radio" name="detalle_metodo_pago" value="tarjeta"> Tarjeta</label>
            </div>
        </div>
        <div class="modal-form-group">
            <label>Nota (opcional)</label>
            <input type="text" id="inputNota" placeholder="Ej: Seña 50%">
        </div>
        <div class="modal-footer-det">
            <button class="btn-modal-cancelar" onclick="cerrarModalPago()">Cancelar</button>
            <button class="btn-modal-confirmar" id="btnConfirmarPagoDetalle">
                Confirmar Pago
                <span id="spinnerPago" class="spinner--hidden">⏳</span>
            </button>
        </div>
    </div>
</div>

<div id="toast" class="toast"></div>

<div id="detalle-meta"
     data-saldo="<?= $saldo ?>"
     data-monto-total="<?= (float)$enc['monto_total'] ?>"
     style="display:none;"></div>

<script src="<?= BASE_URL ?>/public/js/encargos/encargos.js"></script>
<script src="<?= BASE_URL ?>/public/js/encargos/detalle.js"></script>