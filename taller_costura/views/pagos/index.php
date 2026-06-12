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
 
        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="23 6 13.5 15.5 8.5 10.5 1 18"/>
                        <polyline points="17 6 23 6 23 12"/>
                    </svg>
                </div>
                Total Cobrado
            </div>
            <div class="stat-value"><?= formatPesos($totalCobrado) ?></div>
        </div>
 
        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="8" x2="12" y2="12"/>
                        <line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                </div>
                Saldo Pendiente
            </div>
            <div class="stat-value highlight"><?= formatPesos($saldoPendienteTotal) ?></div>
        </div>
 
        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <line x1="12" y1="1" x2="12" y2="23"/>
                        <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>
                    </svg>
                </div>
                Total Señas
            </div>
            <div class="stat-value"><?= formatPesos($totalSenas) ?></div>
        </div>
 
        <div class="stat-card">
            <div class="stat-card-top">
                <div class="stat-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="10"/>
                        <polyline points="12 6 12 12 16 14"/>
                    </svg>
                </div>
                Cuentas por Cobrar
            </div>
            <div class="stat-value"><?= $cuentasCount ?></div>
        </div>
 
    </div><!-- /.pagos-stats -->
 
    <!-- ── Tabs ──────────────────────────────────────── -->
    <div class="pagos-tabs">
        <button class="tab-btn <?= $tabActiva === 'cuentas'   ? 'active' : '' ?>"
                onclick="cambiarTab('cuentas', this)">
            Cuentas por Cobrar
        </button>
        <button class="tab-btn <?= $tabActiva === 'historial' ? 'active' : '' ?>"
                onclick="cambiarTab('historial', this)">
            Historial de Pagos
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
                <div class="encargo-card">
 
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
                                </p>
                            </div>
                            <?= etiquetaEstado($e['estado']) ?>
                        </div>
 
                        <button class="btn-registrar"
                                onclick="abrirModal(
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
 
    <!-- ── Tab: Historial ───────────────────────────── -->
    <div id="tab-historial" class="tab-panel <?= $tabActiva === 'historial' ? 'active' : '' ?>">
        <?php if (empty($historialPagos)): ?>
            <div class="empty-state">
                <svg width="40" height="40" viewBox="0 0 24 24" fill="none"
                     stroke="#C0B0A0" stroke-width="1.5">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="8" y1="12" x2="16" y2="12"/>
                </svg>
                <p>Todavía no hay encargos entregados o saldados.</p>
            </div>
        <?php else: ?>
            <div class="encargo-list">
            <?php foreach ($historialPagos as $e): ?>
                <div class="encargo-card">
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
                                </p>
                            </div>
                            <?= etiquetaEstado($e['estado']) ?>
                        </div>
                    </div>
                    <div class="encargo-card-footer">
                        <div class="encargo-footer-info">
                            <span>Total: <strong><?= formatPesos((float)$e['monto_total']) ?></strong></span>
                            <span>Seña cobrada: <strong><?= formatPesos((float)$e['sena']) ?></strong></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div><!-- /#tab-historial -->
 
</div><!-- /.pagos-wrapper -->
 
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
 
<!-- ══════════════════════════════════════════════════
     JS
══════════════════════════════════════════════════ -->
<script>
/* Estado del modal */
let modalData = { encargoId: 0, saldoPendiente: 0 };
 
/* ── Tabs ─────────────────────────────────────────── */
function cambiarTab(tab, btn) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + tab).classList.add('active');
    btn.classList.add('active');
}
 
