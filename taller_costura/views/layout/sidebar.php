<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once BASE_PATH . '/models/Alerta.php';
require_once BASE_PATH . '/controllers/AuthController.php';

$adminNombre     = AuthController::getAdminNombre() ?? 'Administrador';
$adminId         = AuthController::getAdminId() ?? 1;
$alertaModel     = new Alerta();
$alertasNoLeidas = $alertaModel->contarNoLeidas($adminId);
$paginaActual    = $_GET['page'] ?? 'agenda';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atelier — Gestión de Encargos</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="public/css/layout/layout.css">
    <?php if (in_array($paginaActual, ['agenda', 'crear', 'detalle-encargo'])): ?>
        <link rel="stylesheet" href="public/css/encargos/encargos.css">
    <?php endif; ?>
    <?php if ($paginaActual === 'pagos'): ?>
        <link rel="stylesheet" href="public/css/pagos/pagos.css">
    <?php endif; ?>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-top">
        <div class="sidebar-logo">
            <div class="sidebar-logo-icon">
                <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="6" cy="6" r="3"></circle>
                    <circle cx="6" cy="18" r="3"></circle>
                    <line x1="20" y1="4" x2="8.12" y2="15.88"></line>
                    <line x1="14.47" y1="14.48" x2="20" y2="20"></line>
                    <line x1="8.12" y1="8.12" x2="12" y2="12"></line>
                </svg>
            </div>
            <div class="sidebar-logo-text">
                <h2>Atelier</h2>
                <span>Gestión de Encargos</span>
            </div>
        </div>

        <nav class="sidebar-nav">
            <a href="index.php"
               class="<?= $paginaActual == 'agenda' ? 'activo' : '' ?>">
                <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                Agenda
            </a>

            <a href="index.php?page=clientes"
               class="<?= $paginaActual == 'clientes' ? 'activo' : '' ?>">
                <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                Clientes
            </a>

            <a href="index.php?page=pagos"
               class="<?= $paginaActual == 'pagos' ? 'activo' : '' ?>">
                <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="1" x2="12" y2="23"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
                Pagos
            </a>
        </nav>
    </div>

    <div style="margin-top: auto; display: flex; flex-direction: column; gap: 8px;">
    <div class="sidebar-user">
        <span class="role">Costurera</span>
        <span class="name"><?= htmlspecialchars($adminNombre) ?></span>
    </div>

    <a href="index.php?accion=logout" class="sidebar-logout">
        <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
            <polyline points="16 17 21 12 16 7"/>
            <line x1="21" y1="12" x2="9" y2="12"/>
        </svg>
        Cerrar sesión
    </a>
</div>
</aside>

<div class="main-wrapper">
    <div class="topbar">
        <div class="campana-alertas">
            <a href="index.php?page=alertas">
                <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                </svg>
                <?php if ($alertasNoLeidas > 0): ?>
                    <span class="badge"><?= $alertasNoLeidas ?></span>
                <?php endif; ?>
            </a>
        </div>
    </div>
    <div class="content-area">
