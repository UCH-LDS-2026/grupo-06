<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../models/FichaCliente.php';
require_once __DIR__ . '/../controllers/AuthController.php';

class ClienteController {

    public static function registrar(): void {
        AuthController::requiereLogin();

        $nombre   = trim($_POST['nombre']   ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $email    = trim($_POST['email']    ?? '');

        if ($nombre === '') {
            $_SESSION['error_cliente'] = 'El nombre es obligatorio.';
            header('Location: ' . BASE_URL . '/index.php?page=clientes');
            exit;
        }

        $cliente = new Cliente(0, $nombre, $telefono, $email);
        $ok      = $cliente->guardar();

        if (!$ok) {
            $_SESSION['error_cliente'] = 'El email ya está registrado para otra clienta.';
            header('Location: ' . BASE_URL . '/index.php?page=clientes');
            exit;
        }

        // Si se enviaron medidas, guardar la ficha también
        $tieneMedidas =
            ($_POST['contorno_pecho']   ?? '') !== '' ||
            ($_POST['contorno_cintura'] ?? '') !== '' ||
            ($_POST['contorno_cadera']  ?? '') !== '' ||
            ($_POST['largo_espalda']    ?? '') !== '' ||
            ($_POST['largo_manga']      ?? '') !== '' ||
            ($_POST['largo_pantalon']   ?? '') !== '';

        if ($tieneMedidas) {
            $ficha = new FichaCliente(
                0,
                $cliente->getId(),
                '',
                ($_POST['contorno_pecho']   ?? '') !== '' ? (float)$_POST['contorno_pecho']   : null,
                ($_POST['contorno_cintura'] ?? '') !== '' ? (float)$_POST['contorno_cintura'] : null,
                ($_POST['contorno_cadera']  ?? '') !== '' ? (float)$_POST['contorno_cadera']  : null,
                ($_POST['largo_manga']      ?? '') !== '' ? (float)$_POST['largo_manga']      : null,
                ($_POST['largo_espalda']    ?? '') !== '' ? (float)$_POST['largo_espalda']    : null,
                ($_POST['largo_pantalon']   ?? '') !== '' ? (float)$_POST['largo_pantalon']   : null,
                ''
            );
            $ficha->guardarOActualizar();
        }

        $_SESSION['exito_cliente'] = 'Clienta registrada correctamente.';
        header('Location: ' . BASE_URL . '/index.php?page=clientes');
        exit;
    }

    // =========================================================================
    // EDITAR
    // =========================================================================
    public static function editar(): void {
        AuthController::requiereLogin();

        $id      = (int)($_POST['id'] ?? 0);
        $cliente = Cliente::getById($id);

        if ($cliente === null) {
            $_SESSION['error_cliente'] = 'Clienta no encontrada.';
            header('Location: ' . BASE_URL . '/index.php?page=clientes');
            exit;
        }

        $nombre   = trim($_POST['nombre']   ?? '');
        $telefono = trim($_POST['telefono'] ?? '');
        $email    = trim($_POST['email']    ?? '');

        if ($nombre === '') {
            $_SESSION['error_cliente'] = 'El nombre es obligatorio.';
            header('Location: ' . BASE_URL . '/index.php?page=ficha-cliente&id=' . $id);
            exit;
        }

        $cliente->setNombre($nombre);
        $cliente->setTelefono($telefono);
        $cliente->setEmail($email);
        $cliente->actualizar();

        $_SESSION['exito_cliente'] = 'Datos actualizados correctamente.';
        header('Location: ' . BASE_URL . '/index.php?page=ficha-cliente&id=' . $id);
        exit;
    }

    // =========================================================================
    // ELIMINAR
    // =========================================================================
    public static function eliminar(): void {
        AuthController::requiereLogin();

        $id      = (int)($_POST['id'] ?? 0);
        $cliente = Cliente::getById($id);

        if ($cliente === null) {
            $_SESSION['error_cliente'] = 'Clienta no encontrada.';
            header('Location: ' . BASE_URL . '/index.php?page=clientes');
            exit;
        }

        $cliente->eliminar();

        $_SESSION['exito_cliente'] = 'Clienta eliminada correctamente.';
        header('Location: ' . BASE_URL . '/index.php?page=clientes');
        exit;
    }

    // =========================================================================
    // GUARDAR FICHA
    // =========================================================================
    public static function guardarFicha(): void {
        AuthController::requiereLogin();

        $clienteId = (int)($_POST['cliente_id'] ?? 0);

        if ($clienteId === 0 || Cliente::getById($clienteId) === null) {
            $_SESSION['error_cliente'] = 'Clienta no válida.';
            header('Location: ' . BASE_URL . '/index.php?page=clientes');
            exit;
        }

        $ficha = new FichaCliente(
            0,
            $clienteId,
            trim($_POST['talle'] ?? ''),
            ($_POST['contorno_pecho']   ?? '') !== '' ? (float)$_POST['contorno_pecho']   : null,
            ($_POST['contorno_cintura'] ?? '') !== '' ? (float)$_POST['contorno_cintura'] : null,
            ($_POST['contorno_cadera']  ?? '') !== '' ? (float)$_POST['contorno_cadera']  : null,
            ($_POST['largo_manga']      ?? '') !== '' ? (float)$_POST['largo_manga']      : null,
            ($_POST['largo_espalda']    ?? '') !== '' ? (float)$_POST['largo_espalda']    : null,
            ($_POST['largo_pantalon']   ?? '') !== '' ? (float)$_POST['largo_pantalon']   : null,
            trim($_POST['observaciones_cliente'] ?? '')
        );

        $ficha->guardarOActualizar();

        $_SESSION['exito_cliente'] = 'Ficha actualizada correctamente.';
        header('Location: ' . BASE_URL . '/index.php?page=ficha-cliente&id=' . $clienteId);
        exit;
    }

    // =========================================================================
    // DISPATCHER
    // =========================================================================
    public static function dispatch(): void {
        $accion = $_POST['accion'] ?? '';

        match($accion) {
            'registrar'     => self::registrar(),
            'editar'        => self::editar(),
            'eliminar'      => self::eliminar(),
            'guardar_ficha' => self::guardarFicha(),
            default         => header('Location: ' . BASE_URL . '/index.php?page=clientes')
        };
        exit;
    }
}

// ClienteController se ejecuta desde index.php con dispatch() cuando es necesario