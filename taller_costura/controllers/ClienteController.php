<?php
if (session_status() === PHP_SESSION_NONE) session_start();

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/Cliente.php';
require_once __DIR__ . '/../models/FichaCliente.php';
require_once __DIR__ . '/../models/Encargo.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../controllers/AuthController.php';

class ClienteController {

    // =========================================================================
    // HELPER — Validar medidas
    // =========================================================================
    private static function validarMedidas(array $post): ?string {
        $campos = [
            'contorno_pecho'   => 'Contorno de Busto',
            'contorno_cintura' => 'Contorno de Cintura',
            'contorno_cadera'  => 'Contorno de Cadera',
            'largo_espalda'    => 'Largo de Espalda',
            'largo_manga'      => 'Largo de Manga',
            'largo_pantalon'   => 'Largo de Pantalón',
        ];
        foreach ($campos as $key => $label) {
            $val = $post[$key] ?? '';
            if ($val === '') continue;
            $num = (float)$val;
            if ($num < 1 || $num > 300) {
                return "{$label} debe estar entre 1 y 300 cm.";
            }
        }
        return null;
    }

    // =========================================================================
    // REGISTRAR
    // =========================================================================
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

        // Validar medidas
        $errorMedidas = self::validarMedidas($_POST);
        if ($errorMedidas !== null) {
            $_SESSION['error_cliente'] = $errorMedidas;
            header('Location: ' . BASE_URL . '/index.php?page=clientes');
            exit;
        }

        // Advertencia de teléfono duplicado (no bloquea)
        $warningTel = null;
        if ($telefono !== '') {
            $existeTel = Cliente::getByTelefono($telefono);
            if ($existeTel !== null) {
                $warningTel = 'Atención: el teléfono ya está registrado para ' . $existeTel->getNombre() . '.';
            }
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

        $_SESSION['exito_cliente'] = 'Clienta registrada correctamente.' . ($warningTel ? ' ⚠️ ' . $warningTel : '');
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

        // Advertencia de teléfono duplicado (no bloquea, excluye la clienta actual)
        $warningTel = null;
        if ($telefono !== '') {
            $existeTel = Cliente::getByTelefono($telefono);
            if ($existeTel !== null && $existeTel->getId() !== $id) {
                $warningTel = 'Atención: el teléfono ya está registrado para ' . $existeTel->getNombre() . '.';
            }
        }

        $cliente->setNombre($nombre);
        $cliente->setTelefono($telefono);
        $cliente->setEmail($email);
        $cliente->actualizar();

        $_SESSION['exito_cliente'] = 'Datos actualizados correctamente.' . ($warningTel ? ' ⚠️ ' . $warningTel : '');
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

        // Verificar encargos activos
        $db      = Database::getInstance()->getConnection();
        $encargo = new Encargo($db);
        $stmt    = $encargo->getByClienteId($id);
        $todos   = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $activos = array_filter($todos, fn($e) => in_array($e['estado'], ['pendiente', 'en_proceso', 'listo']));

        if (count($activos) > 0) {
            $cant = count($activos);
            $_SESSION['error_cliente'] = "No se puede eliminar a {$cliente->getNombre()} porque tiene {$cant} encargo" . ($cant === 1 ? '' : 's') . " activo" . ($cant === 1 ? '' : 's') . ". Finalizalos antes de eliminar la clienta.";
            header('Location: ' . BASE_URL . '/index.php?page=ficha-cliente&id=' . $id);
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

        // Validar medidas
        $errorMedidas = self::validarMedidas($_POST);
        if ($errorMedidas !== null) {
            $_SESSION['error_cliente'] = $errorMedidas;
            header('Location: ' . BASE_URL . '/index.php?page=ficha-cliente&id=' . $clienteId);
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