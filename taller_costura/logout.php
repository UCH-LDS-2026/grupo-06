<?php
// Incluimos el config.php desde la carpeta 'config'
require_once __DIR__ . '/config/config.php';

// Iniciamos la sesión para poder destruirla
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Limpiamos y destruimos la sesión
$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

// Redirigimos al login usando la constante BASE_URL
// Esto buscará correctamente: .../taller_costura/views/auth/login.php
header("Location: " . BASE_URL . "/views/auth/login.php");
exit();
?>