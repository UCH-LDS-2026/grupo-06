<?php
/**
 * Archivo de conexión a la base de datos
 * Utiliza PDO para una conexión segura
 */

require_once 'config.php';

class Database {
    private static $instance = null;
    private $pdo;

    private function __construct() {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME . ";charset=utf8mb4";
            
            $this->pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ]);
        } catch (PDOException $e) {
            die('Error en la conexión: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene la instancia única de la conexión (Singleton)
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Retorna la conexión PDO
     */
    public function getConnection() {
        return $this->pdo;
    }

    /**
     * Ejecuta una consulta preparada
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            die('Error en la consulta: ' . $e->getMessage());
        }
    }

    /**
     * Obtiene todos los resultados de una consulta
     */
    public function fetchAll($sql, $params = []) {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Obtiene un registro de una consulta
     */
    public function fetch($sql, $params = []) {
        return $this->query($sql, $params)->fetch();
    }

    /**
     * Obtiene la cantidad de filas afectadas
     */
    public function rowCount($sql, $params = []) {
        return $this->query($sql, $params)->rowCount();
    }

    /**
     * Obtiene el ID de la última inserción
     */
    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
}

// Crear una instancia global para facilitar el acceso
$db = Database::getInstance();
?>
