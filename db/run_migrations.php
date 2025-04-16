<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
require_once __DIR__ . "/class/database.php";

/**
 * Function to run a migration file
 * @param PDO $conn Database connection
 * @param string $filepath Path to migration file
 * @return bool Success status
 */
function runMigration($conn, $filepath) {
    echo "Running migration: " . basename($filepath) . "... ";
    
    try {
        // Read migration file
        $sql = file_get_contents($filepath);
        
        // Fix date placeholder if exists
        $sql = str_replace('<?php echo date(\'Y-m-d H:i:s\'); ?>', date('Y-m-d H:i:s'), $sql);
        
        // Split SQL by semicolons to execute each statement
        $statements = array_filter(
            array_map('trim', 
                explode(';', $sql)
            ),
            function($statement) {
                return !empty($statement) && 
                      !preg_match('/^\s*--/', $statement) && 
                      !preg_match('/^\s*$/', $statement);
            }
        );
        
        // Begin transaction
        $conn->beginTransaction();
        
        // Execute each statement
        foreach ($statements as $statement) {
            $conn->exec($statement);
        }
        
        // Commit transaction
        $conn->commit();
        
        echo "SUCCESS\n";
        return true;
    } catch (PDOException $e) {
        // Rollback transaction on error
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        
        echo "FAILED: " . $e->getMessage() . "\n";
        return false;
    }
}

// Main execution
try {
    // Create database instance
    $db = new Database();
    $conn = $db->getConnection();
    
    // Directory containing migration files
    $migrationsDir = __DIR__ . '/migrations';
    
    // Check if migrations table exists
    $tableExists = false;
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('migrations', $tables)) {
        $tableExists = true;
    }
    
    // Create migrations table if it doesn't exist
    if (!$tableExists) {
        echo "Creating migrations table... ";
        $conn->exec("
            CREATE TABLE IF NOT EXISTS `migrations` (
                `id` INT NOT NULL AUTO_INCREMENT,
                `migration_name` VARCHAR(255) NOT NULL,
                `applied_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE INDEX `migration_UNIQUE` (`migration_name` ASC)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
        ");
        echo "SUCCESS\n";
    }
    
    // Get list of migration files
    $migrationFiles = glob($migrationsDir . '/*.sql');
    sort($migrationFiles); // Sort by filename
    
    // Get already executed migrations
    $stmt = $conn->prepare("SELECT migration_name FROM migrations");
    $stmt->execute();
    $executedMigrations = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    // Counter for executed migrations
    $executedCount = 0;
    
    // Run each migration file
    foreach ($migrationFiles as $file) {
        $migrationName = basename($file);
        
        // Skip if already executed
        if (in_array($migrationName, $executedMigrations)) {
            echo "Skipping " . $migrationName . " (already executed)\n";
            continue;
        }
        
        // Run migration
        if (runMigration($conn, $file)) {
            // Record successful migration
            $stmt = $conn->prepare("INSERT INTO migrations (migration_name) VALUES (:migration_name)");
            $stmt->execute([':migration_name' => $migrationName]);
            $executedCount++;
        }
    }
    
    // Summary
    echo "\n--- Migration Summary ---\n";
    echo "Total migrations: " . count($migrationFiles) . "\n";
    echo "Already executed: " . count($executedMigrations) . "\n";
    echo "Newly executed: " . $executedCount . "\n";
    echo "------------------------\n";
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Migration process completed.\n";
?> 