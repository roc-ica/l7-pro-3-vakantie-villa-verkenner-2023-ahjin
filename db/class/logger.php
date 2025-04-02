<?php

class Logger {
    private function log($username, $conn, $login_time) {
        try {
            $stmt = $conn->prepare("INSERT INTO login_activity (username, login_time) VALUES (:username, :login_time)");
            $stmt->execute(['username' => $username, 'login_time' => $login_time]);
            error_log("Logged login activity for user: $username at time: $login_time");
            return true;
        } catch (PDOException $e) {
            error_log("Error logging login activity: " . $e->getMessage());
            // Continue execution even if logging fails
            return false;
        }
    }
    
    public function logLogin($username, $conn, $login_time) {
        try {
            return $this->log($username, $conn, $login_time);
        } catch (Exception $e) {
            error_log("Exception in logLogin: " . $e->getMessage());
            return false;
        }
    }
}
