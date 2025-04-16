<?php

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/sessions.php';

class Login {
    private PDO $conn;
    private string $username;
    private string $password;

    public function __construct(PDO $conn, string $username, string $password) {
        $this->conn = $conn;
        $this->username = htmlspecialchars(strip_tags($username));
        $this->password = $password;
    }

    private function handleLogin(string $username, string $password): array {
        try {
            // Prepare statement to find admin by username
            $stmt = $this->conn->prepare("SELECT id, username, password FROM admin WHERE username = :username LIMIT 1");
            $stmt->execute([':username' => $username]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Check if admin exists and password is correct
            if ($admin && password_verify($password, $admin['password'])) {
                
                // Start session and store admin info using SessionManager
                $sessionManager = new SessionManager();
                $sessionStarted = $sessionManager->startAdminSession($admin['id'], $admin['username']); 
                
                if ($sessionStarted) {
                    // Log the login activity (optional, can be in SessionManager too)
                     try {
                         $logStmt = $this->conn->prepare("INSERT INTO login_activity (username, login_time) VALUES (:username, NOW())");
                         $logStmt->execute([':username' => $admin['username']]);
                     } catch (PDOException $logError) {
                         error_log("Login activity logging failed: " . $logError->getMessage());
                         // Don't stop login if logging fails
                     }
                    
                    return [
                        "success" => true,
                        "message" => "Login successful"
                    ];
                } else {
                     error_log("Failed to start admin session for user: " . $username);
                    return [
                        "success" => false,
                        "message" => "Session could not be started."
                    ];
                }

            } else {
                // Invalid username or password
                return [
                    "success" => false,
                    "message" => "Invalid username or password"
                ];
            }
        } catch (PDOException $e) {
            error_log("Login error: " . $e->getMessage());
            return [
                "success" => false,
                "message" => "Database error during login"
            ];
        }
    }

    public static function isAdminLoggedIn(): bool {
        return SessionManager::validateAdminSession();
    }

    public function login(): array {
        if (self::isAdminLoggedIn()) {
             return [
                "success" => true,
                "message" => "User is already logged in"
            ];
        }
        return $this->handleLogin($this->username, $this->password);
    }
    
}