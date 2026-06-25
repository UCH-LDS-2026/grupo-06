<?php
/**
 * views/pagos/index.php
 * 
 * Variables esperadas (extraídas por PagoController::index() via extract()):
 *   float  $totalCobrado
 *   float  $saldoPendienteTotal
 *   float  $totalSenas
 *   int    $cuentasCount
 *   array  $cuentasPorCobrar
 *   array  $historialPagos
 *   string $tabActiva          ('cuentas' | 'historial')
 *   array|null $flash          ['ok' => bool, 'mensaje' => string]
 * 
 * Se incluye dentro del layout (header.php + footer.php).
 */
 
// Helper de formato
function formatPesos(float $n): string {
    return '$' . number_format($n, 0, ',', '.');
}
 
// Etiqueta de estado
function etiquetaEstado(string $estado): string {
    $mapa = [
        'pendiente'  => ['label' => 'Pendiente',   'class' => 'badge-pendiente'],
        'en_proceso' => ['label' => 'En proceso',  'class' => 'badge-proceso'],
        'listo'      => ['label' => 'Listo para entregar', 'class' => 'badge-listo'],
        'entregado'  => ['label' => 'Entregado',   'class' => 'badge-entregado'],
    ];
    $info = $mapa[$estado] ?? ['label' => ucfirst($estado), 'class' => 'badge-pendiente'];
    return '<span class="badge ' . $info['class'] . '">' . $info['label'] . '</span>';
}
?>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@24,400,0,0" />
<div class="pagos-wrapper">
 
    <!-- ── Encabezado ─────────────────────────────────── -->
    <div class="pagos-header">
        <h1>Gestión de Pagos</h1>
        <p>Administración de cobros y cuentas por cobrar</p>
    </div>
 
    <!-- ── Flash message (fallback no-AJAX) ──────────── -->
    <?php if (!empty($flash)): ?>
        <div class="flash-msg <?= $flash['ok'] ? 'flash-ok' : 'flash-error' ?>">
            <?= $flash['ok'] ? '✓' : '✗' ?>
            <?= htmlspecialchars($flash['mensaje']) ?>
        </div>
    <?php endif; ?>
 
   <!-- ── Tarjetas de resumen ───────────────────────── -->
<div class="pagos-stats">

    <div class="stat-card stat-card--estemes">
        <div class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                <polyline points="17 6 23 6 23 12"/>
            </svg>
        </div>
        <div class="stat-text">
            <span class="stat-lbl">Este mes</span>
            <span class="stat-val"><?= formatPesos((float)($resumenMensual['cobrado_este_mes'] ?? 0)) ?></span>
            <?php
            $diferencia = (float)($resumenMensual['cobrado_este_mes'] ?? 0) - (float)($resumenMensual['cobrado_mes_anterior'] ?? 0);
            $positivo = $diferencia >= 0;
            ?>
            <span class="stat-diferencia <?= $positivo ? 'positivo' : 'negativo' ?>">
                <?= $positivo ? '↑' : '↓' ?> <?= formatPesos(abs($diferencia)) ?> vs mes anterior
            </span>
        </div>
    </div>

    <div class="stat-card stat-card--anterior">
        <div class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <line x1="12" y1="1" x2="12" y2="23"/>
                <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
            </svg>
        </div>
        <div class="stat-text">
            <span class="stat-lbl">Mes anterior</span>
            <span class="stat-val"><?= formatPesos((float)($resumenMensual['cobrado_mes_anterior'] ?? 0)) ?></span>
        </div>
    </div>

    <div class="stat-card stat-card--pendiente">
        <div class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
        </div>
        <div class="stat-text">
            <span class="stat-lbl">Saldo Pendiente</span>
            <span class="stat-val stat-val--danger"><?= formatPesos($saldoPendienteTotal) ?></span>
        </div>
    </div>

    <div class="stat-card stat-card--cuentas">
        <div class="stat-icon">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="10"/>
                <polyline points="12 6 12 12 16 14"/>
            </svg>
        </div>
        <div class="stat-text">
            <span class="stat-lbl">Cuentas por Cobrar</span>
            <span class="stat-val"><?= $cuentasCount ?></span>
        </div>
    </div>

</div><!-- /.pagos-stats -->

        <!-- ── Filtros ───────────────────────────────────── -->
    <!-- ── Filtros ───────────────────────────────────── -->
