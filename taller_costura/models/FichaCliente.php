<?php
 
require_once __DIR__ . '/../config/database.php';
 
class FichaCliente {
 
    // ─── Atributos ────────────────────────────────────────────────────────────
    private int    $id;
    private int    $cliente_id;
    private string $talle;
    private ?float $contorno_pecho;
    private ?float $contorno_cintura;
    private ?float $contorno_cadera;
    private ?float $largo_manga;
    private string $observaciones_cliente;
    private string $updated_at;
 
    // ─── Constructor ──────────────────────────────────────────────────────────
    public function __construct(
        int     $id                   = 0,
        int     $cliente_id           = 0,
        string  $talle                = '',
        ?float  $contorno_pecho       = null,
        ?float  $contorno_cintura     = null,
        ?float  $contorno_cadera      = null,
        ?float  $largo_manga          = null,
        string  $observaciones_cliente = '',
        string  $updated_at           = ''
    ) {
        $this->id                    = $id;
        $this->cliente_id            = $cliente_id;
        $this->talle                 = $talle;
        $this->contorno_pecho        = $contorno_pecho;
        $this->contorno_cintura      = $contorno_cintura;
        $this->contorno_cadera       = $contorno_cadera;
        $this->largo_manga           = $largo_manga;
        $this->observaciones_cliente = $observaciones_cliente;
        $this->updated_at            = $updated_at;
    }
 
    // ─── Getters ──────────────────────────────────────────────────────────────
    public function getId(): int                    { return $this->id; }
    public function getClienteId(): int             { return $this->cliente_id; }
    public function getTalle(): string              { return $this->talle; }
    public function getContornoPecho(): ?float      { return $this->contorno_pecho; }
    public function getContornoCintura(): ?float    { return $this->contorno_cintura; }
    public function getContornoCadera(): ?float     { return $this->contorno_cadera; }
    public function getLargoManga(): ?float         { return $this->largo_manga; }
    public function getObservaciones(): string      { return $this->observaciones_cliente; }
    public function getUpdatedAt(): string          { return $this->updated_at; }
 
    // ─── Setters ──────────────────────────────────────────────────────────────
    public function setTalle(string $talle): void                   { $this->talle                 = $talle; }
    public function setContornoPecho(?float $v): void               { $this->contorno_pecho        = $v; }
    public function setContornoCintura(?float $v): void             { $this->contorno_cintura      = $v; }
    public function setContornoCadera(?float $v): void              { $this->contorno_cadera       = $v; }
    public function setLargoManga(?float $v): void                  { $this->largo_manga           = $v; }
    public function setObservaciones(string $obs): void             { $this->observaciones_cliente = $obs; }
 
    // =========================================================================
    // CONSULTAS
    // =========================================================================
 
