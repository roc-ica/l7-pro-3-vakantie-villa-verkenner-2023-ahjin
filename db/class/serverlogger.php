<?php 

include_once(__DIR__ . '/database.php');

class ServerLogger {
    private $message;
    private $level;
    private $page;
    private $action;

    public function __construct($message, $level, $page, $action){
        $this->message = $message;
        $this->level = $level;
        $this->page = $page;
        $this->action = $action;
    }

    // Instance method for non-static use
    public function LogProb() {
        // Use the values from the constructor
        $this->logToDatabase($this->message, $this->level, $this->page, $this->action);
    }
    
    // Static method that can be called without creating an instance
    public static function Log($message, $level, $page, $action) {
        // Create a database instance since we can't use $this in static context
        $db = new Database();
        $conn = $db->getConnection();
        
        try {
            $stmt = $conn->prepare("INSERT INTO serverLogger (message, level, page, action, created_at) 
                                   VALUES (:message, :level, :page, :action, NOW())");
            
            $stmt->bindParam(':message', $message);
            $stmt->bindParam(':level', $level);
            $stmt->bindParam(':page', $page);
            $stmt->bindParam(':action', $action);
            
            $stmt->execute();
            
            // Close the connection
            $db->closeConnection($conn);
            
            return true;
        } catch (PDOException $e) {
            // Don't use self-logging here to avoid infinite recursion
            error_log("Logging error: " . $e->getMessage());
            
            // Close the connection even if there's an error
            $db->closeConnection($conn);
            
            return false;
        }
    }
    
    // Private helper method for the instance version
    private function logToDatabase($message, $level, $page, $action) {
        return self::Log($message, $level, $page, $action);
    }
}
?>