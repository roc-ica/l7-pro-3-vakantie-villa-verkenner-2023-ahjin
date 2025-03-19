<?php
// This is a sample file to demonstrate how to insert a villa

// Include database functions
include_once '../database.php';

// Sample villa data
$straat = "Zonnelaan 123";
$post_c = "1234 AB";
$kamers = 5;
$badkamers = 2;
$slaapkamers = 3;
$oppervlakte = 180.5;
$prijs = 750000;

try {
    // Insert the villa
    $villa_id = insert_villa($straat, $post_c, $kamers, $badkamers, $slaapkamers, $oppervlakte, $prijs);
    
    echo "Villa successfully inserted with ID: $villa_id<br>";
    
    // Optional: Retrieve the villa to verify it was inserted correctly
    $villa = get_villa($villa_id);
    
    // Display the villa details
    echo "<h2>Villa Details:</h2>";
    echo "<pre>";
    print_r($villa);
    echo "</pre>";
    
} catch (PDOException $e) {
    echo "Error inserting villa: " . $e->getMessage();
}
?>
