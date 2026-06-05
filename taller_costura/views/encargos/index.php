<?php
require_once __DIR__ . '/../../config/config.php';
// AuthController::requiereLogin();

$encargoModel = new Encargo($db->getConnection());
$todos = $encargoModel->getAll()->fetchAll(PDO::FETCH_ASSOC);

// Fecha en español
$meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
$dias  = ['domingo','lunes','martes','miércoles','jueves','viernes','sábado'];
$fechaHoy = $dias[date('w')] . ', ' . date('d') . ' de ' . $meses[date('n')-1] . ' de ' . date('Y');

// Estadísticas
$estadisticas = [
    'activos'    => count(array_filter($todos, fn($e) => in_array($e['estado'], ['pendiente', 'en_proceso']))),
    'en_proceso' => count(array_filter($todos, fn($e) => $e['estado'] === 'en_proceso')),
    'listos'     => count(array_filter($todos, fn($e) => $e['estado'] === 'listo')),
    'senas'      => number_format(array_sum(array_column($todos, 'sena')), 0, ',', '.'),
    'cobrado'    => number_format(array_sum(array_column($todos, 'monto_total')), 0, ',', '.'),
    'pendiente'  => number_format(
        array_sum(array_column($todos, 'monto_total')) - array_sum(array_column($todos, 'sena')),
        0, ',', '.'
    ),
];

$proximasEntregas = array_filter($todos, fn($e) => $e['estado'] !== 'entregado');
?>

<style>
    .agenda-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-start;
        margin-bottom: 40px;
    }

    .agenda-title h1 {
        font-family: 'Playfair Display', serif;
        font-size: 32px;
        color: #2C1810;
        margin-bottom: 8px;
    }

    .agenda-title p {
        color: #8B7355;
        font-size: 14px;
    }

    .btn-nuevo {
        background: #7D4E2F;
        color: white;
        padding: 12px 24px;
        border-radius: 8px;
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 14px;
        font-weight: 500;
        transition: background 0.2s;
    }

    .btn-nuevo:hover { background: #5C3A23; }

    .stats-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 20px;
        margin-bottom: 48px;
    }

    .stat-card {
        background: white;
        padding: 24px;
        border-radius: 12px;
        border: 1px solid #EDE8E0;
    }

    .stat-value {
        font-family: 'Playfair Display', serif;
        font-size: 28px;
        color: #2C1810;
        margin-bottom: 4px;
    }

    .stat-label {
        font-size: 12px;
        color: #8B7355;
        text-transform: capitalize;
    }

    .stat-money { color: #7D4E2F; }

    .section-title {
        font-family: 'Playfair Display', serif;
        font-size: 24px;
        color: #2C1810;
        margin-bottom: 24px;
    }

    .entrega-card {
        background: white;
        border-radius: 12px;
        border: 1px solid #EDE8E0;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 24px;
        margin-bottom: 16px;
    }

    .entrega-fecha {
        text-align: center;
        min-width: 60px;
        padding-right: 24px;
        border-right: 1px solid #EDE8E0;
    }

    .entrega-fecha .dia {
        font-family: 'Playfair Display', serif;
        font-size: 24px;
        display: block;
        color: #2C1810;
    }

    .entrega-fecha .mes {
        font-size: 12px;
        color: #8B7355;
        text-transform: uppercase;
    }

    .entrega-info { flex: 1; }

    .entrega-info h3 {
        font-size: 16px;
        color: #2C1810;
        margin-bottom: 4px;
    }

    .entrega-info p {
        font-size: 13px;
        color: #8B7355;
    }

    .status-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 500;
    }

    .status-listo      { background: #E8F5E9; color: #2E7D32; }
    .status-en_proceso { background: #FFF8E1; color: #F57F17; }
    .status-pendiente  { background: #F3E5F5; color: #6A1B9A; }
    .status-atrasado   { color: #C0392B; font-size: 11px; font-weight: 500; }
</style>

<div class="agenda-header">
    <div class="agenda-title">
        <h1>Agenda de Encargos</h1>
        <p>Hoy es <?= $fechaHoy ?></p>
    </div>
    <a href="?page=nuevo-encargo" class="btn-nuevo">
        <span>+</span> Nuevo Encargo
    </a>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <span class="stat-value"><?= $estadisticas['activos'] ?></span>
        <p class="stat-label">Encargos Activos</p>
    </div>
    <div class="stat-card">
        <span class="stat-value"><?= $estadisticas['en_proceso'] ?></span>
        <p class="stat-label">En Proceso</p>
    </div>
    <div class="stat-card">
        <span class="stat-value"><?= $estadisticas['listos'] ?></span>
        <p class="stat-label">Listos</p>
    </div>
</div>

<div class="stats-grid">
    <div class="stat-card">
        <p class="stat-label">Total Señas Recibidas</p>
        <span class="stat-value stat-money">$<?= $estadisticas['senas'] ?></span>
    </div>
    <div class="stat-card">
        <p class="stat-label">Total Cobrado</p>
        <span class="stat-value stat-money">$<?= $estadisticas['cobrado'] ?></span>
    </div>
    <div class="stat-card">
        <p class="stat-label">Saldo Pendiente</p>
        <span class="stat-value stat-money" style="color: #A67C52;">$<?= $estadisticas['pendiente'] ?></span>
    </div>
</div>

<h2 class="section-title">Próximas Entregas</h2>

<?php if (!empty($proximasEntregas)): ?>
    <?php foreach ($proximasEntregas as $encargo): ?>
        <?php
            $fecha  = new DateTime($encargo['fecha_entrega']);
            $hoy    = new DateTime('today');
            $diff   = (int)$hoy->diff($fecha)->format('%r%a');
            $dia    = $fecha->format('d');
            $mes    = strtoupper(substr($meses[(int)$fecha->format('n') - 1], 0, 3));
            $estadoClass = 'status-' . $encargo['estado'];
            $estadoLabel = ucfirst(str_replace('_', ' ', $encargo['estado']));
        ?>
        <div class="entrega-card">
            <div class="entrega-fecha">
                <span class="dia"><?= $dia ?></span>
                <span class="mes"><?= $mes ?></span>
                <?php if ($diff < 0): ?>
                    <span class="status-atrasado">Atrasado <?= abs($diff) ?>d</span>
                <?php elseif ($diff === 0): ?>
                    <span class="status-atrasado">¡Hoy!</span>
                <?php endif; ?>
            </div>
            <div class="entrega-info">
                <h3><?= htmlspecialchars($encargo['tipo']) ?></h3>
                <p><?= htmlspecialchars($encargo['cliente_nombre'] ?? 'Sin cliente') ?></p>
                <?php if (!empty($encargo['descripcion'])): ?>
                    <p style="margin-top: 8px; color: #5C4A3A;"><?= htmlspecialchars($encargo['descripcion']) ?></p>
                <?php endif; ?>
            </div>
            <div class="entrega-status">
                <span class="status-badge <?= $estadoClass ?>"><?= $estadoLabel ?></span>
            </div>
        </div>
    <?php endforeach; ?>
<?php else: ?>
    <div class="placeholder" style="background: white; padding: 40px; border-radius: 12px; text-align: center; border: 1px dashed #EDE8E0;">
        <span style="font-size: 40px;">📋</span>
        <p style="color: #8B7355; margin-top: 10px;">No hay encargos próximos para mostrar.</p>
    </div>
<?php endif; ?>

</div><!-- /content-area -->
</div><!-- /main-wrapper -->
</body>
</html>