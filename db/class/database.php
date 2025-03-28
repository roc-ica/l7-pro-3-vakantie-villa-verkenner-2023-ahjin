<?php

require_once __DIR__ . '/../../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
$dotenv->load();

class Database {
    private $servername;
    private $username;
    private $password;
    private $database;
    private $charset;
    private $dsn;


    public function __construct() {
        $this->servername = $_ENV['servername'];
        $this->username = $_ENV['username'];
        $this->password = $_ENV['password'];
        $this->database = $_ENV['database'];
        $this->charset = $_ENV['charset'];
        $this->dsn = "mysql:host={$this->servername};dbname={$this->database};charset={$this->charset}";
    }

    private function connect() {
        try {
            $conn = new PDO(
                $this->dsn,
           $this->username, 
           $this->password, 
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                          PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                          PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$this->charset}"
                          ]
            );
            return $conn;
        } catch (PDOException $e) {
            $logger = new ServerLogger($e->getMessage(), "error", "database", "connect");
            $logger->LogProb();
        }
    }

    private function close($conn) {
        $conn = null;
    }

    public function getConnection() {
        return $this->connect();
    }

    public function closeConnection($conn) {
        $this->close($conn);
    }

    /**
     * Begin a database transaction
     * 
     * @param PDO|null $conn Optional existing connection
     * @return PDO Database connection with active transaction
     */
    public function beginTransaction($conn = null) {
        if (!$conn) {
            $conn = $this->connect();
        }
        $conn->beginTransaction();
        return $conn;
    }
    
    /**
     * Commit a database transaction
     * 
     * @param PDO $conn Database connection with active transaction
     * @return bool Success status
     */
    public function commitTransaction($conn) {
        if ($conn && $conn->inTransaction()) {
            return $conn->commit();
        }
        return false;
    }
    
    /**
     * Rollback a database transaction
     * 
     * @param PDO $conn Database connection with active transaction
     * @return bool Success status
     */
    public function rollbackTransaction($conn) {
        if ($conn && $conn->inTransaction()) {
            return $conn->rollBack();
        }
        return false;
    }
    
    /**
     * Execute a callback function within a transaction
     * 
     * @param callable $callback Function to execute within transaction
     * @return mixed Result from the callback
     * @throws Exception Any exceptions from the callback
     */
    public function executeInTransaction(callable $callback) {
        $conn = $this->connect();
        
        try {
            $conn->beginTransaction();
            $result = $callback($conn);
            $conn->commit();
            return $result;
        } catch (PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            // Log the error
            try {
                $logger = new ServerLogger($e->getMessage(), "error", "database", "transaction");
                $logger->LogProb();
            } catch (Exception $logException) {
                // Prevent logging errors from hiding the original exception
            }
            throw $e;
        } finally {
            $this->close($conn);
        }
    }
}
