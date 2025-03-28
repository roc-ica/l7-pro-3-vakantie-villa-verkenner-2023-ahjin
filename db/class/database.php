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
}
