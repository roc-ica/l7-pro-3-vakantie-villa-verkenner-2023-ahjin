<?php

require_once __DIR__ . '/class/database.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Admin credentials - change these to your preferred values
$username = 'admint';
$password = password_hash('admin123', PASSWORD_DEFAULT); // Hash the password
$created_at = date('Y-m-d H:i:s');
$session_id = ''; // Initial empty session_id

try {
    // Create database connection
    $db = new Database();
    $conn = $db->getConnection();
    
    // Prepare and execute the statement
    $stmt = $conn->prepare("INSERT INTO admin (username, password, created_at, session_id) 
                           VALUES (:username, :password, :created_at, :session_id)");
    
    $stmt->execute([
        ':username' => $username,
        ':password' => $password,
        ':created_at' => $created_at,
        ':session_id' => $session_id
    ]);
    
    $adminId = $conn->lastInsertId();
    
    echo "Admin user created successfully with ID: " . $adminId;
    
} catch (PDOException $e) {
    echo "Error creating admin user: " . $e->getMessage();
} finally {
    // Close the connection
    if (isset($conn)) {
        $db->closeConnection($conn);
    }
}
