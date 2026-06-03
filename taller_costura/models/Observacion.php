<?php
class Observacion {
    private $conn;
    private $table = 'observacion';

    public $id;
    public $encargo_id;
    public $texto;
    public $fecha;

    public function __construct($db) {
        $this->conn = $db;
    }

    public function getByEncargo($encargo_id) {
        $query = "SELECT * FROM " . $this->table . " WHERE encargo_id = ? ORDER BY fecha ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $encargo_id);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function agregar() {
        $query = "INSERT INTO " . $this->table . " (encargo_id, texto, fecha) VALUES (?, ?, NOW())";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $this->encargo_id);
        $stmt->bindParam(2, $this->texto);
        return $stmt->execute();
    }
}
?>