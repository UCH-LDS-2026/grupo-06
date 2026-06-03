<?php
 
require_once __DIR__ . '/../config/database.php';
 
class Administrador {
 
    // ─── Atributos ────────────────────────────────────────────────────────────
    private int    $id;
    private string $nombre;
    private string $email;
    private string $contrasena;   // siempre almacenado como hash bcrypt
    private string $created_at;
 
    // ─── Constructor ──────────────────────────────────────────────────────────
    public function __construct(
        int    $id         = 0,
        string $nombre     = '',
        string $email      = '',
        string $contrasena = '',
        string $created_at = ''
    ) {
        $this->id         = $id;
        $this->nombre     = $nombre;
        $this->email      = $email;
        $this->contrasena = $contrasena;
        $this->created_at = $created_at;
    }
 
    // ─── Getters ──────────────────────────────────────────────────────────────
    public function getId(): int          { return $this->id; }
    public function getNombre(): string   { return $this->nombre; }
    public function getEmail(): string    { return $this->email; }
    public function getCreatedAt(): string { return $this->created_at; }
 
    // ─── Setters ──────────────────────────────────────────────────────────────
    public function setNombre(string $nombre): void { $this->nombre = $nombre; }
    public function setEmail(string $email): void   { $this->email  = $email; }
 
    // =========================================================================
    // AUTENTICACIÓN
    // =========================================================================
 
    /**
     * Inicia sesión con email y contraseña.
     * Devuelve el objeto Administrador si las credenciales son válidas, null si no.
     */
    public static function login(string $email, string $password): ?self {
        $pdo  = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare(
            "SELECT id, nombre, email, contrasena, created_at
             FROM administrador
             WHERE email = :email
             LIMIT 1"
        );
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
 
        if (!$row) {
            return null; // email no encontrado
        }
 
        if (!password_verify($password, $row['contrasena'])) {
            return null; // contraseña incorrecta
        }
 
        return new self(
            (int) $row['id'],
            $row['nombre'],
            $row['email'],
            $row['contrasena'],
            $row['created_at']
        );
    }
 
    // =========================================================================
    // CRUD
    // =========================================================================
 
    /**
     * Trae todos los administradores ordenados por nombre.
     * @return self[]
     */
    public static function getAll(): array {
        $pdo  = Database::getInstance()->getConnection();
        $stmt = $pdo->query(
            "SELECT id, nombre, email, contrasena, created_at
             FROM administrador
             ORDER BY nombre"
        );
        $lista = [];
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $lista[] = new self(
                (int)$row['id'],
                $row['nombre'],
                $row['email'],
                $row['contrasena'],
                $row['created_at']
            );
        }
        return $lista;
    }
 
    /**
     * Busca un administrador por ID. Devuelve null si no existe.
     */
    public static function getById(int $id): ?self {
        $pdo  = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare(
            "SELECT id, nombre, email, contrasena, created_at
             FROM administrador
             WHERE id = :id"
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
 
        return $row ? new self(
            (int)$row['id'],
            $row['nombre'],
            $row['email'],
            $row['contrasena'],
            $row['created_at']
        ) : null;
    }
 
    /**
     * Busca un administrador por email. Devuelve null si no existe.
     * Útil para validar duplicados antes de guardar.
     */
    public static function getByEmail(string $email): ?self {
        $pdo  = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare(
            "SELECT id, nombre, email, contrasena, created_at
             FROM administrador
             WHERE email = :email"
        );
        $stmt->execute([':email' => $email]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
 
        return $row ? new self(
            (int)$row['id'],
            $row['nombre'],
            $row['email'],
            $row['contrasena'],
            $row['created_at']
        ) : null;
    }
 
    /**
     * Inserta este administrador en la base de datos.
     * Recibe la contraseña en texto plano, la hashea internamente.
     * Actualiza $this->id al terminar.
     */
    public function guardar(string $passwordPlano): bool {
        // Verificar que el email no esté en uso
        if (self::getByEmail($this->email) !== null) {
            return false; // email duplicado
        }
 
        $pdo  = Database::getInstance()->getConnection();
        $hash = password_hash($passwordPlano, PASSWORD_BCRYPT);
 
        $stmt = $pdo->prepare(
            "INSERT INTO administrador (nombre, email, contrasena)
             VALUES (:nombre, :email, :contrasena)"
        );
        $ok = $stmt->execute([
            ':nombre'     => $this->nombre,
            ':email'      => $this->email,
            ':contrasena' => $hash,
        ]);
 
        if ($ok) {
            $this->id         = (int) $pdo->lastInsertId();
            $this->contrasena = $hash;
        }
        return $ok;
    }
 
    /**
     * Actualiza nombre y email del administrador.
     */
    public function actualizar(): bool {
        $pdo  = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare(
            "UPDATE administrador
             SET nombre = :nombre, email = :email
             WHERE id = :id"
        );
        return $stmt->execute([
            ':nombre' => $this->nombre,
            ':email'  => $this->email,
            ':id'     => $this->id,
        ]);
    }
 
    /**
     * Cambia la contraseña verificando primero la actual.
     * Devuelve false si la contraseña actual no coincide.
     */
    public function cambiarContrasena(string $contrasenaActual, string $nuevaPlano): bool {
        if (!password_verify($contrasenaActual, $this->contrasena)) {
            return false;
        }
 
        $nuevoHash = password_hash($nuevaPlano, PASSWORD_BCRYPT);
        $pdo       = Database::getInstance()->getConnection();
        $stmt      = $pdo->prepare(
            "UPDATE administrador SET contrasena = :contrasena WHERE id = :id"
        );
        $ok = $stmt->execute([':contrasena' => $nuevoHash, ':id' => $this->id]);
 
        if ($ok) {
            $this->contrasena = $nuevoHash;
        }
        return $ok;
    }
 
    /**
     * Elimina este administrador de la base de datos.
     */
    public function eliminar(): bool {
        $pdo  = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("DELETE FROM administrador WHERE id = :id");
        return $stmt->execute([':id' => $this->id]);
    }
}