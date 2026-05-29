<?php
class Encargo {
    private $conn;
    private $table = 'encargo';

    public $id;
    public $administrador_id;
    public $cliente_id;
    public $tipo;
    public $descripcion;
    public $observaciones_encargo;
    public $fecha_entrega;
    public $monto_total;
    public $sena;
    public $estado;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getAll() {
        $query = "SELECT e.*, c.nombre AS cliente_nombre 
                  FROM " . $this->table . " e
                  LEFT JOIN cliente c ON e.cliente_id = c.id
                  ORDER BY e.fecha_entrega ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function getByEstado($estado) {
        $query = "SELECT e.*, c.nombre AS cliente_nombre 
                  FROM " . $this->table . " e
                  LEFT JOIN cliente c ON e.cliente_id = c.id
                  WHERE e.estado = ?
                  ORDER BY e.fecha_entrega ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $estado);
        $stmt->execute();
        return $stmt;
    }

    public function getById() {
        $query = "SELECT e.*, c.nombre AS cliente_nombre 
                  FROM " . $this->table . " e
                  LEFT JOIN cliente c ON e.cliente_id = c.id
                  WHERE e.id = ? LIMIT 1";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function create() {
        $query = "INSERT INTO " . $this->table . " 
                  (administrador_id, cliente_id, tipo, descripcion, observaciones_encargo, fecha_entrega, monto_total, sena, estado)
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pendiente')";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->administrador_id);
        $stmt->bindParam(2, $this->cliente_id);
        $stmt->bindParam(3, $this->tipo);
        $stmt->bindParam(4, $this->descripcion);
        $stmt->bindParam(5, $this->observaciones_encargo);
        $stmt->bindParam(6, $this->fecha_entrega);
        $stmt->bindParam(7, $this->monto_total);
        $stmt->bindParam(8, $this->sena);
        if ($stmt->execute()) {
            $this->id = $this->conn->lastInsertId();
            return true;
        }
        return false;
    }

    public function update() {
        $query = "UPDATE " . $this->table . " 
                  SET cliente_id=?, tipo=?, descripcion=?, observaciones_encargo=?, 
                      fecha_entrega=?, monto_total=?, sena=?, estado=?
                  WHERE id=?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->cliente_id);
        $stmt->bindParam(2, $this->tipo);
        $stmt->bindParam(3, $this->descripcion);
        $stmt->bindParam(4, $this->observaciones_encargo);
        $stmt->bindParam(5, $this->fecha_entrega);
        $stmt->bindParam(6, $this->monto_total);
        $stmt->bindParam(7, $this->sena);
        $stmt->bindParam(8, $this->estado);
        $stmt->bindParam(9, $this->id);
        return $stmt->execute();
    }

    public function cambiarEstado() {
        $query = "UPDATE " . $this->table . " SET estado=? WHERE id=?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->estado);
        $stmt->bindParam(2, $this->id);
        return $stmt->execute();
    }

    public function delete() {
        $query = "DELETE FROM " . $this->table . " WHERE id=?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->id);
        return $stmt->execute();
    }

    public function calcularSaldo() {
        return $this->monto_total - $this->sena;
    }

    // devuelve cuantos dias faltan para la entrega
    public function calcularDemora() {
        $hoy = new DateTime();
        $entrega = new DateTime($this->fecha_entrega);
        $diff = $hoy->diff($entrega);
        return $diff->days;
    }
}
?>