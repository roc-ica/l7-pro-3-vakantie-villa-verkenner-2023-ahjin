<?php

require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

class Database {
    private $servername;
    private $username;
    private $password;
    private $database;

    public function __construct() {
        $this->servername = $_ENV['servername'];
        $this->username = $_ENV['username'];
        $this->password = $_ENV['password'];
        $this->database = $_ENV['database'];
    }

    public function connect() {
        $conn = new mysqli(
            $this->servername, 
            $this->username, 
            $this->password, 
            $this->database
        );

        if ($conn->connect_error) {
            die("Connection failed: " . $conn->connect_error);
        }

        return $conn;
    }

    public function close($conn) {
        $conn->close();
    }
}