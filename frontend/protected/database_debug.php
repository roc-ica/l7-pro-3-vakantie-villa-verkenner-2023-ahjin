<?php
// Copy of your Database class with added debugging
class DatabaseDebug {
    private $servername;
    private $username;
    private $password;
    private $database;
    private $charset;
    private $dsn;

    public function __construct() {
        echo "<p>DatabaseDebug constructor called</p>";
        
        if (!isset($_ENV['servername']) || !isset($_ENV['username']) || 
            !isset($_ENV['password']) || !isset($_ENV['database'])) {
            echo "<p style='color:red'>ERROR: Missing required environment variables</p>";
            return;
        }
        
        $this->servername = $_ENV['servername'];
        $this->username = $_ENV['username'];
        $this->password = $_ENV['password'];
        $this->database = $_ENV['database'];
        $this->charset = isset($_ENV['charset']) ? $_ENV['charset'] : 'utf8mb4';
        $this->dsn = "mysql:host={$this->servername};dbname={$this->database};charset={$this->charset}";
        
        echo "<p>DSN created: " . str_replace($this->password, '[HIDDEN]', $this->dsn) . "</p>";
    }

    public function connect() {
        try {
            echo "<p>Connect method called</p>";
            
            // Check if the driver is available
            if (!in_array('mysql', PDO::getAvailableDrivers())) {
                echo "<p style='color:red'>ERROR: MySQL PDO driver is not installed or enabled</p>";
                return null;
            }

            echo "<p>Creating PDO connection...</p>";
            $conn = new PDO(
                $this->dsn,
                $this->username,
                $this->password,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                 PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC]
            );
            echo "<p style='color:green'>PDO connection created successfully</p>";
            return $conn;
        } catch (PDOException $e) {
            echo "<p style='color:red'>PDO Exception in connect(): " . htmlspecialchars($e->getMessage()) . "</p>";
            return null;
        }
    }

    public function getConnection() {
        echo "<p>getConnection method called</p>";
        return $this->connect();
    }
}

// Test the debug class
echo "<h1>Testing DatabaseDebug Class</h1>";
$dbDebug = new DatabaseDebug();
$conn = $dbDebug->getConnection();

if ($conn) {
    echo "<p style='color:green'>Connection successful!</p>";
} else {
    echo "<p style='color:red'>Connection failed!</p>";
}
?>