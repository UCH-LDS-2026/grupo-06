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
    <link rel="stylesheet" href="public/css/layout/layout.css">
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