<?php
// Turn off output buffering to prevent headers already sent errors
ob_start();

// session_start(); // Removed this line
require_once __DIR__ . '/../../db/class/database.php';
require_once __DIR__ . '/../../db/class/login.php';
require_once __DIR__ . '/../../db/class/sessions.php';

// Create a debug log file
$logFile = __DIR__ . '/login_debug.log';
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Login process started\n", FILE_APPEND);

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 0); // Don't display errors to browser
ini_set('log_errors', 1);
ini_set('error_log', $logFile);

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Invalid request method: {$_SERVER['REQUEST_METHOD']}\n", FILE_APPEND);
    header('Location: login.php?error=invalid_request');
    exit;
}

// Get username and password from POST data
$username = trim($_POST['username'] ?? '');
$password = trim($_POST['password'] ?? '');

// Log received data (without password)
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Received username: " . (isset($username) ? $username : 'not set') . "\n", FILE_APPEND);

// Basic validation: check if username and password are provided
if (empty($username) || empty($password)) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Missing fields\n", FILE_APPEND);
    header('Location: login.php?error=missing_fields');
    exit;
}

// Initialize database connection
file_put_contents($logFile, date('Y-m-d H:i:s') . " - Initializing database connection\n", FILE_APPEND);
$db = new Database();
$conn = $db->getConnection();

if (!$conn) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Database connection failed\n", FILE_APPEND);
    error_log("Login process: Database connection failed.");
    header('Location: login.php?error=dberror');
    exit;
}

try {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Creating login instance\n", FILE_APPEND);
    // Create login instance and attempt login
    $loginHandler = new Login($conn, $username, $password);
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Attempting login\n", FILE_APPEND);
    $loginResult = $loginHandler->login();
    
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Login result: " . json_encode($loginResult) . "\n", FILE_APPEND);
    
    if ($loginResult['success']) {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Login successful, redirecting to dashboard\n", FILE_APPEND);
        // Redirect to dashboard on successful login
        header('Location: admin.php');
        exit;
    } else {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Login failed: " . $loginResult['message'] . "\n", FILE_APPEND);
        // Redirect back to login with error
        if ($loginResult['message'] === 'Database error during login') {
            header('Location: login.php?error=dberror');
        } else {
            header('Location: login.php?error=invalid'); // Invalid credentials
        }
        exit;
    }
} catch (Exception $e) {
    file_put_contents($logFile, date('Y-m-d H:i:s') . " - Exception: " . $e->getMessage() . "\n", FILE_APPEND);
    error_log("Login error: " . $e->getMessage());
    header('Location: login.php?error=server_error');
    exit;
} finally {
    // Close database connection
    if ($conn) {
        file_put_contents($logFile, date('Y-m-d H:i:s') . " - Closing database connection\n", FILE_APPEND);
        $db->closeConnection($conn);
    }
    
    // End output buffering and flush
    ob_end_flush();
}
