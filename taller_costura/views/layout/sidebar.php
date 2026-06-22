<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once BASE_PATH . '/models/Alerta.php';
require_once BASE_PATH . '/controllers/AuthController.php';

$adminNombre = AuthController::getAdminNombre() ?? 'Administrador';
$adminId = AuthController::getAdminId() ?? 1;

$alertaModel = new Alerta();
$alertasNoLeidas = $alertaModel->contarNoLeidas($adminId);

$paginaActual = $_GET['page'] ?? 'agenda';
?>
<button class="menu-toggle" onclick="toggleSidebar()">
    <span class="material-symbols-outlined">menu</span>
</button>

<script>
function toggleSidebar() {
    document.querySelector('.sidebar').classList.toggle('active');
}
</script>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atelier — Gestión de Encargos</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/sidebar.css">
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200" />
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">

    <?php if (in_array($paginaActual, ['agenda', 'crear', 'detalle-encargo', 'editar-encargo'])): ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/encargos/encargos.css">
    <?php endif; ?>

    <?php if ($paginaActual === 'clientes'): ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/clientes/clientes.css">
    <?php endif; ?>

    <?php if ($paginaActual === 'pagos'): ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/pagos/pagos.css">
    <?php endif; ?>

    <?php if ($paginaActual === 'alertas'): ?>
        <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/alertas/alertas.css">
    <?php endif; ?>
</head>

<body>

<aside class="sidebar">
    <div class="sidebar-top">

        <div class="sidebar-logo">
            <div class="sidebar-logo-icon">
                <span class="material-symbols-outlined">content_cut</span>
            </div>

            <div class="sidebar-logo-text">
                <h2>Atelier</h2>
                <span>Sistema de Gestión</span>
            </div>
        </div>

       <nav class="sidebar-nav">
    <a href="<?= BASE_URL ?>/index.php" 
       onclick="closeSidebarMobile()" 
       class="<?= $paginaActual == 'agenda' ? 'activo' : '' ?>">
       <span class="material-symbols-outlined">box</span> Encargos
    </a>

    <a href="<?= BASE_URL ?>/index.php?page=clientes" 
       onclick="closeSidebarMobile()" 
       class="<?= $paginaActual == 'clientes' ? 'activo' : '' ?>">
       <span class="material-symbols-outlined">group</span> Clientes
    </a>

    <a href="<?= BASE_URL ?>/index.php?page=pagos" 
       onclick="closeSidebarMobile()" 
       class="<?= $paginaActual == 'pagos' ? 'activo' : '' ?>">
       <span class="material-symbols-outlined">attach_money</span> Pagos
    </a>
</nav>

<script>
function closeSidebarMobile() {
    // Si la pantalla es pequeña, removemos la clase active
    if (window.innerWidth <= 768) {
        document.querySelector('.sidebar').classList.remove('active');
    }
}
</script>

    </div>

    <a href="<?= BASE_URL ?>/logout.php" class="logout-btn">
        <span class="material-symbols-outlined">arrow_back</span>
        Cerrar sesión
    </a>

</aside>


<div class="floating-alerts">

    <a href="<?= BASE_URL ?>/index.php?page=alertas" class="campana-btn">
        <span class="material-symbols-outlined">notifications</span>

        <?php if ($alertasNoLeidas > 0): ?>
            <span class="badge"><?= $alertasNoLeidas ?></span>
        <?php endif; ?>
    </a>

    <?php if (isset($_SESSION['exito_cliente'])): ?>
        <div id="toast-campana" class="toast-campana">
            <span class="material-symbols-outlined">check_circle</span>
            <span id="toast-campana-msg">
                <?= htmlspecialchars($_SESSION['exito_cliente']) ?>
            </span>
        </div>

        <script>
        document.addEventListener('DOMContentLoaded', () => {
            const toast = document.getElementById('toast-campana');

            setTimeout(() => {
                toast.classList.add('visible');
            }, 100);

            setTimeout(() => {
                toast.classList.remove('visible');
            }, 3500);
        });
        </script>

        <?php unset($_SESSION['exito_cliente']); ?>
    <?php endif; ?>

</div>
<div class="main-wrapper">
    <div class="content-area">