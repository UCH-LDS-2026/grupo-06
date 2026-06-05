<?php
require_once __DIR__ . '/../../config/config.php';
// AuthController::requiereLogin();

// Simulación de datos (Aquí conectarás tus modelos después)
$fechaHoy = "lunes, 13 de abril de 2026"; // Puedes usar date() en español
$estadisticas = [
    'activos' => 5,
    'en_proceso' => 1,
    'listos' => 2,
    'senas' => '49.500',
    'cobrado' => '58.500',
    'pendiente' => '51.500'
];

?>

<style>
    /* Estilos específicos para la vista de Agenda/Encargos */
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

    /* Grid de Estadísticas */
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

    .stat-money { color: #7D4E2F; } /* Color especial para montos */

    /* Sección de Entregas */
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

    .status-listo { background: #E8F5E9; color: #2E7D32; }
    .status-atrasado { color: #C0392B; font-size: 11px; font-weight: 500; }
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

<!-- Fila superior de contadores -->
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

<!-- Fila de montos económicos -->
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

<!-- Ejemplo de una tarjeta de entrega -->
<div class="entrega-card">
    <div class="entrega-fecha">
        <span class="dia">12</span>
        <span class="mes">ABR</span>
        <span class="status-atrasado">Atrasado 1d</span>
    </div>
    <div class="entrega-info">
        <h3>Ajuste de Vestido</h3>
        <p>María González</p>
        <p style="margin-top: 8px; color: #5C4A3A;">Ajustar costados y largo de vestido rojo</p>
    </div>
    <div class="entrega-status">
        <span class="status-badge status-listo">Listo</span>
    </div>
</div>

<!-- Aquí puedes abrir un foreach para cargar los encargos reales de la DB -->
<?php if (empty($proximasEntregas)): ?>
    <!-- Mantenemos el placeholder por si no hay datos -->
    <div class="placeholder" style="background: white; padding: 40px; border-radius: 12px; text-align: center; border: 1px dashed #EDE8E0;">
        <span style="font-size: 40px;">📋</span>
        <p style="color: #8B7355; margin-top: 10px;">No hay encargos próximos para mostrar.</p>
    </div>
<?php endif; ?>

</div><!-- /content-area -->
</div><!-- /main-wrapper -->
</body>
</html>