<div class="ag-buscador-bar">
    <div class="toolbar">
        <div class="search-wrap">
            <span class="material-symbols-outlined search-icon">search</span>
            <input type="text" id="filtro-cliente" placeholder="Buscar por encargo, cliente o fecha..."
                oninput="filtrarHistorial()">
            <button type="button" class="search-cal-btn" id="pago-cal-btn"
                title="Filtrar por rango de fechas"
                onclick="toggleCalendarioPago()">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="16" height="16">
                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/>
                    <line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </button>
            <div class="enc-date-picker" id="pago-date-picker">
                <label>Desde</label>
                <input type="date" id="filtro-desde" onchange="filtrarHistorial()">
                <label>Hasta</label>
                <input type="date" id="filtro-hasta" onchange="filtrarHistorial()">
            </div>
        </div>
        <button type="button" class="btn-filtro-pill" id="btn-sin-retirar" 
            onclick="toggleSinRetirar(this)">
            ⚠ Sin retirar
        </button>
        <button type="button" class="filtro-btn" id="pago-limpiar-btn"
            style="display:none;" onclick="limpiarFiltros()">
            ✕ Limpiar
        </button>
    </div>
</div>

    <!-- ── Tabs ──────────────────────────────────────── -->
    <div class="pagos-tabs">
        <button class="tab-btn <?= $tabActiva === 'cuentas'   ? 'active' : '' ?>"
                onclick="cambiarTab('cuentas', this)">
            Cuentas por Cobrar
        </button>
        <button class="tab-btn <?= $tabActiva === 'historial' ? 'active' : '' ?>"
                onclick="cambiarTab('historial', this)">
            Resumen Mensual
        </button>
    </div>
 
    <!-- ── Tab: Cuentas por Cobrar ───────────────────── -->
    <div id="tab-cuentas" class="tab-panel <?= $tabActiva === 'cuentas' ? 'active' : '' ?>">
        <?php if (empty($cuentasPorCobrar)): ?>
            <div class="empty-state">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none"
                     stroke="#C0B0A0" stroke-width="1.5">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="8" y1="12" x2="16" y2="12"/>
                </svg>
                <p>No hay cuentas pendientes por cobrar.</p>
            </div>
        <?php else: ?>
            <div class="encargo-list">
            <?php foreach ($cuentasPorCobrar as $e): ?>
               <div class="encargo-card historial-item"
                    data-cliente="<?= strtolower(htmlspecialchars($e['cliente_nombre'] ?? '')) ?>"
                     data-tipo="<?= strtolower(htmlspecialchars($e['tipo'] ?? '')) ?>"
                    data-fecha="<?= $e['fecha_entrega'] ?>"
                   onclick="window.location.href='index.php?page=detalle-encargo&id=<?= $e['id'] ?>&origen=pagos&filtro=' + encodeURIComponent(document.getElementById('filtro-cliente').value)"
                    style="cursor: pointer;"> 
 
                    <!-- Fila superior -->
                    <div class="encargo-card-top">
                        <div class="encargo-card-top-left">
                            <div>
                                <p class="encargo-tipo"><?= htmlspecialchars($e['tipo']) ?></p>
                                <p class="encargo-cliente">
                                    <?= $e['cliente_nombre']
                                        ? htmlspecialchars($e['cliente_nombre'])
                                        : '<em style="opacity:.6">Sin cliente registrado</em>' ?>
                                </p>
                                <p class="encargo-fecha">
                                    Entrega: <?= date('d/m/Y', strtotime($e['fecha_entrega'])) ?>
                                    <?php if (strtotime($e['fecha_entrega']) < strtotime('today')): ?>
                                        <span class="badge-sin-retirar"> Sin retirar</span>
                                    <?php endif; ?>
                                </p>
                            </div>
                            <?= etiquetaEstado($e['estado']) ?>
                        </div>
 
                        <button class="btn-registrar"
                                onclick="event.stopPropagation(); abrirModal(
                                    <?= $e['id'] ?>,
                                    '<?= addslashes(htmlspecialchars($e['tipo'])) ?>',
                                    '<?= addslashes(htmlspecialchars($e['cliente_nombre'] ?? 'Sin nombre')) ?>',
                                    <?= $e['monto_total'] ?>,
                                    <?= $e['sena'] ?>,
                                    <?= $e['saldo_pendiente'] ?>
                                )">
                            <svg width="13" height="13" viewBox="0 0 24 24" fill="none"
                                 stroke="currentColor" stroke-width="2.5">
                                <line x1="12" y1="5" x2="12" y2="19"/>
                                <line x1="5" y1="12" x2="19" y2="12"/>
                            </svg>
                            Registrar Pago
                        </button>
                    </div>
 
                    <!-- Barra de progreso -->
                    <div class="progreso-row">
                        <span class="progreso-label">
                            Pagado: <?= formatPesos((float)$e['sena']) ?>
                        </span>
                        <span class="progreso-pct"><?= $e['porcentaje_pagado'] ?>%</span>
                    </div>
                    <div class="progreso-bar-bg">
                        <div class="progreso-bar-fill"
                             style="width: <?= min(100, (int)$e['porcentaje_pagado']) ?>%">
                        </div>
                    </div>
 
                    <!-- Pie -->
                    <div class="encargo-card-footer">
                        <div class="encargo-footer-info">
                            <span>Precio Total: <strong><?= formatPesos((float)$e['monto_total']) ?></strong></span>
                        </div>
                        <div style="text-align:right">
                            <span class="saldo-pendiente-label">Saldo Pendiente</span>
                            <span class="saldo-pendiente-valor"><?= formatPesos((float)$e['saldo_pendiente']) ?></span>
                        </div>
                    </div>
 
                </div><!-- /.encargo-card -->
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div><!-- /#tab-cuentas -->
 
    <div id="tab-historial" class="tab-panel <?= $tabActiva === 'historial' ? 'active' : '' ?>">
    <?php if (empty($resumenPorMes)): ?>
        <div class="empty-state">
            <p>No hay pagos registrados todavía.</p>
        </div>
    <?php else: ?>
        <?php
        $meses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
        $maxTotal = max(array_column($resumenPorMes, 'total'));
        ?>
        <div class="encargo-list">
            <?php foreach ($resumenPorMes as $r): ?>
            <div class="encargo-card" style="cursor:default;">
                <div class="resumen-mes-label">
                    <span class="resumen-mes-nombre"><?= $meses[(int)$r['mes']] ?> <?= $r['anio'] ?></span>
                    <span class="resumen-mes-total"><?= formatPesos((float)$r['total']) ?></span>
                </div>
                <div class="resumen-barra-bg" style="margin-top:10px;">
                    <div class="resumen-barra-fill" style="width: <?= $maxTotal > 0 ? round(($r['total'] / $maxTotal) * 100) : 0 ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div><!-- /#tab-historial -->
 
