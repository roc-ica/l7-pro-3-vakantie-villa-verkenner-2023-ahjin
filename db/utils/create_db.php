<?php
/**
 * Database Creation Script
 * 
 * Creates all necessary tables for the VillaVerkenner application
 * 
 * Usage: php create_db.php
 */

// Set error reporting to show all errors in development
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database path
$dbPath = __DIR__ . '/../villaVerkenner.db';
$dbDir = dirname($dbPath);

// Header
echo "===================================\n";
echo "VillaVerkenner Database Setup Tool\n";
echo "===================================\n\n";

// Check directory permissions
if (!is_writable($dbDir)) {
    echo "Database directory ($dbDir) is not writable.\n";
    echo "Attempting to fix permissions...\n";
    
    if (!@chmod($dbDir, 0777)) {
        echo "Failed to set directory permissions. Please manually ensure the directory is writable.\n";
        exit(1);
    }
    
    echo "Directory permissions updated successfully.\n";
}

try {
    // Connect to database
    echo "Connecting to database...\n";
    $pdo = new PDO('sqlite:' . $dbPath);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Set proper permissions for the database file if it's newly created
    if (file_exists($dbPath)) {
        chmod($dbPath, 0666); // Ensure it's readable and writable
    }
    
    echo "Connection established.\n\n";
    
    // Table creation array with structure and description
    $tables = [
        'villas' => [
            'description' => 'Main villa listings table with property details',
            'sql' => "CREATE TABLE IF NOT EXISTS villas (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                straat TEXT NOT NULL,
                post_c TEXT NOT NULL,
                kamers SMALLINT NOT NULL,
                badkamers SMALLINT NOT NULL,
                slaapkamers SMALLINT NOT NULL,
                oppervlakte REAL NOT NULL,
                prijs INTEGER NOT NULL
            )"
        ],
        'labels' => [
            'description' => 'Villa property labels/tags',
            'sql' => "CREATE TABLE IF NOT EXISTS labels (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                naam TEXT NOT NULL
            )"
        ],
        'villa_labels' => [
            'description' => 'Junction table linking villas to labels',
            'sql' => "CREATE TABLE IF NOT EXISTS villa_labels (
                villa_id INTEGER NOT NULL,
                label_id INTEGER NOT NULL,
                PRIMARY KEY (villa_id, label_id),
                FOREIGN KEY (villa_id) REFERENCES villas(id) ON DELETE CASCADE,
                FOREIGN KEY (label_id) REFERENCES labels(id) ON DELETE CASCADE
            )"
        ],
        'villa_images' => [
            'description' => 'Images associated with villas',
            'sql' => "CREATE TABLE IF NOT EXISTS villa_images (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                villa_id INTEGER NOT NULL,
                image_path TEXT NOT NULL,
                FOREIGN KEY (villa_id) REFERENCES villas(id) ON DELETE CASCADE
            )"
        ],
        'tickets' => [
            'description' => 'Support/contact tickets from users',
            'sql' => "CREATE TABLE IF NOT EXISTS tickets (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                naam TEXT NOT NULL,
                email TEXT NOT NULL,
                bericht TEXT NOT NULL,
                status SMALLINT NOT NULL CHECK (status IN (0, 1, 2)),
                prioriteit SMALLINT NOT NULL CHECK (prioriteit IN (0, 1, 2)),
                datum TEXT NOT NULL
            )"
        ],
        'formulieren' => [
            'description' => 'Form submissions from website',
            'sql' => "CREATE TABLE IF NOT EXISTS formulieren (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                naam TEXT NOT NULL,
                email TEXT NOT NULL,
                telefoon TEXT NOT NULL,
                datum TEXT NOT NULL,
                bericht TEXT NOT NULL
            )"
        ],
        'ticket_formulieren' => [
            'description' => 'Junction table linking tickets to form submissions',
            'sql' => "CREATE TABLE IF NOT EXISTS ticket_formulieren (
                ticket_id INTEGER NOT NULL,
                formulier_id INTEGER NOT NULL,
                PRIMARY KEY (ticket_id, formulier_id),
                FOREIGN KEY (ticket_id) REFERENCES tickets(id) ON DELETE CASCADE,
                FOREIGN KEY (formulier_id) REFERENCES formulieren(id) ON DELETE CASCADE
            )"
        ],
        'analytics' => [
            'description' => 'Website analytics data',
            'sql' => "CREATE TABLE IF NOT EXISTS analytics (
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                datum TEXT NOT NULL,
                ip_adres TEXT NOT NULL,
                pagina TEXT NOT NULL,
                query TEXT NOT NULL
            )"
        ],
        'serverLogger' => [
            'description' => 'Server-side logging table',
            'sql' => "CREATE TABLE IF NOT EXISTS serverLogger(
                id INTEGER PRIMARY KEY AUTOINCREMENT,
                timestamp TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
                action TEXT NOT NULL,
                message TEXT NOT NULL
            )"
        ]
    ];
    
    // Create each table
    echo "Creating tables:\n";
    foreach ($tables as $name => $tableInfo) {
        echo "- $name: {$tableInfo['description']}\n";
        $pdo->exec($tableInfo['sql']);
    }
    
    echo "\nAll tables created successfully!\n";
    
    // Create uploads directory if it doesn't exist
    $uploadsDir = $_SERVER['DOCUMENT_ROOT'] . '/uploads/villas/';
    if (!file_exists($uploadsDir)) {
        echo "\nCreating uploads directory: $uploadsDir\n";
        if (mkdir($uploadsDir, 0777, true)) {
            echo "Uploads directory created successfully.\n";
        } else {
            echo "Failed to create uploads directory. Please create it manually.\n";
        }
    }
    
    echo "\nDatabase setup completed successfully!\n";
    
} catch (PDOException $e) {
    echo "ERROR: Database setup failed!\n";
    echo "Error message: " . $e->getMessage() . "\n"; 
    exit(1);
}
?>
