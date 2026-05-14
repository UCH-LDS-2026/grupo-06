<?php
/**
 * Punto de entrada de la aplicación
 */

require_once 'config/database.php';

// Incluir el layout
require_once VIEWS_PATH . '/layout/header.php';
require_once VIEWS_PATH . '/layout/navbar.php';
?>

<div class="container mt-5">
    <h1>Bienvenido a <?php echo APP_NAME; ?></h1>
    <p>Sistema de gestión de talleres de costura</p>
</div>

<?php
require_once VIEWS_PATH . '/layout/footer.php';
?>
