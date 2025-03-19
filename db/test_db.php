<?php
header('Content-Type: text/plain');

// Suppress warnings
error_reporting(0);

echo "Database Connectivity Test\n";
echo "=========================\n\n";

// Check PHP and SQLite versions
echo "PHP Version: " . PHP_VERSION . "\n";
echo "SQLite Extension Loaded: " . (extension_loaded('sqlite3') ? 'Yes' : 'No') . "\n";
echo "PDO SQLite Extension Loaded: " . (extension_loaded('pdo_sqlite') ? 'Yes' : 'No') . "\n\n";

// Check database directory
$dbDir = __DIR__;
echo "Database Directory: $dbDir\n";
echo "Database Directory Permissions: " . substr(sprintf('%o', fileperms($dbDir)), -4) . "\n";
echo "Attempting to update database directory permissions...\n";
$dirChmodResult = @chmod($dbDir, 0777);
echo "Directory permission update result: " . ($dirChmodResult ? 'Success' : 'Failed') . "\n";
echo "New database directory permissions: " . substr(sprintf('%o', fileperms($dbDir)), -4) . "\n\n";

// Check database file
$dbPath = $dbDir . '/villaVerkenner.db';
echo "Database Path: $dbPath\n";
echo "Database Exists: " . (file_exists($dbPath) ? 'Yes' : 'No') . "\n";

if (file_exists($dbPath)) {
    echo "Database Size: " . filesize($dbPath) . " bytes\n";
    echo "Database Readable: " . (is_readable($dbPath) ? 'Yes' : 'No') . "\n";
    echo "Database Writable: " . (is_writable($dbPath) ? 'Yes' : 'No') . "\n";
    echo "Database Permissions: " . substr(sprintf('%o', fileperms($dbPath)), -4) . "\n";
    
    // Update permissions to make the database writable by everyone
    echo "Attempting to update database permissions...\n";
    $chmodResult = @chmod($dbPath, 0666);
    echo "Permission update result: " . ($chmodResult ? 'Success' : 'Failed') . "\n";
    echo "New database permissions: " . substr(sprintf('%o', fileperms($dbPath)), -4) . "\n\n";
} else {
    echo "Database file does not exist. Please make sure it's created.\n\n";
}

// Test database connection
echo "Testing database connection...\n";
try {
    $db = new PDO('sqlite:' . $dbPath);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Connection successful!\n\n";
    
    // Check tables
    echo "Checking database tables...\n";
    $tables = $db->query("SELECT name FROM sqlite_master WHERE type='table' ORDER BY name")->fetchAll(PDO::FETCH_COLUMN);
    if (count($tables) > 0) {
        echo "Found tables: " . implode(', ', $tables) . "\n";
        
        // Check records in villas table
        $villaCount = $db->query("SELECT COUNT(*) FROM villas")->fetchColumn();
        echo "Number of villas in database: $villaCount\n";
    } else {
        echo "No tables found in database.\n";
    }
    
} catch (PDOException $e) {
    echo "Connection failed: " . $e->getMessage() . "\n";
}

// Check uploads directory
$uploadsDir = __DIR__ . '/../uploads/villas/';
echo "\nUploads Directory: $uploadsDir\n";
echo "Uploads Directory Exists: " . (file_exists($uploadsDir) ? 'Yes' : 'No') . "\n";

if (file_exists($uploadsDir)) {
    echo "Uploads Directory Readable: " . (is_readable($uploadsDir) ? 'Yes' : 'No') . "\n";
    echo "Uploads Directory Writable: " . (is_writable($uploadsDir) ? 'Yes' : 'No') . "\n";
    echo "Uploads Directory Permissions: " . substr(sprintf('%o', fileperms($uploadsDir)), -4) . "\n";
} else {
    echo "Attempting to create uploads directory...\n";
    $result = @mkdir($uploadsDir, 0777, true);
    echo "Directory creation result: " . ($result ? 'Success' : 'Failed') . "\n";
}

echo "\nTest completed.\n";
?> 