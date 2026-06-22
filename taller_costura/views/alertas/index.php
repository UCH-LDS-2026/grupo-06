<?php
/**
 * views/alertas/index.php
 * Variables esperadas: $alertas (array), $adminId (int)
 */

require_once BASE_PATH . '/controllers/AlertaController.php';
$alertaCtrl = new AlertaController();
$alertas = $alertaCtrl->listar($_SESSION['admin_id'] ?? 1);
$clientasSinFicha = $alertaCtrl->obtenerClientasSinFicha($_SESSION['admin_id'] ?? 1);

function tiempoTranscurrido(string $fecha): string {
    $diff = time() - strtotime($fecha);
    if ($diff < 60)     return 'Hace un momento';
    if ($diff < 3600)   return 'Hace ' . floor($diff/60) . ' min';
    if ($diff < 86400)  return 'Hace ' . floor($diff/3600) . ' h';
    return 'Hace ' . floor($diff/86400) . ' días';
}

function iconoTipo(string $tipo): string {
    return match($tipo) {
        'vencimiento' => 'schedule',
        'pago'        => 'payments',
        'estado'      => 'check_circle',
        default       => 'notifications'
    };
}
?>

<div class="alertas-wrapper">

    <div class="alertas-header">
        <h1>Alertas</h1>
        <p>Notificaciones y avisos del sistema</p>
    </div>

    <?php if (!empty($alertas)): ?>
        <div class="alertas-acciones">
            <button class="btn-marcar-todas" onclick="marcarTodas()">
                <span class="material-symbols-outlined" style="font-size:17px;color:var(--acento-2)">done_all</span>
                Marcar todas como leídas
            </button>
        </div>
    <?php endif; ?>

    <div class="alertas-lista" id="lista-alertas">

        <?php if (empty($alertas)): ?>
            <div class="empty-state">
                <span class="material-symbols-outlined">notifications_off</span>
                <p>No hay alertas por el momento.</p>
            </div>

        <?php else: ?>
            <?php foreach ($alertas as $alerta): ?>
                <div class="alerta-card <?= $alerta['leida'] ? 'leida' : 'no-leida' ?>"
                     id="alerta-<?= $alerta['id'] ?>">

                    <?php if (!$alerta['leida']): ?>
                        <div class="dot-unread"></div>
                    <?php endif; ?>

                    <div class="alerta-icono icono-<?= htmlspecialchars($alerta['tipo']) ?>">
                        <span class="material-symbols-outlined">
                            <?= iconoTipo($alerta['tipo']) ?>
                        </span>
                    </div>

                    <div class="alerta-contenido">
                        <p class="alerta-mensaje"><?= htmlspecialchars($alerta['mensaje']) ?></p>
                        <div class="alerta-meta">

                            <span class="alerta-tipo tipo-<?= htmlspecialchars($alerta['tipo']) ?>">
                                <?= ucfirst($alerta['tipo']) ?>
                            </span>

                            <span class="alerta-tiempo">
                                <?= tiempoTranscurrido($alerta['fecha']) ?>
                            </span>

                            <?php if (!empty($alerta['tipo_encargo'])): ?>
                                <span class="alerta-encargo">
                                    <?= htmlspecialchars($alerta['tipo_encargo']) ?>
                                    <?php if (!empty($alerta['nombre_cliente'])): ?>
                                        — <?= htmlspecialchars($alerta['nombre_cliente']) ?>
                                    <?php endif; ?>
                                </span>
                            <?php endif; ?>

                        </div>
                    </div>

                    <?php if (!$alerta['leida']): ?>
                        <button class="btn-marcar"
                                onclick="marcarLeida(<?= $alerta['id'] ?>, this)">
                            Marcar leída
                        </button>
                    <?php endif; ?>

                </div>
            <?php endforeach; ?>
        <?php endif; ?>

    </div>
</div>

<div id="toast" class="toast"></div>

<script>
function marcarLeida(id, btn) {
    fetch('index.php?page=alertas&accion=marcar&id=' + id, {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            const card = document.getElementById('alerta-' + id);
            card.classList.remove('no-leida');
            card.classList.add('leida');
            const dot = card.querySelector('.dot-unread');
            if (dot) dot.remove();
            btn.remove();
            actualizarBadge(-1);
            mostrarToast('Alerta marcada como leída');
        }
    })
    .catch(() => mostrarToast('Error al actualizar', true));
}

function marcarTodas() {
    fetch('index.php?page=alertas&accion=marcar_todas', {
        method: 'POST',
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(data => {
        if (data.ok) {
            document.querySelectorAll('.alerta-card').forEach(card => {
                card.classList.remove('no-leida');
                card.classList.add('leida');
                const dot = card.querySelector('.dot-unread');
                if (dot) dot.remove();
                const btn = card.querySelector('.btn-marcar');
                if (btn) btn.remove();
            });
            actualizarBadge(0);
            mostrarToast('Todas las alertas marcadas como leídas');
        }
    })
    .catch(() => mostrarToast('Error al actualizar', true));
}

function mostrarToast(msg, esError = false) {
    if (!esError) {
        mostrarToastCampana(msg);
    } else {
        const t = document.getElementById('toast');
        if (!t) return;
        t.textContent = msg;
        t.className = 'toast toast-error';
        void t.offsetWidth;
        t.classList.add('show');
        setTimeout(() => t.classList.remove('show'), 3200);
    }
}

function actualizarBadge(cambio) {
    const badge = document.querySelector('.floating-alerts .badge');
    if (!badge) return;
    if (cambio === 0) { badge.remove(); return; }
    const nuevo = (parseInt(badge.textContent) || 0) + cambio;
    if (nuevo <= 0) badge.remove();
    else badge.textContent = nuevo;
}
</script>