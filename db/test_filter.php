<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include necessary classes
require_once __DIR__ . "/class/database.php";
require_once __DIR__ . "/class/filter.php";

// Function to print results
function printResults($results, $title) {
    echo "<h2>" . $title . "</h2>";
    echo "<p>Found " . count($results) . " results</p>";
    
    if (count($results) > 0) {
        echo "<table border='1'>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Address</th>
                <th>Area (m²)</th>
                <th>Price (€)</th>
                <th>Features</th>
                <th>Locations</th>
            </tr>";
        
        foreach ($results as $row) {
            echo "<tr>
                <td>" . $row['id'] . "</td>
                <td>" . $row['titel'] . "</td>
                <td>" . $row['straat'] . ", " . $row['post_c'] . ", " . $row['plaatsnaam'] . "</td>
                <td>" . $row['oppervlakte'] . "</td>
                <td>" . number_format($row['prijs'], 0, ',', '.') . "</td>
                <td>" . ($row['features'] ?? 'None') . "</td>
                <td>" . ($row['locations'] ?? 'None') . "</td>
            </tr>";
        }
        
        echo "</table>";
    } else {
        echo "<p>No results found</p>";
    }
}

// Test cases
$testCases = [
    "Basic Search for Biarodpas" => [
        'zoekterm' => 'Biarodpas',
        'min_price' => 0,
        'max_price' => 5000000,
        'min_area' => 0,
        'max_area' => 2000,
        'kamers' => 0,
        'slaapkamers' => 0,
        'badkamers' => 0
    ],
    "Biarodpas with Low Max Area" => [
        'zoekterm' => 'Biarodpas',
        'min_price' => 0,
        'max_price' => 5000000,
        'min_area' => 0,
        'max_area' => 1000,  // Biarodpas is 1200m²
        'kamers' => 0,
        'slaapkamers' => 0,
        'badkamers' => 0
    ],
    "Exact Room Match" => [
        'zoekterm' => 'Biarodpas',
        'min_price' => 0,
        'max_price' => 5000000,
        'min_area' => 0,
        'max_area' => 2000,
        'kamers' => 3,  // Biarodpas has 3 rooms
        'slaapkamers' => 0,
        'badkamers' => 0
    ],
    "Wrong Room Match" => [
        'zoekterm' => 'Biarodpas',
        'min_price' => 0,
        'max_price' => 5000000,
        'min_area' => 0,
        'max_area' => 2000,
        'kamers' => 4,  // Biarodpas has 3 rooms
        'slaapkamers' => 0,
        'badkamers' => 0
    ],
    "General Filter Test" => [
        'zoekterm' => '',
        'min_price' => 0,
        'max_price' => 5000000,
        'min_area' => 0,
        'max_area' => 2000,
        'kamers' => 3,
        'slaapkamers' => 0,
        'badkamers' => 0
    ]
];

echo "<h1>Filter Test Results</h1>";

// Run tests
foreach ($testCases as $title => $filters) {
    $filter = new Filter($filters);
    $results = $filter->getFilteredVillas();
    printResults($results, $title);
    echo "<hr>";
}
?> 