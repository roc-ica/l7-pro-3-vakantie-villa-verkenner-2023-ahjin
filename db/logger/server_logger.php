<?php
/**
 * ServerLogger Class
 * Handles server-side logging functionality
 */

// Set error reporting based on environment
$isProduction = !isset($_SERVER['ENVIRONMENT']) || $_SERVER['ENVIRONMENT'] !== 'development';
if ($isProduction) {
    error_reporting(0); // Suppress all errors in production
} else {
    error_reporting(E_ALL); // Show all errors in development
}

require_once __DIR__ . '/../database.php';

class ServerLogger {
    // Log table name
    private static string $table = 'serverLogger';
    
    // Maximum number of logs to retain
    private static int $maxLogs = 1000;
    
    /**
     * Log a message to the database
     *
     * @param string $message Message to log
     * @return bool Success status
     */
    public static function log(string $message): bool
    {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("INSERT INTO " . self::$table . " (action, message) VALUES (:action, :message)");
            $stmt->execute([
                ':action' => 'system',
                ':message' => $message
            ]);
            
            // Periodically clean up old logs (1% chance to trigger cleanup)
            if (mt_rand(1, 100) === 1) {
                self::cleanupOldLogs();
            }
            
            return true;
        } catch (Exception $e) {
            // Log to error_log as a fallback
            error_log("ServerLogger error: " . $e->getMessage());
            return false;
        } finally {
            Database::close();
        }
    }
    
    /**
     * Get all logs from the database
     *
     * @param int $limit Maximum number of logs to retrieve
     * @return array Logs as an array
     */
    public static function getLogs(int $limit = 100): array
    {
        try {
            $db = Database::getConnection();
            
            $sql = "SELECT * FROM " . self::$table . " ORDER BY timestamp DESC LIMIT :limit";
            
            $stmt = $db->prepare($sql);
            $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
            $stmt->execute();
            
            return $stmt->fetchAll();
        } catch (Exception $e) {
            error_log("Error retrieving logs: " . $e->getMessage());
            return [];
        } finally {
            Database::close();
        }
    }
    
    /**
     * Delete logs older than a specified time period
     *
     * @param int $days Number of days to keep logs for
     * @return bool Success status
     */
    public static function deleteOldLogs(int $days = 30): bool
    {
        try {
            $db = Database::getConnection();
            
            // SQLite date calculation for logs older than $days
            $stmt = $db->prepare("
                DELETE FROM " . self::$table . "
                WHERE datetime(timestamp) < datetime('now', :days)
            ");
            
            $stmt->execute([':days' => "-$days days"]);
            return true;
        } catch (Exception $e) {
            error_log("Error deleting old logs: " . $e->getMessage());
            return false;
        } finally {
            Database::close();
        }
    }
    
    /**
     * Remove excess logs to maintain reasonable table size
     *
     * @return bool Success status
     */
    private static function cleanupOldLogs(): bool
    {
        try {
            $db = Database::getConnection();
            
            // Get total log count
            $count = $db->query("SELECT COUNT(*) FROM " . self::$table)->fetchColumn();
            
            // If we have more logs than our max, delete oldest ones
            if ($count > self::$maxLogs) {
                $toDelete = $count - self::$maxLogs;
                
                $stmt = $db->prepare("
                    DELETE FROM " . self::$table . "
                    WHERE id IN (
                        SELECT id FROM " . self::$table . "
                        ORDER BY timestamp ASC
                        LIMIT :limit
                    )
                ");
                
                $stmt->bindValue(':limit', $toDelete, PDO::PARAM_INT);
                $stmt->execute();
            }
            
            return true;
        } catch (Exception $e) {
            error_log("Error cleaning up logs: " . $e->getMessage());
            return false;
        } finally {
            Database::close();
        }
    }
}

?>