    /**
     * Busca la ficha de un cliente por su cliente_id.
     * Devuelve null si el cliente todavía no tiene ficha.
     */
    public static function getByClienteId(int $cliente_id): ?self {
        $pdo  = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare(
            "SELECT id, cliente_id, talle, contorno_pecho, contorno_cintura,
                    contorno_cadera, largo_manga, observaciones_cliente, updated_at
             FROM ficha_cliente
             WHERE cliente_id = :cliente_id"
        );
        $stmt->execute([':cliente_id' => $cliente_id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
 
        return $row ? new self(
            (int)$row['id'],
            (int)$row['cliente_id'],
            $row['talle']                 ?? '',
            isset($row['contorno_pecho'])   ? (float)$row['contorno_pecho']   : null,
            isset($row['contorno_cintura']) ? (float)$row['contorno_cintura'] : null,
            isset($row['contorno_cadera'])  ? (float)$row['contorno_cadera']  : null,
            isset($row['largo_manga'])      ? (float)$row['largo_manga']      : null,
            $row['observaciones_cliente'] ?? '',
            $row['updated_at']            ?? ''
        ) : null;
    }
 
    /**
     * Busca una ficha por su propio ID.
     * Devuelve null si no existe.
     */
    public static function getById(int $id): ?self {
        $pdo  = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare(
            "SELECT id, cliente_id, talle, contorno_pecho, contorno_cintura,
                    contorno_cadera, largo_manga, observaciones_cliente, updated_at
             FROM ficha_cliente
             WHERE id = :id"
        );
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
 
        return $row ? new self(
            (int)$row['id'],
            (int)$row['cliente_id'],
            $row['talle']                 ?? '',
            isset($row['contorno_pecho'])   ? (float)$row['contorno_pecho']   : null,
            isset($row['contorno_cintura']) ? (float)$row['contorno_cintura'] : null,
            isset($row['contorno_cadera'])  ? (float)$row['contorno_cadera']  : null,
            isset($row['largo_manga'])      ? (float)$row['largo_manga']      : null,
            $row['observaciones_cliente'] ?? '',
            $row['updated_at']            ?? ''
        ) : null;
    }
 
    // =========================================================================
    // GUARDAR / ACTUALIZAR
    // =========================================================================
 
    /**
     * Guarda o actualiza la ficha del cliente.
     * Si el cliente ya tiene ficha la actualiza (UPDATE),
     * si no tiene ficha la crea (INSERT).
     * Actualiza $this->id al terminar.
     */
    public function guardarOActualizar(): bool {
        $existente = self::getByClienteId($this->cliente_id);
 
        if ($existente !== null) {
            // Ya tiene ficha → UPDATE
            $this->id = $existente->getId();
            return $this->actualizar();
        }
 
        // No tiene ficha → INSERT
        return $this->guardar();
    }
 
    /**
     * Inserta una ficha nueva en la base de datos.
     * Actualiza $this->id al terminar.
     */
    private function guardar(): bool {
        $pdo  = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare(
            "INSERT INTO ficha_cliente
                (cliente_id, talle, contorno_pecho, contorno_cintura,
                 contorno_cadera, largo_manga, observaciones_cliente)
             VALUES
                (:cliente_id, :talle, :contorno_pecho, :contorno_cintura,
                 :contorno_cadera, :largo_manga, :observaciones_cliente)"
        );
        $ok = $stmt->execute($this->toParams());
 
        if ($ok) {
            $this->id = (int) $pdo->lastInsertId();
        }
        return $ok;
    }
 
    /**
     * Actualiza la ficha existente en la base de datos.
     * updated_at se actualiza automáticamente por la BD (ON UPDATE CURRENT_TIMESTAMP).
     */
    private function actualizar(): bool {
        $pdo  = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare(
            "UPDATE ficha_cliente
             SET talle                 = :talle,
                 contorno_pecho        = :contorno_pecho,
                 contorno_cintura      = :contorno_cintura,
                 contorno_cadera       = :contorno_cadera,
                 largo_manga           = :largo_manga,
                 observaciones_cliente = :observaciones_cliente
             WHERE cliente_id = :cliente_id"
        );
        return $stmt->execute($this->toParams());
    }
 
    /**
     * Elimina la ficha de este cliente.
     */
    public function eliminar(): bool {
        $pdo  = Database::getInstance()->getConnection();
        $stmt = $pdo->prepare("DELETE FROM ficha_cliente WHERE cliente_id = :cliente_id");
        return $stmt->execute([':cliente_id' => $this->cliente_id]);
    }
 
    // =========================================================================
    // HELPERS
    // =========================================================================
 
    /**
     * Arma el array de parámetros para INSERT y UPDATE.
     */
    private function toParams(): array {
        return [
            ':cliente_id'            => $this->cliente_id,
            ':talle'                 => $this->talle    ?: null,
            ':contorno_pecho'        => $this->contorno_pecho,
            ':contorno_cintura'      => $this->contorno_cintura,
            ':contorno_cadera'       => $this->contorno_cadera,
            ':largo_manga'           => $this->largo_manga,
            ':observaciones_cliente' => $this->observaciones_cliente ?: null,
        ];
    }
}