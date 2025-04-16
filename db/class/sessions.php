<?php
require_once __DIR__ . '/logger.php';
require_once __DIR__ . '/database.php';

class SessionManager {
    // Constants for session configuration
    private const SESSION_NAME = 'villa_admin_session'; // More specific name
    private const SESSION_LIFETIME = 3600; // 1 hour validity
    private const SESSION_REGENERATE_TIME = 300; // Regenerate ID every 5 minutes

    /**
     * Configures and starts a secure session if not already started.
     */
    private static function ensureSessionStarted(): bool {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return true; // Already started
        }
        
        if (session_status() === PHP_SESSION_NONE) {
            try {
                // Secure session settings
                ini_set('session.use_strict_mode', 1);
                ini_set('session.use_only_cookies', 1);
                ini_set('session.cookie_httponly', 1);
                ini_set('session.cookie_samesite', 'Lax'); // Or 'Strict' if appropriate

                // Set secure flag if HTTPS is used
                if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
                    ini_set('session.cookie_secure', 1);
                }

                session_name(self::SESSION_NAME);
                session_set_cookie_params([
                    'lifetime' => 0, // Cookie lives until browser close, session lifetime handled server-side
                    'path' => '/', // Available across the entire domain
                    'domain' => $_SERVER['HTTP_HOST'], // Use current host
                    'secure' => isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on',
                    'httponly' => true,
                    'samesite' => 'Lax' 
                ]);
                
                if (session_start()) {
                    error_log("Session started successfully: " . self::SESSION_NAME);
                    return true;
                } else {
                     error_log("session_start() failed.");
                     return false;
                }
            } catch (Exception $e) {
                 error_log("Error configuring/starting session: " . $e->getMessage());
                 return false;
            }
        } else {
            // Session is disabled or other issue
             error_log("Session is disabled or could not be started.");
             return false;
        }
    }

    /**
     * Start the admin session after successful login.
     */
    public static function startAdminSession(int $adminId, string $username): bool {
        if (!self::ensureSessionStarted()) {
             error_log("Admin session start failed: Could not ensure session started.");
            return false;
        }
        
        try {
            // Regenerate ID to prevent session fixation
            session_regenerate_id(true);
            error_log("Admin session ID regenerated for user: " . $username);

            // Clear any previous session data (start fresh)
            $_SESSION = []; 

            // Store essential admin information
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $adminId;
            $_SESSION['admin_username'] = $username;
            $_SESSION['last_activity'] = time();
            $_SESSION['session_start_time'] = time(); // For ID regeneration tracking

            error_log("Admin session data stored for user: {$username} (ID: {$adminId})");
            return true;
        } catch (Exception $e) {
             error_log("Error in startAdminSession: " . $e->getMessage());
            return false;
        }
    }
    
    /**
     * Validates the current admin session.
     * Checks for login status, activity timeout, and regenerates ID periodically.
     */
    public static function validateAdminSession(): bool {
        if (!self::ensureSessionStarted()) {
            return false; // Cannot validate if session won't start
        }
        
        // Check if essential session variables are set
        if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true || !isset($_SESSION['admin_id']) || !isset($_SESSION['last_activity'])) {
            error_log("Admin session validation failed: Required data missing or not logged in.");
            return false;
        }
        
        // Check for session timeout based on last activity
        $inactiveTime = time() - $_SESSION['last_activity'];
        if ($inactiveTime > self::SESSION_LIFETIME) {
            error_log("Admin session expired due to inactivity ({$inactiveTime}s) for user: {$_SESSION['admin_username']}");
            self::destroySession();
            return false;
        }

         // Regenerate session ID periodically for enhanced security
         if (!isset($_SESSION['session_start_time']) || (time() - $_SESSION['session_start_time'] > self::SESSION_REGENERATE_TIME)) {
             if (session_regenerate_id(true)) {
                 $_SESSION['session_start_time'] = time(); // Reset timer after regeneration
                 error_log("Admin session ID regenerated periodically for user: {$_SESSION['admin_username']}");
             } else {
                  error_log("Periodic session ID regeneration failed for user: {$_SESSION['admin_username']}");
                 // Optionally destroy session if regeneration fails, or just log it
             }
         }
        
        // Update last activity time on successful validation
        $_SESSION['last_activity'] = time();
        error_log("Admin session validated for user: {$_SESSION['admin_username']}");
        return true;
    }
    
    /**
     * Destroy the current admin session and clear associated data.
     */
    public static function destroySession() {
        if (!self::ensureSessionStarted()) {
            return; // No session to destroy
        }

        try {
            error_log("Attempting to destroy admin session for user: " . ($_SESSION['admin_username'] ?? 'unknown'));
            
            // Clear session data from the superglobal
            $_SESSION = [];
            
            // Delete the session cookie
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(
                    session_name(),
                    '',
                    time() - 42000, // Set expiry in the past
                    $params["path"],
                    $params["domain"],
                    $params["secure"],
                    $params["httponly"]
                );
                error_log("Session cookie deleted.");
            }
            
            // Destroy the session on the server
            if (session_destroy()) {
                 error_log("Admin session destroyed successfully.");
            } else {
                 error_log("session_destroy() failed.");
            }

        } catch (Exception $e) {
            error_log("Error during admin session destruction: " . $e->getMessage());
        }
    }
}