<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
require_once __DIR__ . "/class/database.php";

try {
    // Create database instance
    $db = new Database();
    $conn = $db->getConnection();
    
    // Check if migrations table exists
    $tableExists = false;
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    
    echo "Available tables:<br>";
    foreach ($tables as $table) {
        echo "- " . $table . "<br>";
    }
    
    if (in_array('migrations', $tables)) {
        echo "<br>Migrations table exists!<br>";
        
        // Check structure
        $columns = $conn->query("DESCRIBE migrations")->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<br>Structure of migrations table:<br>";
        echo "<pre>";
        print_r($columns);
        echo "</pre>";
    } else {
        echo "<br>Migrations table does not exist!<br>";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "<br>";
}
?> 