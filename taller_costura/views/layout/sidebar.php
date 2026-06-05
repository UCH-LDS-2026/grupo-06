<?php
if (session_status() === PHP_SESSION_NONE) session_start();
require_once BASE_PATH . '/models/Alerta.php';
require_once BASE_PATH . '/controllers/AuthController.php';

$adminNombre = AuthController::getAdminNombre() ?? 'Administrador';
$adminId     = AuthController::getAdminId() ?? 1;
$alertaModel = new Alerta();
$alertasNoLeidas = $alertaModel->contarNoLeidas($adminId);
$paginaActual = $_GET['page'] ?? 'agenda';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atelier — Gestión de Encargos</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
    <?php if (in_array($paginaActual, ['agenda', 'crear', 'detalle-encargo'])): ?>
        <link rel="stylesheet" href="public/css/encargos/encargos.css">
    <?php endif; ?>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #FAF8F5;
            color: #2C1810;
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR WITH FLEXBOX SPACE-BETWEEN */
        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: #FFFFFF;
            border-right: 1px solid #EDE8E0;
            padding: 28px 20px;
            display: flex;
            flex-direction: column;
            justify-content: space-between; /* Empuja el contenedor de usuario hacia el fondo */
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
        }

        .sidebar-top {
            display: flex;
            flex-direction: column;
            gap: 32px;
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 24px;
            border-bottom: 1px solid #EDE8E0;
        }

        .sidebar-logo-icon {
            width: 44px;
            height: 44px;
            background: #FAF8F5;
            border: 1px solid #EDE8E0;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .sidebar-logo-icon svg {
            width: 20px;
            height: 20px;
            stroke: #8B7355;
            fill: none;
            stroke-width: 1.5;
        }

        .sidebar-logo-text h2 {
            font-family: 'Playfair Display', serif;
            font-size: 20px;
            font-weight: 500;
            color: #2C1810;
            line-height: 1.2;
        }

        .sidebar-logo-text span {
            font-size: 11px;
            color: #8B7355;
            letter-spacing: 0.3px;
        }

        .sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 400;
            color: #5C4A3A;
            transition: all 0.2s ease;
        }

        .sidebar-nav a svg {
            width: 18px;
            height: 18px;
            stroke: #5C4A3A;
            fill: none;
            stroke-width: 1.75;
            transition: stroke 0.2s ease;
        }

        .sidebar-nav a:hover {
            background: #FAF8F5;
            color: #2C1810;
        }

        .sidebar-nav a:hover svg {
            stroke: #2C1810;
        }

        .sidebar-nav a.activo {
            background: #7D4E2F;
            color: #FFFFFF;
            font-weight: 500;
        }

        .sidebar-nav a.activo svg {
            stroke: #FFFFFF;
        }

        /* RECUADRO EN LA BASE */
        .sidebar-user {
            background-color: #F3ECE3; /* Tono beige suave de la maqueta */
            padding: 14px 18px;
            border-radius: 12px; /* Esquinas redondeadas suaves */
            display: flex;
            flex-direction: column;
            gap: 4px;
            width: 100%;
        }

        .sidebar-user .role {
            font-size: 12px;
            color: #8B7355;
            font-weight: 400;
        }

        .sidebar-user .name {
            font-family: 'Inter', sans-serif;
            font-size: 15px;
            font-weight: 500;
            color: #2C1810;
        }

        /* CONTENIDO PRINCIPAL */
        .main-wrapper {
            margin-left: 260px;
            flex: 1;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding: 16px 32px;
            background: #FAF8F5;
            border-bottom: 1px solid #EDE8E0;
        }

        .campana-alertas a {
            position: relative;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .campana-alertas svg {
            width: 22px;
            height: 22px;
            stroke: #5C4A3A;
            fill: none;
            stroke-width: 1.75;
        }

        .campana-alertas .badge {
            position: absolute;
            top: -4px;
            right: -4px;
            background: #C0392B;
            color: white;
            font-size: 10px;
            font-family: 'Inter', sans-serif;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .content-area {
            padding: 32px;
            flex: 1;
        }
    </style>
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
            <a href="/grupo-06/taller_costura/index.php"
               class="<?= $paginaActual == 'agenda' ? 'activo' : '' ?>">
                <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                    <line x1="16" y1="2" x2="16" y2="6"></line>
                    <line x1="8" y1="2" x2="8" y2="6"></line>
                    <line x1="3" y1="10" x2="21" y2="10"></line>
                </svg>
                Agenda
            </a>
            
            <a href="/grupo-06/taller_costura/index.php?page=clientes"
               class="<?= $paginaActual == 'clientes' ? 'activo' : '' ?>">
                <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path>
                    <circle cx="9" cy="7" r="4"></circle>
                    <path d="M23 21v-2a4 4 0 0 0-3-3.87"></path>
                    <path d="M16 3.13a4 4 0 0 1 0 7.75"></path>
                </svg>
                Clientes
            </a>
            
            <a href="/grupo-06/taller_costura/index.php?page=pagos"
               class="<?= $paginaActual == 'pagos' ? 'activo' : '' ?>">
                <svg viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="1" x2="12" y2="23"></line>
                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                </svg>
                Pagos
            </a>
        </nav>
    </div>

    <div class="sidebar-user">
        <span class="role">Costurera</span>
        <span class="name"><?= htmlspecialchars($adminNombre) ?></span>
    </div>
</aside>

<div class="main-wrapper">
    <div class="topbar">
        <div class="campana-alertas">
            <a href="/grupo-06/taller_costura/index.php?page=alertas">
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