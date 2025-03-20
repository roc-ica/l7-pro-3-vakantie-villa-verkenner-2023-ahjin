<?php
// A utility script to fix database permissions
header('Content-Type: text/plain');

// Database file path
$dbPath = __DIR__ . '/villaVerkenner.db';

echo "Checking and fixing permissions...\n\n";

// Check if database file exists
if (file_exists($dbPath)) {
    echo "Database file exists: " . $dbPath . "\n";
    echo "Current permissions: " . substr(sprintf('%o', fileperms($dbPath)), -4) . "\n";
    
    // Try to make it writable by everyone
    $result = chmod($dbPath, 0666);
    echo "Changing file permissions to 0666: " . ($result ? "Success" : "Failed") . "\n";
    echo "New permissions: " . substr(sprintf('%o', fileperms($dbPath)), -4) . "\n\n";
} else {
    echo "Database file does not exist.\n\n";
}

// Check database directory
$dbDir = __DIR__;
echo "Database directory: " . $dbDir . "\n";
echo "Current permissions: " . substr(sprintf('%o', fileperms($dbDir)), -4) . "\n";

// Try to make it writable by everyone
$result = chmod($dbDir, 0777);
echo "Changing directory permissions to 0777: " . ($result ? "Success" : "Failed") . "\n";
echo "New permissions: " . substr(sprintf('%o', fileperms($dbDir)), -4) . "\n\n";

// Check uploads directory
$uploadsDir = __DIR__ . '/../uploads/villas/';
if (!file_exists($uploadsDir)) {
    echo "Creating uploads directory...\n";
    $result = mkdir($uploadsDir, 0777, true);
    echo "Creation result: " . ($result ? "Success" : "Failed") . "\n\n";
} else {
    echo "Uploads directory exists: " . $uploadsDir . "\n";
    echo "Current permissions: " . substr(sprintf('%o', fileperms($uploadsDir)), -4) . "\n";
    
    // Try to make it writable by everyone
    $result = chmod($uploadsDir, 0777);
    echo "Changing directory permissions to 0777: " . ($result ? "Success" : "Failed") . "\n";
    echo "New permissions: " . substr(sprintf('%o', fileperms($uploadsDir)), -4) . "\n\n";
}

// Check uploads parent directory
$uploadsParentDir = __DIR__ . '/../uploads/';
if (file_exists($uploadsParentDir)) {
    echo "Uploads parent directory exists: " . $uploadsParentDir . "\n";
    echo "Current permissions: " . substr(sprintf('%o', fileperms($uploadsParentDir)), -4) . "\n";
    
    // Try to make it writable by everyone
    $result = chmod($uploadsParentDir, 0777);
    echo "Changing directory permissions to 0777: " . ($result ? "Success" : "Failed") . "\n";
    echo "New permissions: " . substr(sprintf('%o', fileperms($uploadsParentDir)), -4) . "\n\n";
}

echo "Permissions check and fix completed.\n";
echo "Please run the test_db.php script to verify the changes.\n";
?> 