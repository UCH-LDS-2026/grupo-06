<?php
 
require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../models/FichaCliente.php';
require_once __DIR__ . '/../controllers/AuthController.php';
 
class ClienteController {
 
    // =========================================================================
    // REGISTRAR
    // =========================================================================
 
    /**
     * Procesa el formulario de registro de un cliente nuevo.
     * Espera $_POST['nombre'], $_POST['telefono'], $_POST['email'].
     */
    public static function registrar(): void {
        AuthController::requiereLogin();
 
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            require_once __DIR__ . '/../views/clientes/crear.php';
            return;
        }
 
        $nombre   = trim($_POST['nombre']   ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $email    = trim($_POST['email']    ?? '');
 
        // Validación
        if ($nombre === '') {
            $_SESSION['error_cliente'] = 'El nombre es obligatorio.';
            header('Location: ../views/clientes/crear.php');
            exit;
        }
 
        $cliente = new Cliente(0, $nombre, $telefono, $email);
        $ok      = $cliente->guardar();
 
        if (!$ok) {
            $_SESSION['error_cliente'] = 'El email ya está registrado para otro cliente.';
            header('Location: ../views/clientes/crear.php');
            exit;
        }
 
        $_SESSION['exito_cliente'] = 'Cliente registrado correctamente.';
        header('Location: ../views/clientes/index.php');
        exit;
    }
 
    // =========================================================================
    // EDITAR
    // =========================================================================
 
    /**
     * Muestra el formulario de edición (GET) o procesa los cambios (POST).
     * Espera $_GET['id'] o $_POST['id'].
     */
    public static function editar(): void {
        AuthController::requiereLogin();
 
        $id = (int)($_REQUEST['id'] ?? 0);
 
        if ($id === 0) {
            header('Location: ../views/clientes/index.php');
            exit;
        }
 
        $cliente = Cliente::getById($id);
 
        if ($cliente === null) {
            $_SESSION['error_cliente'] = 'Cliente no encontrado.';
            header('Location: ../views/clientes/index.php');
            exit;
        }
 
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // GET → mostrar formulario con datos actuales
            require_once __DIR__ . '/../views/clientes/editar.php';
            return;
        }
 
        // POST → actualizar
        $nombre   = trim($_POST['nombre']   ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $email    = trim($_POST['email']    ?? '');
 
        if ($nombre === '') {
            $_SESSION['error_cliente'] = 'El nombre es obligatorio.';
            header('Location: ../views/clientes/editar.php?id=' . $id);
            exit;
        }
 
        $cliente->setNombre($nombre);
        $cliente->setTelefono($telefono);
        $cliente->setEmail($email);
        $cliente->actualizar();
 
        $_SESSION['exito_cliente'] = 'Cliente actualizado correctamente.';
        header('Location: ../views/clientes/index.php');
        exit;
    }
 
    // =========================================================================
    // ELIMINAR
    // =========================================================================
 
    /**
     * Elimina un cliente por ID.
     * Espera $_POST['id'] (siempre por POST para evitar eliminaciones accidentales).
     */
    public static function eliminar(): void {
        AuthController::requiereLogin();
 
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../views/clientes/index.php');
            exit;
        }
 
        $id      = (int)($_POST['id'] ?? 0);
        $cliente = Cliente::getById($id);
 
        if ($cliente === null) {
            $_SESSION['error_cliente'] = 'Cliente no encontrado.';
            header('Location: ../views/clientes/index.php');
            exit;
        }
 
        $cliente->eliminar();
 
        $_SESSION['exito_cliente'] = 'Cliente eliminado correctamente.';
        header('Location: ../views/clientes/index.php');
        exit;
    }
 
    // =========================================================================
    // VER FICHA
    // =========================================================================
 
    /**
     * Muestra la ficha de medidas de un cliente.
     * Si no tiene ficha todavía, pasa null a la vista para que muestre el formulario vacío.
     * Espera $_GET['id'] (cliente_id).
     */
    public static function verFicha(): void {
        AuthController::requiereLogin();
 
        $clienteId = (int)($_GET['id'] ?? 0);
 
        if ($clienteId === 0) {
            header('Location: ../views/clientes/index.php');
            exit;
        }
 
        $cliente = Cliente::getById($clienteId);
 
        if ($cliente === null) {
            $_SESSION['error_cliente'] = 'Cliente no encontrado.';
            header('Location: ../views/clientes/index.php');
            exit;
        }
 
        // Puede ser null si el cliente aún no tiene ficha
        $ficha = FichaCliente::getByClienteId($clienteId);
 
        require_once __DIR__ . '/../views/clientes/ficha.php';
    }
 
    // =========================================================================
    // GUARDAR FICHA
    // =========================================================================
 
    /**
     * Guarda o actualiza la ficha de medidas de un cliente.
     * Espera $_POST con cliente_id y los campos de medidas.
     */
    public static function guardarFicha(): void {
        AuthController::requiereLogin();
 
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ../views/clientes/index.php');
            exit;
        }
 
        $clienteId = (int)($_POST['cliente_id'] ?? 0);
 
        if ($clienteId === 0 || Cliente::getById($clienteId) === null) {
            $_SESSION['error_cliente'] = 'Cliente no válido.';
            header('Location: ../views/clientes/index.php');
            exit;
        }
 
        $ficha = new FichaCliente(
            0,
            $clienteId,
            trim($_POST['talle']            ?? ''),
            isset($_POST['contorno_pecho'])   && $_POST['contorno_pecho']   !== '' ? (float)$_POST['contorno_pecho']   : null,
            isset($_POST['contorno_cintura']) && $_POST['contorno_cintura'] !== '' ? (float)$_POST['contorno_cintura'] : null,
            isset($_POST['contorno_cadera'])  && $_POST['contorno_cadera']  !== '' ? (float)$_POST['contorno_cadera']  : null,
            isset($_POST['largo_manga'])      && $_POST['largo_manga']      !== '' ? (float)$_POST['largo_manga']      : null,
            trim($_POST['observaciones_cliente'] ?? '')
        );
 
        $ficha->guardarOActualizar();
 
        $_SESSION['exito_cliente'] = 'Ficha actualizada correctamente.';
        header('Location: ../views/clientes/ficha.php?id=' . $clienteId);
        exit;
    }
}