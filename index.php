<?php
// Load environment variables
require_once 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Database connection test
try {
    $dbFile = $_ENV['DB_FILE'] ?? '/var/www/html/db/villaverkenner.sqlite';
    
    // Ensure the database directory exists
    $dbDir = dirname($dbFile);
    if (!is_dir($dbDir)) {
        mkdir($dbDir, 0755, true);
    }

    // Create PDO connection to SQLite
    $pdo = new PDO("sqlite:$dbFile");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Optional: Create a test table if it doesn't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS test_connection (
        id INTEGER PRIMARY KEY,
        message TEXT,
        created_at DATETIME DEFAULT CURRENT_TIMESTAMP
    )");
    
    $connected = true;
} catch(PDOException $e) {
    $connected = false;
    $error = $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IJsland</title>
</head>
<body>
    <h1>Villa Verkenner - Docker Setup</h1>
    
    <div class="status <?= $connected ? 'success' : 'error' ?>">
        <?php if ($connected): ?>
            SQLite Connection: ✅ Successfully Connected
        <?php else: ?>
            SQLite Connection: ❌ Failed 
            <p>Error: <?= htmlspecialchars($error ?? 'Unknown error') ?></p>
        <?php endif; ?>
    </div>

    <div class="status">
        <h2>Environment Details</h2>
        <p>Database File: <?= htmlspecialchars($dbFile) ?></p>
    </div>

    <p>If you see a green success message, your Docker setup is working correctly!</p>
</body>
</html>