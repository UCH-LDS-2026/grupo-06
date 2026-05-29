<?php
require_once BASE_PATH . '/models/Alerta.php';
$alertaModel = new Alerta();
$alertasNoLeidas = $alertaModel->contarNoLeidas($_SESSION['admin_id'] ?? 1);
$paginaActual = $_GET['page'] ?? 'inicio';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Atelier — Gestión de Encargos</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;500&family=Inter:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background-color: #FAF8F5;
            color: #2C1810;
            display: flex;
            min-height: 100vh;
        }

        /* SIDEBAR */
        .sidebar {
            width: 260px;
            min-height: 100vh;
            background: #FFFFFF;
            border-right: 1px solid #EDE8E0;
            padding: 28px 20px;
            display: flex;
            flex-direction: column;
            gap: 32px;
            position: fixed;
            top: 0;
            left: 0;
            height: 100%;
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
            font-size: 18px;
        }

        .sidebar-logo-text h2 {
            font-family: 'Playfair Display', serif;
            font-size: 18px;
            font-weight: 500;
            color: #2C1810;
        }

        .sidebar-logo-text span {
            font-size: 12px;
            color: #8B7355;
        }

        .sidebar-nav {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .sidebar-nav a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: 10px;
            text-decoration: none;
            font-size: 14px;
            color: #5C4A3A;
            transition: all 0.2s;
        }

        .sidebar-nav a:hover {
            background: #FAF8F5;
        }

        .sidebar-nav a.activo {
            background: #7D4E2F;
            color: #FFFFFF;
        }

        .sidebar-nav a.activo svg path,
        .sidebar-nav a.activo svg rect {
            stroke: #FFFFFF;
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
            font-size: 20px;
            color: #5C4A3A;
        }

        .campana-alertas .badge {
            position: absolute;
            top: -6px;
            right: -8px;
            background: #C0392B;
            color: white;
            font-size: 10px;
            font-family: 'Inter', sans-serif;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
        }

        .content-area {
            padding: 32px;
            flex: 1;
        }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="sidebar-logo-icon">✂️</div>
        <div class="sidebar-logo-text">
            <h2>Atelier</h2>
            <span>Gestión de Encargos</span>
        </div>
    </div>
    <nav class="sidebar-nav">
        <a href="/ProyectoFinal/grupo-06/taller_costura/index.php"
           class="<?= $paginaActual == 'inicio' ? 'activo' : '' ?>">
            📅 Agenda
        </a>
        <a href="/ProyectoFinal/grupo-06/taller_costura/index.php?page=clientes"
           class="<?= $paginaActual == 'clientes' ? 'activo' : '' ?>">
            👤 Clientes
        </a>
        <a href="/ProyectoFinal/grupo-06/taller_costura/index.php?page=pagos"
           class="<?= $paginaActual == 'pagos' ? 'activo' : '' ?>">
            💰 Pagos
        </a>
    </nav>
</aside>

<div class="main-wrapper">
    <div class="topbar">
        <div class="campana-alertas">
            <a href="/ProyectoFinal/grupo-06/taller_costura/index.php?page=alertas">
                🔔
                <?php if ($alertasNoLeidas > 0): ?>
                    <span class="badge"><?= $alertasNoLeidas ?></span>
                <?php endif; ?>
            </a>
        </div>
    </div>
    <div class="content-area">