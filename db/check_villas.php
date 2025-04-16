<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
require_once __DIR__ . "/class/database.php";

try {
    // Create database instance
    $db = new Database();
    $conn = $db->getConnection();
    
    echo "<h2>All Villas</h2>";
    
    // Query to get all villas
    $stmt = $conn->prepare("SELECT id, titel, oppervlakte, plaatsnaam FROM villas");
    $stmt->execute();
    
    echo "<table border='1'>
        <tr>
            <th>ID</th>
            <th>Title</th>
            <th>Area (m²)</th>
            <th>Location</th>
        </tr>";
    
    // Display all villas
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>
            <td>{$row['id']}</td>
            <td>{$row['titel']}</td>
            <td>{$row['oppervlakte']}</td>
            <td>{$row['plaatsnaam']}</td>
        </tr>";
    }
    
    echo "</table>";
    
    // Search for Biarodpas villa
    echo "<h2>Search for 'Biarodpas'</h2>";
    $search = "Biarodpas";
    $stmt = $conn->prepare("SELECT id, titel, oppervlakte, plaatsnaam FROM villas WHERE titel LIKE :search");
    $stmt->bindValue(':search', "%$search%", PDO::PARAM_STR);
    $stmt->execute();
    
    $searchResults = $stmt->fetchAll(PDO::FETCH_ASSOC);
    if (count($searchResults) > 0) {
        echo "<table border='1'>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Area (m²)</th>
                <th>Location</th>
            </tr>";
        
        foreach ($searchResults as $row) {
            echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['titel']}</td>
                <td>{$row['oppervlakte']}</td>
                <td>{$row['plaatsnaam']}</td>
            </tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No villas found with title containing 'Biarodpas'</p>";
    }
    
    // Get min and max area
    $stmt = $conn->prepare("SELECT MIN(oppervlakte) as min_area, MAX(oppervlakte) as max_area FROM villas");
    $stmt->execute();
    $areaLimits = $stmt->fetch(PDO::FETCH_ASSOC);
    
    echo "<h2>Area Limits</h2>";
    echo "<p>Minimum Area: {$areaLimits['min_area']} m²</p>";
    echo "<p>Maximum Area: {$areaLimits['max_area']} m²</p>";
    
} catch (Exception $e) {
    echo "<h2>Error</h2>";
    echo "<p>Database connection failed: " . $e->getMessage() . "</p>";
}
?> 