<?php

require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

$servername = $_ENV['servername'];
$username = $_ENV['username'];
$password = $_ENV['password'];
$database = $_ENV['database'];

// Try to establish connection
try {
    $conn = new mysqli($servername, $username, $password, $database);
    
    // Check if connection was successful
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    echo "Connection successful! Connected to database: " . $database . "<br>";
    
    // Optional: Run a simple test query
    $result = $conn->query("SELECT 1");
    if ($result) {
        echo "Query test successful!";
    }
    
    // Close connection
    $conn->close();
} catch (Exception $e) {
    die("Connection failed: " . $e->getMessage());
}
?>