/* ── Modal ────────────────────────────────────────── */
function abrirModal(id, tipo, cliente, total, sena, saldo) {
    modalData = { encargoId: id, saldoPendiente: parseFloat(saldo) };
 
    document.getElementById('modal-tipo').textContent    = tipo;
    document.getElementById('modal-cliente').textContent = cliente;
    document.getElementById('modal-total').textContent   = formatPesos(total);
    document.getElementById('modal-sena').textContent    = formatPesos(sena);
    document.getElementById('modal-saldo').textContent   = formatPesos(saldo);
 
    const input = document.getElementById('modal-monto');
    input.value = '';
    input.max   = saldo;
 
    document.getElementById('modal-monto-hint').textContent = '';
    document.getElementById('modal-monto-hint').className   = 'hint';
    document.getElementById('btn-confirmar').disabled = false;
 
    document.getElementById('modalPago').classList.add('open');
    setTimeout(() => input.focus(), 80);
}
 
function cerrarModal() {
    document.getElementById('modalPago').classList.remove('open');
}
 
function cerrarModalSiFondo(e) {
    if (e.target === document.getElementById('modalPago')) cerrarModal();
}
 
/* ── Validación en tiempo real ───────────────────── */
function validarMonto(input) {
    const hint = document.getElementById('modal-monto-hint');
    const val  = parseFloat(input.value);
    const btn  = document.getElementById('btn-confirmar');
 
    if (isNaN(val) || val <= 0) {
        hint.textContent = 'Ingresá un monto mayor a cero.';
        hint.className   = 'hint error';
        btn.disabled = true;
    } else if (val > modalData.saldoPendiente) {
        hint.textContent = 'El monto no puede superar el saldo pendiente (' + formatPesos(modalData.saldoPendiente) + ').';
        hint.className   = 'hint error';
        btn.disabled = true;
    } else {
        hint.textContent = '';
        hint.className   = 'hint';
        btn.disabled = false;
    }
}
 
/* ── Envío AJAX ──────────────────────────────────── */
function enviarPago() {
    const monto = parseFloat(document.getElementById('modal-monto').value);
    if (!monto || monto <= 0 || monto > modalData.saldoPendiente) return;
 
    const btn     = document.getElementById('btn-confirmar');
    const spinner = document.getElementById('spinner-pago');
    btn.disabled        = true;
    spinner.style.display = 'inline-block';
 
    const fd = new FormData();
    fd.append('encargo_id', modalData.encargoId);
    fd.append('monto',      monto);
 
    fetch('index.php?page=pagos&accion=registrar', {
        method:  'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        body:    fd
    })
    .then(r => r.json())
    .then(data => {
        cerrarModal();
        mostrarToast(data.mensaje, data.ok ? 'ok' : 'error');
        if (data.ok) {
            // Recargar la página tras un momento para reflejar el nuevo estado
            setTimeout(() => location.reload(), 1200);
        } else {
            btn.disabled        = false;
            spinner.style.display = 'none';
        }
    })
    .catch(() => {
        cerrarModal();
        mostrarToast('Error de conexión. Intentá de nuevo.', 'error');
        btn.disabled        = false;
        spinner.style.display = 'none';
    });
}
 
function mostrarToast(msg, tipo) {
    // Toast local de pagos
    const t = document.getElementById('toast');
    if (t) {
        t.textContent = msg;
        t.className = 'toast' + (tipo === 'error' ? ' toast-error' : '');
        void t.offsetWidth;
        t.classList.add('show');
        setTimeout(() => t.classList.remove('show'), 3200);
    }

    // Toast campana (solo si es éxito)
    if (tipo !== 'error') {
        mostrarToastCampana(msg);
    }
}

function mostrarToastCampana(msg) {
    const toast = document.getElementById('toast-campana');
    const msgEl = document.getElementById('toast-campana-msg');
    if (!toast || !msgEl) return;

    msgEl.textContent = msg;
    toast.style.display = 'flex';
    void toast.offsetWidth;
    toast.classList.add('visible');

    setTimeout(() => {
        toast.classList.remove('visible');
        setTimeout(() => toast.style.display = 'none', 300);
    }, 3500);
}
 
/* ── Helper formato ──────────────────────────────── */
function formatPesos(n) {
    return '$' + Number(n).toLocaleString('es-AR');
}
 
/* ── Cerrar modal con Escape ─────────────────────── */
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') cerrarModal();
});
</script>
 