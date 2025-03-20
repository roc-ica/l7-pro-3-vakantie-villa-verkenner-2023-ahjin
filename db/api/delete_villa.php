<?php
/**
 * API Endpoint: Delete Villa
 * Deletes a villa and all related data (images, labels)
 */

// Error based on environment
$isProduction = !isset($_SERVER['ENVIRONMENT']) || $_SERVER['ENVIRONMENT'] !== 'development';
if ($isProduction) {
    error_reporting(0); // Suppress all errors in production
} else {
    error_reporting(E_ALL); // Show all errors in development
}

// Define API_ACCESS for api_utils.php
define('API_ACCESS', true);

// Include dependencies
require_once '../database.php';
require_once 'api_utils.php';

// Handle CORS
addCorsHeaders();
handleOptionsRequest();

// Check for valid request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    sendErrorResponse(
        'Method not allowed. Please use POST or GET.', 
        'method_not_allowed',
        [],
        405
    );
}

// Check for villa ID parameter
$villaId = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0);

if ($villaId <= 0) {
    sendErrorResponse(
        'Missing or invalid villa ID',
        'invalid_input'
    );
}

try {
    // Start transaction
    Database::beginTransaction();
    
    // Check if villa exists
    $villa = VillaManager::getVilla($villaId);
    if (!$villa) {
        sendErrorResponse(
            "Villa with ID $villaId not found",
            'not_found',
            [],
            404
        );
    }
    
    // First, get images associated with this villa to delete files later
    $images = ImageManager::getVillaImages($villaId);
    
    // Remove all labels from this villa
    LabelManager::removeAllVillaLabels($villaId);
    
    // Delete all images from database
    foreach ($images as $image) {
        ImageManager::deleteImage($image['id']);
    }
    
    // Delete the villa itself
    if (!VillaManager::deleteVilla($villaId)) {
        throw new Exception("Failed to delete villa ID: $villaId");
    }
    
    // Commit transaction
    Database::commit();
    
    // Delete physical image files (after transaction is committed)
    foreach ($images as $image) {
        $imagePath = $_SERVER['DOCUMENT_ROOT'] . $image['image_path'];
        if (file_exists($imagePath)) {
            @unlink($imagePath);
        }
    }
    
    // Return success response
    sendSuccessResponse(
        'Villa successfully deleted',
        ['villa_id' => $villaId]
    );
    
} catch (PDOException $e) {
    // Rollback transaction on database error
    Database::rollback();
    
    // Log the error
    logApiError('delete_villa', $e->getMessage());
    
    // Return error response
    sendErrorResponse(
        $isProduction ? 'A database error occurred' : $e->getMessage(),
        'database_error', 
        [],
        500
    );
} catch (Exception $e) {
    // Rollback transaction on any other error
    Database::rollback();
    
    // Log the error
    logApiError('delete_villa', $e->getMessage());
    
    // Return error response
    sendErrorResponse(
        $isProduction ? 'A server error occurred' : $e->getMessage(),
        'server_error',
        [],
        500
    );
}
?> 