<?php
// Load environment variables
require_once 'vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Database connection test
try {
    // Get MySQL connection details from environment
    $host = $_ENV['servername'];
    $database = $_ENV['database'];
    $username = $_ENV['username'];
    $password = $_ENV['password'];
    $charset = $_ENV['charset'];
    
    // Create PDO connection to MySQL
    $dsn = "mysql:host=$host;dbname=$database;charset=$charset";
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Test the connection with a simple query
    $stmt = $pdo->query("SELECT version() as version");
    $mysqlVersion = $stmt->fetch()['version'];
    
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
    <title>Villa Verkenner - Setup</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            text-align: center;
        }
        .status {
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }
        .success {
            background-color: #dff0d8;
            color: #3c763d;
            border: 1px solid #d6e9c6;
        }
        .error {
            background-color: #f2dede;
            color: #a94442;
            border: 1px solid #ebccd1;
        }
    </style>
</head>
<body>
    <h1>Villa Verkenner - Setup</h1>
    
    <div class="status <?= $connected ? 'success' : 'error' ?>">
        <?php if ($connected): ?>
            MySQL Connection: ✅ Successfully Connected
            <p>MySQL Version: <?= htmlspecialchars($mysqlVersion) ?></p>
        <?php else: ?>
            MySQL Connection: ❌ Failed 
            <p>Error: <?= htmlspecialchars($error ?? 'Unknown error') ?></p>
        <?php endif; ?>
    </div>

    <div class="status">
        <h2>Environment Details</h2>
        <p>Database Host: <?= htmlspecialchars($host) ?></p>
        <p>Database Name: <?= htmlspecialchars($database) ?></p>
        <p>PHP Version: <?= phpversion() ?></p>
    </div>

    <p>If you see a green success message, your setup is working correctly!</p>
    <p>Access phpMyAdmin at: <a href="http://localhost:<?= $_ENV['PMA_PORT'] ?? '8889' ?>">http://localhost:<?= $_ENV['PMA_PORT'] ?? '8889' ?></a></p>
</body>
</html>