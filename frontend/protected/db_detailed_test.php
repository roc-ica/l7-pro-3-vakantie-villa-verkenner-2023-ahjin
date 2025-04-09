<?php
// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Include autoloader if needed
require_once __DIR__ . '/../../vendor/autoload.php';

echo "<h1>Detailed Database Connection Test</h1>";

// Try to load environment variables directly
if (file_exists(__DIR__ . '/../../.env')) {
    echo "<p>Found .env file at: " . __DIR__ . '/../../.env' . "</p>";
    
    // Try to load with Dotenv directly
    try {
        $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
        $dotenv->load();
        echo "<p style='color:green'>Successfully loaded .env file with Dotenv</p>";
    } catch (Exception $e) {
        echo "<p style='color:red'>Error loading .env with Dotenv: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p style='color:red'>ERROR: .env file not found at expected location!</p>";
}

// Display environment variables
echo "<h2>Environment Variables</h2>";
echo "<ul>";
echo "<li>servername: " . (isset($_ENV['servername']) ? htmlspecialchars($_ENV['servername']) : 'Not set') . "</li>";
echo "<li>username: " . (isset($_ENV['username']) ? htmlspecialchars($_ENV['username']) : 'Not set') . "</li>";
echo "<li>password: " . (isset($_ENV['password']) ? '[HIDDEN]' : 'Not set') . "</li>";
echo "<li>database: " . (isset($_ENV['database']) ? htmlspecialchars($_ENV['database']) : 'Not set') . "</li>";
echo "<li>charset: " . (isset($_ENV['charset']) ? htmlspecialchars($_ENV['charset']) : 'Not set') . "</li>";
echo "</ul>";

// Try direct PDO connection
echo "<h2>Testing Direct PDO Connection</h2>";

if (isset($_ENV['servername']) && isset($_ENV['username']) && isset($_ENV['password']) && isset($_ENV['database'])) {
    try {
        $dsn = "mysql:host={$_ENV['servername']};dbname={$_ENV['database']};charset=" . (isset($_ENV['charset']) ? $_ENV['charset'] : 'utf8mb4');
        echo "<p>DSN: " . str_replace($_ENV['password'], '[HIDDEN]', $dsn) . "</p>";
        
        echo "<p>Attempting direct PDO connection...</p>";
        $pdo = new PDO(
            $dsn,
            $_ENV['username'],
            $_ENV['password'],
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        
        echo "<p style='color:green'>SUCCESS: Direct PDO connection established!</p>";
        
        // Test a simple query
        $stmt = $pdo->query("SELECT VERSION() as version");
        $version = $stmt->fetch(PDO::FETCH_ASSOC);
        echo "<p>MySQL Version: " . htmlspecialchars($version['version']) . "</p>";
        
        // Check if database exists
        $stmt = $pdo->query("SHOW DATABASES LIKE '{$_ENV['database']}'");
        $dbExists = $stmt->rowCount() > 0;
        
        if ($dbExists) {
            echo "<p style='color:green'>Database '{$_ENV['database']}' exists.</p>";
            
            // Check tables
            $stmt = $pdo->query("SHOW TABLES");
            $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            echo "<p>Tables in database:</p>";
            echo "<ul>";
            foreach ($tables as $table) {
                echo "<li>" . htmlspecialchars($table) . "</li>";
            }
            echo "</ul>";
        } else {
            echo "<p style='color:red'>ERROR: Database '{$_ENV['database']}' does not exist!</p>";
        }
    } catch (PDOException $e) {
        echo "<p style='color:red'>PDO ERROR: " . htmlspecialchars($e->getMessage()) . "</p>";
        
        // Check common error codes
        if (strpos($e->getMessage(), "Access denied") !== false) {
            echo "<p>This suggests your username or password is incorrect.</p>";
        } elseif (strpos($e->getMessage(), "Unknown database") !== false) {
            echo "<p>This suggests the database '{$_ENV['database']}' does not exist.</p>";
        } elseif (strpos($e->getMessage(), "Connection refused") !== false) {
            echo "<p>This suggests the database server is not running or not accessible.</p>";
        }
    }
} else {
    echo "<p style='color:red'>ERROR: Missing required environment variables for database connection.</p>";
}

// Now try using the Database class
echo "<h2>Testing Database Class Connection</h2>";

// Include the database class
require_once __DIR__ . '/../../db/class/database.php';

if (class_exists('Database')) {
    echo "<p>Database class found. Examining class structure...</p>";
    
    // Get class methods
    $methods = get_class_methods('Database');
    echo "<p>Methods in Database class: " . implode(', ', $methods) . "</p>";
    
    try {
        echo "<p>Creating Database instance...</p>";
        $db = new Database();
        
        echo "<p>Calling getConnection()...</p>";
        $conn = $db->getConnection();
        
        if ($conn) {
            echo "<p style='color:green'>SUCCESS: Database connection established via Database class!</p>";
        } else {
            echo "<p style='color:red'>ERROR: Database class getConnection() returned null.</p>";
            echo "<p>This suggests there's an issue in the connect() method of the Database class.</p>";
        }
    } catch (Exception $e) {
        echo "<p style='color:red'>EXCEPTION in Database class: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p style='color:red'>ERROR: Database class not found after include!</p>";
}

// Check PDO drivers
echo "<h2>PDO Drivers</h2>";
echo "<p>Available PDO drivers: " . implode(', ', PDO::getAvailableDrivers()) . "</p>";
if (!in_array('mysql', PDO::getAvailableDrivers())) {
    echo "<p style='color:red'>ERROR: MySQL PDO driver is not available!</p>";
}

// Check PHP version
echo "<h2>PHP Environment</h2>";
echo "<p>PHP Version: " . phpversion() . "</p>";
echo "<p>Server Software: " . (isset($_SERVER['SERVER_SOFTWARE']) ? htmlspecialchars($_SERVER['SERVER_SOFTWARE']) : 'Unknown') . "</p>";
?>