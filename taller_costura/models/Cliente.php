<?php
 
require_once __DIR__ . '/../config/database.php';
 
class Cliente {
 
    // ─── Atributos ────────────────────────────────────────────────────────────
    private int    $id;
    private string $nombre;
    private string $telefono;
    private string $email;
    private string $created_at;
 
    // ─── Constructor ──────────────────────────────────────────────────────────
    public function __construct(
        int    $id         = 0,
        string $nombre     = '',
        string $telefono   = '',
        string $email      = '',
        string $created_at = ''
    ) {
        $this->id         = $id;
        $this->nombre     = $nombre;
        $this->telefono   = $telefono;
        $this->email      = $email;
        $this->created_at = $created_at;
    }
 
    // ─── Getters ──────────────────────────────────────────────────────────────
    public function getId(): int           { return $this->id; }
    public function getNombre(): string    { return $this->nombre; }
    public function getTelefono(): string  { return $this->telefono; }
    public function getEmail(): string     { return $this->email; }
    public function getCreatedAt(): string { return $this->created_at; }
 
    // ─── Setters ──────────────────────────────────────────────────────────────
    public function setNombre(string $nombre): void     { $this->nombre   = $nombre; }
    public function setTelefono(string $tel): void      { $this->telefono = $tel; }
    public function setEmail(string $email): void       { $this->email    = $email; }
 
    // =========================================================================
    // CRUD
    // =========================================================================
 
    /**
     * Trae todos los clientes ordenados por nombre.
     * @return self[]
     */
    public static function getAll(): array {
        $pdo  = Database::getConnection();
        $stmt = $pdo->query(
            "SELECT id, nombre, telefono, email, created_at
             FROM cliente
             ORDER BY nombre"
        );
        $lista = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $lista[] = new self(
                (int)$row['id'],
                $row['nombre'],
                $row['telefono'] ?? '',
                $row['email']    ?? '',
                $row['created_at']
            );
        }
        return $lista;
    }
 
    /**
     * Busca un cliente por ID. Devuelve null si no existe.
     */
    public static function getById(int $id): ?self {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT id, nombre, telefono, email, created_at
             FROM cliente
             WHERE id = :id"
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
 
        return $row ? new self(
            (int)$row['id'],
            $row['nombre'],
            $row['telefono'] ?? '',
            $row['email']    ?? '',
            $row['created_at']
        ) : null;
    }
 
    /**
     * Busca clientes por nombre o email (búsqueda parcial).
     * Devuelve todos los que coincidan.
     * @return self[]
     */
    public static function buscar(string $termino): array {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT id, nombre, telefono, email, created_at
             FROM cliente
             WHERE nombre LIKE :termino
                OR email  LIKE :termino
             ORDER BY nombre"
        );
        $stmt->execute([':termino' => '%' . $termino . '%']);
 
        $lista = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $lista[] = new self(
                (int)$row['id'],
                $row['nombre'],
                $row['telefono'] ?? '',
                $row['email']    ?? '',
                $row['created_at']
            );
        }
        return $lista;
    }
 
    /**
     * Busca un cliente por email exacto.
     * Útil para validar duplicados antes de guardar.
     */
    public static function getByEmail(string $email): ?self {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            "SELECT id, nombre, telefono, email, created_at
             FROM cliente
             WHERE email = :email"
        );
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
 
        return $row ? new self(
            (int)$row['id'],
            $row['nombre'],
            $row['telefono'] ?? '',
            $row['email']    ?? '',
            $row['created_at']
        ) : null;
    }
 
    /**
     * Inserta este cliente en la base de datos.
     * Actualiza $this->id al terminar.
     * Devuelve false si el email ya está en uso.
     */
    public function guardar(): bool {
        // Validar email duplicado solo si se ingresó uno
        if ($this->email !== '' && self::getByEmail($this->email) !== null) {
            return false;
        }
 
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO cliente (nombre, telefono, email)
             VALUES (:nombre, :telefono, :email)"
        );
        $ok = $stmt->execute([
            ':nombre'   => $this->nombre,
            ':telefono' => $this->telefono ?: null,
            ':email'    => $this->email    ?: null,
        ]);
 
        if ($ok) {
            $this->id = (int) $pdo->lastInsertId();
        }
        return $ok;
    }
 
    /**
     * Actualiza los datos de este cliente en la base de datos.
     */
    public function actualizar(): bool {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare(
            "UPDATE cliente
             SET nombre   = :nombre,
                 telefono = :telefono,
                 email    = :email
             WHERE id = :id"
        );
        return $stmt->execute([
            ':nombre'   => $this->nombre,
            ':telefono' => $this->telefono ?: null,
            ':email'    => $this->email    ?: null,
            ':id'       => $this->id,
        ]);
    }
 
    /**
     * Elimina este cliente de la base de datos.
     */
    public function eliminar(): bool {
        $pdo  = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM cliente WHERE id = :id");
        return $stmt->execute([':id' => $this->id]);
    }
}
 