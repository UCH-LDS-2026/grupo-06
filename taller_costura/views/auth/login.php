<?php
require_once __DIR__ . '/../../controllers/AuthController.php';
require_once __DIR__ . '/../../config/config.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$error = $_SESSION['error_login'] ?? null;
$errorCambio = $_SESSION['error_cambio'] ?? null;
$exitoCambio = $_SESSION['exito_cambio'] ?? null;

if (!isset($_SESSION['admin_id'])) {
    session_unset();
    session_destroy();
    session_start();
}

$_SESSION['error_login'] = $error;
$_SESSION['error_cambio'] = $errorCambio;
$_SESSION['exito_cambio'] = $exitoCambio;

$error = $_SESSION['error_login'] ?? null;
unset($_SESSION['error_login']);
$errorCambio = $_SESSION['error_cambio'] ?? null;
unset($_SESSION['error_cambio']);
$exitoCambio = $_SESSION['exito_cambio'] ?? null;
unset($_SESSION['exito_cambio']);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Taller — Ingresar</title>
    <link href="https://fonts.googleapis.com/css2?family=Source+Serif+4:wght@400;600&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined" rel="stylesheet" />
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/css/login.css">
</head>
<body>
<div class="wrapper">
    <div class="marca">
        <div class="divisor">
            <span class="material-symbols-outlined">content_cut</span>
        </div>
        <h1>Taller</h1>
        <p>SISTEMA DE GESTIÓN</p>
    </div>

    <div class="card">
        <h2 class="card-titulo">Ingresar al sistema</h2>
        <?php if ($error): ?> <div class="alerta alerta-error"><?= htmlspecialchars($error) ?></div> <?php endif; ?>
        <form action="<?= BASE_URL ?>/index.php" method="POST">
            <input type="hidden" name="accion" value="login">
            <div class="campo">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" placeholder="admin@taller.com" required autofocus>
            </div>
            <div class="campo">
                <label for="contrasena">Contraseña</label>
                <input type="password" id="contrasena" name="contrasena" placeholder="••••••••" required>
            </div>
            <button type="submit" class="btn">Ingresar</button>
        </form>
        <div class="separador">¿Olvidaste tu contraseña?</div>
        <button class="btn" onclick="toggleCambio()">Cambiar contraseña</button>
    </div>

    <div class="card" id="seccion-cambio">
        <h2 class="card-titulo">Cambiar contraseña</h2>
        <form action="<?= BASE_URL ?>/index.php" method="POST">
            <input type="hidden" name="accion" value="cambiar_contrasena">
            <div class="campo">
                <label for="email_cambio">Email del administrador</label>
                <input type="email" id="email_cambio" name="email" required>
            </div>
            <div class="campo">
                <label for="contrasena_actual">Contraseña actual</label>
                <input type="password" id="contrasena_actual" name="contrasena_actual" required>
            </div>
            <div class="campo">
                <label for="nueva_contrasena">Nueva contraseña</label>
                <input type="password" id="nueva_contrasena" name="nueva_contrasena" required>
                <p class="hint">Mínimo 8 caracteres.</p>
            </div>
            <div class="campo">
                <label for="confirmar_contrasena">Confirmar nueva contraseña</label>
                <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" required>
            </div>
            <button type="submit" class="btn">Guardar</button>
        </form>
    </div>
</div>

<script>
    function toggleCambio() {
        document.getElementById('seccion-cambio').classList.toggle('visible');
    }
    <?php if ($errorCambio || $exitoCambio): ?>
        document.getElementById('seccion-cambio').classList.add('visible');
    <?php endif; ?>
</script>
</body>
</html>