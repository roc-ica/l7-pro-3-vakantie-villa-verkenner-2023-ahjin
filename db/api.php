<?php

require_once __DIR__ . '/class/database.php';
require_once __DIR__ . '/class/serverlogger.php';
require_once __DIR__ . '/func/api_functions.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Check if PDO MySQL driver is available
    if (!in_array('mysql', PDO::getAvailableDrivers())) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'error' => 'MySQL PDO driver is not installed or enabled on the server'
        ]);
        exit;
    }
    
    $db = new Database();
    $conn = $db->getConnection();
    
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    
    $apiHelper = new ApiHelper();
    // Process API request
    $result = $apiHelper->ProccessApi();
    
    // Output result as JSON
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'data' => $result
    ]);
    
} catch (Exception $e) {
    // Log the error
    error_log("API Error: " . $e->getMessage());
    
    // Return error response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}

?>