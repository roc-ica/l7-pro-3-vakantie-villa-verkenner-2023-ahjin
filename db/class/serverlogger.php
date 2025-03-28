<?php 

include_once(__DIR__ . 'database.php');

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

    public function LogProb($message, $level, $page, $action) {
        $log = new ServerLogger($message, $level, $page, $action);

    }
}
?>