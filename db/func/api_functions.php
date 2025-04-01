<?php


class ApiHelper {
        private const EXPECTED_METHODS = ["GET", "POST", "PUT", "DELETE"];
    private const MAX_INPUT_LENGTH = 8192; // 8KB max input size for security

    private static function validateRequestMethod(): bool {
        $method = $_SERVER['REQUEST_METHOD'] ?? null;
        
        if (!in_array($method, self::EXPECTED_METHODS, true)) {
            return false;
        }
        return true;
    }

    private static function getRequestData(): ?array {
        if (!self::validateRequestMethod()) {
            return null;
        }

        // Only attempt to read input for methods that typically have a body
        if (!in_array($_SERVER['REQUEST_METHOD'], ['POST', 'PUT', 'DELETE'], true)) {
            return [];
        }

        $input = file_get_contents('php://input');
        
        // Basic security check for input size
        if (strlen($input) > self::MAX_INPUT_LENGTH) {
            return null;
        }

        if (empty($input)) {
            return [];
        }

        $data = json_decode($input, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return null;
        }

        return $data ?? [];
    }


    public function ProccessApi() {
        $data = self::getRequestData();
        if ($data === null) {
            throw new Exception("Invalid request method or data format");
        }
        
        // Return empty data for now, will be expanded later
        return [
            'status' => 'success',
            'message' => 'API request processed successfully',
            'data' => $data
        ];
    }
}