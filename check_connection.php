<?php

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include the database class
require_once __DIR__ . '/db/class/database.php';

// Create a database instance
$db = new Database();

// Try to connect and report status
try {
    $conn = $db->getConnection();
    if ($conn) {
        echo "Connection successful\n";
        echo "PDO drivers available: ";
        print_r(PDO::getAvailableDrivers());
    } else {
        echo "Connection failed\n";
    }
} catch (PDOException $e) {
    echo "Connection error: " . $e->getMessage() . "\n";
}
?> 