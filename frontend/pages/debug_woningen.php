<?php
// Enable error reporting for all errors
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Output a debug message
echo "Debug started<br>";

// Try to include the database and filter classes
try {
    require_once __DIR__ . '/../../db/class/database.php';
    echo "Database class loaded successfully<br>";
    
    require_once __DIR__ . '/../../db/class/filter.php';
    echo "Filter class loaded successfully<br>";

    // Try to create a Filter instance
    $filters = [];
    $filterHandler = new Filter($filters);
    echo "Filter handler created successfully<br>";
    
    // Try to get properties
    $featureOptions = $filterHandler->getFeatureOptions();
    echo "Feature options retrieved: " . count($featureOptions) . "<br>";
    
    $locationOptions = $filterHandler->getLocationOptions();
    echo "Location options retrieved: " . count($locationOptions) . "<br>";
    
    $properties = $filterHandler->getFilteredVillas();
    echo "Properties retrieved: " . count($properties) . "<br>";
    
    // Debug database connection directly
    $db = new Database();
    $conn = $db->getConnection();
    
    if ($conn) {
        echo "Database connection successful<br>";
        
        // Test a simple query
        try {
            $stmt = $conn->query("SHOW TABLES");
            echo "Tables in database:<br>";
            echo "<ul>";
            while ($table = $stmt->fetch(PDO::FETCH_COLUMN)) {
                echo "<li>" . htmlspecialchars($table) . "</li>";
            }
            echo "</ul>";
        } catch (Exception $e) {
            echo "Error running query: " . htmlspecialchars($e->getMessage()) . "<br>";
        }
    } else {
        echo "Database connection failed<br>";
    }
    
} catch (Exception $e) {
    echo "Error: " . htmlspecialchars($e->getMessage()) . "<br>";
    echo "File: " . htmlspecialchars($e->getFile()) . "<br>";
    echo "Line: " . $e->getLine() . "<br>";
}

echo "Debug completed";
?> 