<?php
require_once __DIR__ . '/config.php';
class Database
{
    private static $instance = null;
    private $connection;
    private function __construct()
    {
        $dbPath = dirname(__DIR__) . '/database/socialai.db';
        $dbDir = dirname($dbPath);
        if (!is_dir($dbDir)) {
            mkdir($dbDir, 0755, true);
        }
        $dsn = "sqlite:" . $dbPath;
        try {
            $this->connection = new PDO($dsn, null, null, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false
            ]);
            $this->connection->exec('PRAGMA foreign_keys = ON');
            $this->initializeSchema();
        } catch (PDOException $e) {
            error_log("DB Connection error: " . $e->getMessage());
            die("Błąd połączenia z bazą danych.");
        }
    }
    private function initializeSchema()
    {
        $result = $this->connection->query("SELECT name FROM sqlite_master WHERE type='table' AND name='users'");
        if ($result->fetch() === false) {
            $schemaFile = dirname(__DIR__) . '/database/schema_sqlite.sql';
            if (file_exists($schemaFile)) {
                $schema = file_get_contents($schemaFile);
                $this->connection->exec($schema);
            }
        }
    }
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    public function getConnection()
    {
        return $this->connection;
    }
    private function __clone()
    {
    }
    public function __wakeup()
    {
        throw new Exception("Cannot unserialize singleton");
    }
}
