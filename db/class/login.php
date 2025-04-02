<?php

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/sessions.php';

class Login {
    private PDO $conn;
    private string $username;
    private string $password;

    public function __construct(PDO $conn, string $username, string $password) {
        $this->conn = $conn;
        $this->username = $username;
        $this->password = $password;
    }

    private function handleLogin($username, $password) {
        try {
            // Prepare statement to find admin by username
            $stmt = $this->conn->prepare("SELECT * FROM admin WHERE username = :username LIMIT 1");
            $stmt->execute(['username' => $username]);
            $admin = $stmt->fetch(PDO::FETCH_ASSOC);
            
            // Check if admin exists and password is correct
            if ($admin && password_verify($password, $admin['password'])) {
                // Create a session ID and update the admin record
                $session_id = session_create_id();
                
                $updateStmt = $this->conn->prepare("UPDATE admin SET session_id = :session_id WHERE id = :id");
                $updateStmt->execute([
                    ':session_id' => $session_id,
                    ':id' => $admin['id']
                ]);
                
                // Start session for the admin
                $session = new SessionManager($this->conn, $username, time());
                $session->SessionS();
                
                return [
                    "success" => true,
                    "message" => "Login successful"
                ];
            } else {
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

    private function isLoggedIn() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        return isset($_SESSION['username']);
    }

    public function login() {
        if (!$this->isLoggedIn()) {
            return $this->handleLogin($this->username, $this->password);
        } else {
            return [
                "success" => true,
                "message" => "User is already logged in"
            ];
        }
    }
    
}