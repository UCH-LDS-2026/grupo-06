<?php
require_once '../models/Encargo.php';
require_once '../config/database.php';

class EncargoController {
    private $db;
    private $encargo;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->encargo = new Encargo($this->db);
    }

    // Registrar encargo
    public function registrar() {
        $data = json_decode(file_get_contents("php://input"));

        $this->encargo->administrador_id = $data->administrador_id ?? 1;
        $this->encargo->cliente_id       = $data->cliente_id ?? null;
        $this->encargo->tipo             = $data->tipo;
        $this->encargo->descripcion      = $data->descripcion ?? null;
        $this->encargo->observaciones_encargo = $data->observaciones_encargo ?? null;
        $this->encargo->fecha_entrega    = $data->fecha_entrega;
        $this->encargo->monto_total      = $data->monto_total ?? 0;
        $this->encargo->sena             = $data->sena ?? 0;

        if ($this->encargo->create()) {
            http_response_code(201);
            echo json_encode(['mensaje' => 'Encargo registrado', 'id' => $this->encargo->id]);
        } else {
            http_response_code(500);
            echo json_encode(['mensaje' => 'Error al registrar encargo']);
        }
    }

    // Editar encargo
    public function editar($id) {
        $data = json_decode(file_get_contents("php://input"));

        $this->encargo->id               = $id;
        $this->encargo->cliente_id       = $data->cliente_id ?? null;
        $this->encargo->tipo             = $data->tipo;
        $this->encargo->descripcion      = $data->descripcion ?? null;
        $this->encargo->observaciones_encargo = $data->observaciones_encargo ?? null;
        $this->encargo->fecha_entrega    = $data->fecha_entrega;
        $this->encargo->monto_total      = $data->monto_total ?? 0;
        $this->encargo->sena             = $data->sena ?? 0;
        $this->encargo->estado           = $data->estado;

        if ($this->encargo->update()) {
            echo json_encode(['mensaje' => 'Encargo actualizado']);
        } else {
            http_response_code(500);
            echo json_encode(['mensaje' => 'Error al actualizar']);
        }
    }

    // Eliminar encargo
    public function eliminar($id) {
        $this->encargo->id = $id;
        if ($this->encargo->delete()) {
            echo json_encode(['mensaje' => 'Encargo eliminado']);
        } else {
            http_response_code(500);
            echo json_encode(['mensaje' => 'Error al eliminar']);
        }
    }
}