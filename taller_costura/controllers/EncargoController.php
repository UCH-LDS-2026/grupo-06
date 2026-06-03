<?php
require_once BASE_PATH . '/models/Encargo.php';
require_once BASE_PATH . '/models/Observacion.php';

class EncargoController {
    private $db;
    private $encargo;
    private $observacion;

    public function __construct($db) {
        $this->db = $db;
        $this->encargo = new Encargo($db);
        $this->observacion = new Observacion($db);
    }

    public function registrar() {
        $data = json_decode(file_get_contents("php://input"));
        $this->encargo->administrador_id       = $data->administrador_id ?? $_SESSION['admin_id'] ?? 1;
        $this->encargo->cliente_id             = $data->cliente_id ?? null;
        $this->encargo->tipo                   = $data->tipo;
        $this->encargo->descripcion            = $data->descripcion ?? null;
        $this->encargo->observaciones_encargo  = $data->observaciones_encargo ?? null;
        $this->encargo->fecha_entrega          = $data->fecha_entrega;
        $this->encargo->monto_total            = $data->monto_total ?? 0;
        $this->encargo->sena                   = $data->sena ?? 0;

        if ($this->encargo->create()) {
            http_response_code(201);
            echo json_encode(['mensaje' => 'Encargo registrado', 'id' => $this->encargo->id]);
        } else {
            http_response_code(500);
            echo json_encode(['mensaje' => 'Error al registrar encargo']);
        }
    }

    public function editar($id) {
        $data = json_decode(file_get_contents("php://input"));
        $this->encargo->id                     = $id;
        $this->encargo->cliente_id             = $data->cliente_id ?? null;
        $this->encargo->tipo                   = $data->tipo;
        $this->encargo->descripcion            = $data->descripcion ?? null;
        $this->encargo->observaciones_encargo  = $data->observaciones_encargo ?? null;
        $this->encargo->fecha_entrega          = $data->fecha_entrega;
        $this->encargo->monto_total            = $data->monto_total ?? 0;
        $this->encargo->sena                   = $data->sena ?? 0;
        $this->encargo->estado                 = $data->estado;

        if ($this->encargo->update()) {
            echo json_encode(['mensaje' => 'Encargo actualizado']);
        } else {
            http_response_code(500);
            echo json_encode(['mensaje' => 'Error al actualizar']);
        }
    }

    public function eliminar($id) {
        $this->encargo->id = $id;
        if ($this->encargo->delete()) {
            echo json_encode(['mensaje' => 'Encargo eliminado']);
        } else {
            http_response_code(500);
            echo json_encode(['mensaje' => 'Error al eliminar']);
        }
    }

    public function cambiarEstado($id) {
        $data = json_decode(file_get_contents("php://input"));
        $estadosValidos = ['pendiente', 'en_proceso', 'listo', 'entregado'];
        if (!in_array($data->estado ?? '', $estadosValidos)) {
            http_response_code(400);
            echo json_encode(['mensaje' => 'Estado inválido']);
            return;
        }
        $this->encargo->id     = $id;
        $this->encargo->estado = $data->estado;
        if ($this->encargo->cambiarEstado()) {
            echo json_encode(['mensaje' => 'Estado actualizado']);
        } else {
            http_response_code(500);
            echo json_encode(['mensaje' => 'Error al cambiar estado']);
        }
    }

    public function agregarObservacion($encargo_id) {
        $data = json_decode(file_get_contents("php://input"));
        if (empty($data->texto)) {
            http_response_code(400);
            echo json_encode(['mensaje' => 'Texto requerido']);
            return;
        }
        $this->observacion->encargo_id = $encargo_id;
        $this->observacion->texto      = $data->texto;
        if ($this->observacion->agregar()) {
            echo json_encode(['mensaje' => 'Observación agregada']);
        } else {
            http_response_code(500);
            echo json_encode(['mensaje' => 'Error al agregar observación']);
        }
    }
}
?>