<?php
require_once __DIR__ . '/logger.php';
require_once __DIR__ . '/database.php';

class SessionManager {
    private PDO $conn;
    private string $username;
    private string $login_time;
    private Logger $logger;

    private const SESSION_NAME = 'villa_verkenner_session';
    private const SESSION_LIFETIME = 3600; // 1 hour

    public function __construct(PDO $conn, string $username, string $login_time) {
        $this->conn = $conn;
        $this->username = $username;
        $this->login_time = $login_time;
        $this->logger = new Logger();
    }

    /**
     * Start the user session and store necessary information
     */
    private function startSession() {
        // Log session start attempt
        error_log("Starting session for user: {$this->username}");
        
        try {
            if (session_status() === PHP_SESSION_NONE) {
                // Configure secure session settings
                ini_set('session.use_strict_mode', 1);
                ini_set('session.use_only_cookies', 1);
                ini_set('session.cookie_httponly', 1);
                
                if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                    ini_set('session.cookie_secure', 1);
                }
                
                session_name(self::SESSION_NAME);
                session_set_cookie_params(self::SESSION_LIFETIME);
                session_start();
                error_log("Session started successfully with name: " . self::SESSION_NAME);
            } else {
                error_log("Session already active");
            }

            // Regenerate session ID to prevent session fixation
            session_regenerate_id(true);
            error_log("Session ID regenerated");

            // Store user information in session
            $_SESSION['username'] = $this->username;
            $_SESSION['login_time'] = $this->login_time;
            $_SESSION['last_activity'] = time();
            error_log("Session data stored for user: {$this->username}");

            // Log the login activity
            $this->logger->logLogin($this->username, $this->conn, $this->login_time);
            error_log("Login activity logged successfully");
        } catch (Exception $e) {
            error_log("Error in startSession: " . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Start a new user session
     */
    public function SessionS() {
        try {
            error_log("SessionS method called for user: {$this->username}");
            $this->startSession();
            return true;
        } catch (Exception $e) {
            error_log("Error in SessionS: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Check if a session is valid and not expired
     * 
     * @return bool True if the session is valid
     */
    public static function validateSession() {
        try {
            error_log("validateSession called");
            if (session_status() === PHP_SESSION_NONE) {
                session_name(self::SESSION_NAME);
                session_start();
                error_log("Session started in validateSession");
            }
            
            // Check if required session data exists
            if (!isset($_SESSION['username']) || !isset($_SESSION['last_activity'])) {
                error_log("Session validation failed: required data missing");
                return false;
            }
            
            // Check for session timeout
            $inactiveTime = time() - $_SESSION['last_activity'];
            if ($inactiveTime > self::SESSION_LIFETIME) {
                // Session expired, destroy it
                error_log("Session expired after {$inactiveTime} seconds of inactivity");
                self::destroySession();
                return false;
            }
            
            // Update last activity time
            $_SESSION['last_activity'] = time();
            error_log("Session validated for user: {$_SESSION['username']}");
            return true;
        } catch (Exception $e) {
            error_log("Error in validateSession: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Destroy the current session
     */
    public static function destroySession() {
        try {
            error_log("destroySession called");
            if (session_status() === PHP_SESSION_ACTIVE) {
                // Clear session data
                $_SESSION = [];
                
                // Delete the session cookie
                if (ini_get("session.use_cookies")) {
                    $params = session_get_cookie_params();
                    setcookie(
                        session_name(),
                        '',
                        time() - 42000,
                        $params["path"],
                        $params["domain"],
                        $params["secure"],
                        $params["httponly"]
                    );
                }
                
                // Destroy the session
                session_destroy();
                error_log("Session destroyed successfully");
            } else {
                error_log("No active session to destroy");
            }
        } catch (Exception $e) {
            error_log("Error in destroySession: " . $e->getMessage());
        }
    }
}