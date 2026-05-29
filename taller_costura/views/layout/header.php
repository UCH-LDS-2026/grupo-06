<?php
require_once BASE_PATH . '/models/Alerta.php';
$alertaModel = new Alerta();
$alertasNoLeidas = $alertaModel->contarNoLeidas($_SESSION['admin_id'] ?? 1);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?></title>
    <link rel="stylesheet" href="/ProyectoFinal/grupo-06/taller_costura/public/css/style.css">
</head>
<body>
    <div class="layout">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2>🧵 <?= APP_NAME ?></h2>
            </div>
            <nav class="sidebar-nav">
                <a href="/ProyectoFinal/grupo-06/taller_costura/index.php">🏠 Inicio</a>
                <a href="/ProyectoFinal/grupo-06/taller_costura/index.php?page=encargos">📋 Encargos</a>
                <a href="/ProyectoFinal/grupo-06/taller_costura/index.php?page=clientes">👤 Clientes</a>
                <a href="/ProyectoFinal/grupo-06/taller_costura/index.php?page=pagos">💰 Pagos</a>
            </nav>
        </aside>
        <div class="main-content">
            <header class="topbar">
                <h1 class="page-title">Sistema de Costura</h1>
                <div class="topbar-right">
                    <div class="campana-alertas">
                        <a href="/ProyectoFinal/grupo-06/taller_costura/index.php?page=alertas">
                            🔔
                            <?php if ($alertasNoLeidas > 0): ?>
                                <span class="badge"><?= $alertasNoLeidas ?></span>
                            <?php endif; ?>
                        </a>
                    </div>
                </div>
            </header>
            <main class="content">