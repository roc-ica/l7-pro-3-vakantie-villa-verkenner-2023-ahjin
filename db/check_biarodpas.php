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
    
    echo "<h2>Checking for 'Biarodpas' villa</h2>";
    
    // Direct query with LIKE for title
    $stmt = $conn->prepare("SELECT * FROM villas WHERE titel LIKE :search");
    $stmt->bindValue(':search', '%Biarodpas%', PDO::PARAM_STR);
    $stmt->execute();
    
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Direct title search results: " . count($results) . "</h3>";
    if (count($results) > 0) {
        echo "<pre>";
        print_r($results);
        echo "</pre>";
    } else {
        echo "<p>No villa found with title containing 'Biarodpas'</p>";
    }
    
    // Search using straat, post_c, and plaatsnaam
    echo "<h3>Search in address fields</h3>";
    $stmt = $conn->prepare("SELECT * FROM villas WHERE straat LIKE :search OR post_c LIKE :search OR plaatsnaam LIKE :search");
    $stmt->bindValue(':search', '%Biarodpas%', PDO::PARAM_STR);
    $stmt->execute();
    
    $addressResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Address search results: " . count($addressResults) . "</h3>";
    if (count($addressResults) > 0) {
        echo "<pre>";
        print_r($addressResults);
        echo "</pre>";
    } else {
        echo "<p>No villa found with address fields containing 'Biarodpas'</p>";
    }
    
    // Check case sensitivity
    echo "<h3>Check with case-insensitive search</h3>";
    $stmt = $conn->prepare("SELECT * FROM villas WHERE LOWER(titel) LIKE LOWER(:search)");
    $stmt->bindValue(':search', '%biarodpas%', PDO::PARAM_STR);
    $stmt->execute();
    
    $caseResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<h3>Case-insensitive results: " . count($caseResults) . "</h3>";
    if (count($caseResults) > 0) {
        echo "<pre>";
        print_r($caseResults);
        echo "</pre>";
    } else {
        echo "<p>No villa found with case-insensitive title search for 'biarodpas'</p>";
    }
    
    // List all villas for comparison
    echo "<h3>All villas in database</h3>";
    $stmt = $conn->prepare("SELECT id, titel FROM villas");
    $stmt->execute();
    
    $allVillas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo "<p>Total villas: " . count($allVillas) . "</p>";
    echo "<ul>";
    foreach ($allVillas as $villa) {
        echo "<li>ID: " . $villa['id'] . " - Title: " . $villa['titel'] . "</li>";
    }
    echo "</ul>";
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p>Database query failed: " . $e->getMessage() . "</p>";
}
?> 