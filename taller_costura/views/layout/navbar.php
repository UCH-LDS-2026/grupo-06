<?php
$paginaActual = $_GET['page'] ?? 'inicio';
?>
<nav class="navbar">
    <a href="/ProyectoFinal/grupo-06/taller_costura/index.php" 
       class="<?= $paginaActual == 'inicio' ? 'activo' : '' ?>">
        🏠 Inicio
    </a>
    <a href="/ProyectoFinal/grupo-06/taller_costura/index.php?page=encargos"
       class="<?= $paginaActual == 'encargos' ? 'activo' : '' ?>">
        📋 Encargos
    </a>
    <a href="/ProyectoFinal/grupo-06/taller_costura/index.php?page=clientes"
       class="<?= $paginaActual == 'clientes' ? 'activo' : '' ?>">
        👤 Clientes
    </a>
    <a href="/ProyectoFinal/grupo-06/taller_costura/index.php?page=pagos"
       class="<?= $paginaActual == 'pagos' ? 'activo' : '' ?>">
        💰 Pagos
    </a>
    <a href="/ProyectoFinal/grupo-06/taller_costura/index.php?page=alertas"
       class="<?= $paginaActual == 'alertas' ? 'activo' : '' ?>">
        🔔 Alertas
    </a>
</nav>