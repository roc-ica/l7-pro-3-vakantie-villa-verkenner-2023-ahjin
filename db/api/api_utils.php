<?php
/**
 * API Utilities
 * 
 * Shared functions and constants for API endpoints
 */

// Prevent direct access to this file
if (!defined('API_ACCESS')) {
    http_response_code(403);
    echo json_encode(['error' => 'Direct access to this file is not allowed']);
    exit;
}

/**
 * Send a standardized JSON response
 * 
 * @param array $data Response data
 * @param int $statusCode HTTP status code
 */
function sendJsonResponse(array $data, int $statusCode = 200): void {
    http_response_code($statusCode);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * Send a success response
 * 
 * @param string $message Success message
 * @param array $data Additional data to include
 * @param int $statusCode HTTP status code (default 200)
 */
function sendSuccessResponse(string $message, array $data = [], int $statusCode = 200): void {
    $response = [
        'success' => true,
        'message' => $message
    ];
    
    if (!empty($data)) {
        $response = array_merge($response, $data);
    }
    
    sendJsonResponse($response, $statusCode);
}

/**
 * Send an error response
 * 
 * @param string $message Error message
 * @param string $errorType Error type identifier
 * @param array $data Additional data to include
 * @param int $statusCode HTTP status code (default 400)
 */
function sendErrorResponse(string $message, string $errorType = 'general_error', array $data = [], int $statusCode = 400): void {
    $response = [
        'success' => false,
        'error' => $errorType,
        'message' => $message
    ];
    
    if (!empty($data)) {
        $response = array_merge($response, $data);
    }
    
    sendJsonResponse($response, $statusCode);
}

/**
 * Add standard CORS headers to allow API access
 */
function addCorsHeaders(): void {
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: Content-Type');   
}

/**
 * Handle preflight OPTIONS requests for CORS
 */
function handleOptionsRequest(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
        http_response_code(200);
        exit;
    }
}

/**
 * Get request data regardless of content type
 * 
 * @return array Request data
 */
function getRequestData(): array {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    $isMultipart = strpos($contentType, 'multipart/form-data') !== false;
    $isJson = strpos($contentType, 'application/json') !== false;
    
    // Get data based on content type
    if ($isMultipart) {
        $data = $_POST;
    } elseif ($isJson) {
        $data = json_decode(file_get_contents('php://input'), true);
    } else {
        // Try POST data first
        $data = $_POST;
        
        // If empty, try JSON input
        if (empty($data)) {
            $data = json_decode(file_get_contents('php://input'), true);
        }
    }
    
    return $data ?: [];
}

/**
 * Validate required fields in the request data
 * 
 * @param array $data Request data
 * @param array $requiredFields List of required field names
 * @return array Missing fields (empty if all required fields are present)
 */
function validateRequiredFields(array $data, array $requiredFields): array {
    $missingFields = [];
    
    foreach ($requiredFields as $field) {
        if (!isset($data[$field]) || (is_string($data[$field]) && trim($data[$field]) === '')) {
            $missingFields[] = $field;
        }
    }
    
    return $missingFields;
}

/**
 * Log an API error
 * 
 * @param string $endpoint The API endpoint
 * @param string $message Error message
 */
function logApiError(string $endpoint, string $message): void {
    error_log("API Error [$endpoint]: $message");
} 