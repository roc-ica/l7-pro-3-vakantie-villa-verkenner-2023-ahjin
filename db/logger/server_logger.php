<?php
// Suppress warnings that might break JSON output
error_reporting(0);

include_once '../database.php';

class ServerLogger {
    public static function log($message)
    {
        try {
            $pdo = connect_db();
            $stmt = $pdo->prepare("INSERT INTO serverLogger (action) VALUES (:message)");
            $stmt->execute([':message' => $message]);
            $pdo = null;
        } catch (Exception $e) {
            // Silently fail logging - don't break the main application flow
        }
    }

    public static function getLogs()
    {
        try {
            $pdo = connect_db();
            $stmt = $pdo->prepare("SELECT * FROM serverLogger ORDER BY timestamp DESC");
            $stmt->execute();
            $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $pdo = null;
            return $logs;
        } catch (Exception $e) {
            return [];
        }
    }
};

?>