<!-- ══════════════════════════════════════════════════
     MODAL — Registrar Pago
══════════════════════════════════════════════════ -->
<div id="modalPago" class="modal-overlay" onclick="cerrarModalSiFondo(event)">
    <div class="modal-box">
 
        <div class="modal-header">
            <h2>Registrar Pago</h2>
            <button class="modal-close" onclick="cerrarModal()" title="Cerrar">&times;</button>
        </div>
 
        <div class="modal-body">
            <!-- Info del encargo -->
            <div class="modal-encargo-info">
                <p>
                    <strong id="modal-tipo">—</strong><br>
                    <span id="modal-cliente">—</span><br>
                    Total: <strong id="modal-total">—</strong> &nbsp;|&nbsp;
                    Seña cobrada: <strong id="modal-sena">—</strong><br>
                    Saldo pendiente: <strong id="modal-saldo" style="color:var(--pago-danger)">—</strong>
                </p>
            </div>
 
            <!-- Campo monto -->
            <div class="form-group">
                <label for="modal-monto">Monto a registrar</label>
                <input type="number" id="modal-monto" min="1" step="0.01"
                       placeholder="Ej: 3000"
                       oninput="validarMonto(this)">
                <div id="modal-monto-hint" class="hint"></div>
                <div class="form-group">
    <label>Método de pago</label>
    <div class="metodo-pago-opciones">
                <label class="metodo-opcion">
                <input type="radio" name="metodo_pago" value="efectivo" checked>
                <span>Efectivo</span>
            </label>
            <label class="metodo-opcion">
                <input type="radio" name="metodo_pago" value="transferencia">
                <span>Transferencia</span>
            </label>
            <label class="metodo-opcion">
                <input type="radio" name="metodo_pago" value="tarjeta">
                <span>Tarjeta</span>
            </label>   
    </div>
</div>
            </div>
        </div>
 
        <div class="modal-footer">
            <button class="btn-cancelar" onclick="cerrarModal()">Cancelar</button>
            <button class="btn-confirmar" id="btn-confirmar" onclick="enviarPago()">
                Confirmar
                <span class="spinner" id="spinner-pago"></span>
            </button>
        </div>
 
    </div>
</div>
 
<!-- Toast -->
<div id="toast" class="toast"></div>

<script src="<?= BASE_URL ?>/public/js/pagos/pagos.js"></script>
 