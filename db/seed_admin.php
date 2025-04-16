<?php
// Enable error reporting
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Include database connection
require_once __DIR__ . "/class/database.php";

// Function to check if username already exists
function usernameExists($conn, $username) {
    $stmt = $conn->prepare("SELECT COUNT(*) FROM admins WHERE username = :username");
    $stmt->execute([':username' => $username]);
    return (int) $stmt->fetchColumn() > 0;
}

// Main execution
try {
    // Create database instance
    $db = new Database();
    $conn = $db->getConnection();
    
    // Default admin credentials
    $admin = [
        'username' => 'admin',
        'password' => password_hash('admin123', PASSWORD_BCRYPT),
        'email' => 'admin@villaverkenner.nl',
        'first_name' => 'Admin',
        'last_name' => 'User',
        'role' => 'admin'
    ];
    
    // Check if admins table exists
    $tableExists = false;
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    if (in_array('admins', $tables)) {
        $tableExists = true;
    }
    
    // Create admin table if it doesn't exist
    if (!$tableExists) {
        echo "Creating admins table... ";
        
        $conn->exec("
            CREATE TABLE IF NOT EXISTS `admins` (
              `id` INT NOT NULL AUTO_INCREMENT,
              `username` VARCHAR(50) NOT NULL,
              `password` VARCHAR(255) NOT NULL,
              `email` VARCHAR(100) NOT NULL,
              `first_name` VARCHAR(50) NOT NULL,
              `last_name` VARCHAR(50) NOT NULL,
              `role` ENUM('admin', 'editor', 'viewer') NOT NULL DEFAULT 'viewer',
              `is_active` TINYINT(1) NOT NULL DEFAULT 1,
              `last_login` DATETIME NULL,
              `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
              `updated_at` DATETIME NULL ON UPDATE CURRENT_TIMESTAMP,
              PRIMARY KEY (`id`),
              UNIQUE INDEX `username_UNIQUE` (`username` ASC),
              UNIQUE INDEX `email_UNIQUE` (`email` ASC)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ");
        
        echo "SUCCESS\n";
    }
    
    // Check if admin user already exists
    if (usernameExists($conn, $admin['username'])) {
        echo "Admin user already exists.\n";
    } else {
        // Insert admin user
        echo "Creating admin user... ";
        
        $stmt = $conn->prepare("
            INSERT INTO admins (username, password, email, first_name, last_name, role)
            VALUES (:username, :password, :email, :first_name, :last_name, :role)
        ");
        
        $stmt->execute([
            ':username' => $admin['username'],
            ':password' => $admin['password'],
            ':email' => $admin['email'],
            ':first_name' => $admin['first_name'],
            ':last_name' => $admin['last_name'],
            ':role' => $admin['role']
        ]);
        
        echo "SUCCESS\n";
        echo "Created admin user with credentials:\n";
        echo "Username: " . $admin['username'] . "\n";
        echo "Password: admin123\n";
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}

echo "Admin seeding completed.\n";
?> 