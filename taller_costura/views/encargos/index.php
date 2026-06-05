<?php
require_once __DIR__ . '/../../config/config.php';
 
AuthController::requiereLogin();
 
require_once __DIR__ . '/../../views/layout/header.php';
?>
 
        <!-- Contenido de encargos -->
        <div class="page-header">
            <h1>Encargos</h1>
        </div>
 
        <div class="placeholder">
            <span>📋</span>
            <p>No hay encargos registrados todavía.</p>
        </div>
 
    </div><!-- /content-area -->
</div><!-- /main-wrapper -->
</body>
</html>
 