<?php
 
require_once __DIR__ . '/../models/Administrador.php';
 
class AuthController {
 
    // =========================================================================
    // INICIAR SESIÓN
    // =========================================================================
 
    public static function iniciarSesion(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
 
        if (isset($_SESSION['admin_id'])) {
            header('Location: /sistema_costura/grupo-06/taller_costura/views/encargos/index.php');
            exit;
        }
 
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            require_once __DIR__ . '/../views/auth/login.php';
            return;
        }
 
        $email      = trim($_POST['email']      ?? '');
        $contrasena = trim($_POST['contrasena'] ?? '');
 
        if ($email === '' || $contrasena === '') {
            $_SESSION['error_login'] = 'Por favor completá todos los campos.';
            header('Location: /sistema_costura/grupo-06/taller_costura/views/auth/login.php');
            exit;
        }
 
        $admin = Administrador::login($email, $contrasena);
 
        if ($admin === null) {
            $_SESSION['error_login'] = 'Email o contraseña incorrectos.';
            header('Location: /sistema_costura/grupo-06/taller_costura/views/auth/login.php');
            exit;
        }
 
        $_SESSION['admin_id']     = $admin->getId();
        $_SESSION['admin_nombre'] = $admin->getNombre();
        $_SESSION['admin_email']  = $admin->getEmail();
        unset($_SESSION['error_login']);
 
        header('Location: /sistema_costura/grupo-06/taller_costura/views/encargos/index.php');
        exit;
    }
 
    // =========================================================================
    // CERRAR SESIÓN
    // =========================================================================
 
    public static function cerrarSesion(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
        session_unset();
        session_destroy();
 
        header('Location: /sistema_costura/grupo-06/taller_costura/views/auth/login.php');
        exit;
    }
 
    // =========================================================================
    // CAMBIAR CONTRASEÑA
    // =========================================================================
 
    public static function cambiarContrasena(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
 
        $email            = trim($_POST['email']               ?? '');
        $contrasenaActual = trim($_POST['contrasena_actual']   ?? '');
        $nueva            = trim($_POST['nueva_contrasena']    ?? '');
        $confirmar        = trim($_POST['confirmar_contrasena'] ?? '');
 
        if ($email === '' || $contrasenaActual === '' || $nueva === '' || $confirmar === '') {
            $_SESSION['error_cambio'] = 'Completá todos los campos.';
            header('Location: /sistema_costura/grupo-06/taller_costura/views/auth/login.php');
            exit;
        }
 
        if (strlen($nueva) < 8) {
            $_SESSION['error_cambio'] = 'La nueva contraseña debe tener al menos 8 caracteres.';
            header('Location: /sistema_costura/grupo-06/taller_costura/views/auth/login.php');
            exit;
        }
 
        if ($nueva !== $confirmar) {
            $_SESSION['error_cambio'] = 'La nueva contraseña y la confirmación no coinciden.';
            header('Location: /sistema_costura/grupo-06/taller_costura/views/auth/login.php');
            exit;
        }
 
        $admin = Administrador::getByEmail($email);
        if ($admin === null) {
            $_SESSION['error_cambio'] = 'No existe un administrador con ese email.';
            header('Location: /sistema_costura/grupo-06/taller_costura/views/auth/login.php');
            exit;
        }
 
        $ok = $admin->cambiarContrasena($contrasenaActual, $nueva);
        if (!$ok) {
            $_SESSION['error_cambio'] = 'La contraseña actual es incorrecta.';
            header('Location: /sistema_costura/grupo-06/taller_costura/views/auth/login.php');
            exit;
        }
 
        $_SESSION['exito_cambio'] = 'Contraseña actualizada correctamente. Ya podés ingresar.';
        header('Location: /sistema_costura/grupo-06/taller_costura/views/auth/login.php');
        exit;
    }
 
    // =========================================================================
    // HELPERS
    // =========================================================================
 
    public static function requiereLogin(): void {
        if (session_status() === PHP_SESSION_NONE) session_start();
 
        if (!isset($_SESSION['admin_id'])) {
            header('Location: /sistema_costura/grupo-06/taller_costura/views/auth/login.php');
            exit;
        }
    }
 
    public static function getAdminNombre(): ?string {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return $_SESSION['admin_nombre'] ?? null;
    }
 
    public static function getAdminId(): ?int {
        if (session_status() === PHP_SESSION_NONE) session_start();
        return isset($_SESSION['admin_id']) ? (int)$_SESSION['admin_id'] : null;
    }
 
    // =========================================================================
    // DISPATCHER
    // =========================================================================
 
    public static function dispatch(): void {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') return;
 
        $accion = $_POST['accion'] ?? '';
 
        match($accion) {
            'login'              => self::iniciarSesion(),
            'cambiar_contrasena' => self::cambiarContrasena(),
            'logout'             => self::cerrarSesion(),
            default              => header('Location: /sistema_costura/grupo-06/taller_costura/views/auth/login.php')
        };
        exit;
    }
}
 
// Punto de entrada cuando se llama directamente al controlador
if (basename(__FILE__) === basename($_SERVER['SCRIPT_FILENAME'])) {
    AuthController::dispatch();
}
 