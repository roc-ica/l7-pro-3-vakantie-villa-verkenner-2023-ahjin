<?php

include_once __DIR__ . '/../class/serverlogger.php';

class ApiHelper {
    private const EXPECTED_METHODS = ["GET", "POST", "PUT", "DELETE", "PATCH"];
    private const MAX_INPUT_LENGTH = 8192; // 8KB max input size for security

    private static function validateRequestMethod(): bool {
        $method = $_SERVER['REQUEST_METHOD'] ?? null;
        
        if (!in_array($method, self::EXPECTED_METHODS, true)) {
            ServerLogger::log(
                "Invalid method: {$method}", 
                "error", 
                "api", 
                "validateRequestMethod"
            );
            return false;
        }
        return true;
    }

    private static function getRequestData(): ?array {
        if (!self::validateRequestMethod()) {
            return null;
        }

        // Only attempt to read input for methods that typically have a body
        if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'PATCH', 'DELETE'], true)) {
            return [];
        }

        $input = file_get_contents('php://input');
        
        // Basic security check for input size
        if (strlen($input) > self::MAX_INPUT_LENGTH) {
            ServerLogger::log(
                "Input data exceeds maximum allowed size", 
                "error", 
                "api", 
                "getRequestData"
            );
            return null;
        }

        if (empty($input)) {
            return [];
        }

        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            ServerLogger::log(
                "JSON decode error: " . json_last_error_msg(), 
                "error", 
                "api", 
                "getRequestData"
            );
            return null;
        }

        return $data ?? [];
    }


    public function ProccessApi() {
        $data = $this->getRequestData();
        if ($data === null) {
            ServerLogger::logToDatabase("No data available", "minor", "backend", "api_Req");
        }
    }
}