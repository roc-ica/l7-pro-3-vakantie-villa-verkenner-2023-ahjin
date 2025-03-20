<?php
/**
 * API Endpoint: Update Villa
 * Updates villa information in the database
 */

// Set error reporting based on environment
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
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    sendErrorResponse(
        'Method not allowed. Please use POST.',
        'method_not_allowed',
        [],
        405
    );
}

// Get request data
$data = getRequestData();

// Validate required fields
$requiredFields = ['id', 'straat', 'post_c', 'kamers', 'badkamers', 'slaapkamers', 'oppervlakte', 'prijs'];
$missingFields = validateRequiredFields($data, $requiredFields);

if (!empty($missingFields)) {
    sendErrorResponse(
        'Missing required fields',
        'missing_fields',
        ['missing_fields' => $missingFields]
    );
}

try {
    // Start transaction
    Database::beginTransaction();
    
    // Convert and sanitize inputs
    $villaId = (int)$data['id'];
    $straat = trim($data['straat']);
    $postCode = trim($data['post_c']);
    $kamers = (int)$data['kamers'];
    $badkamers = (int)$data['badkamers'];
    $slaapkamers = (int)$data['slaapkamers'];
    $oppervlakte = (float)$data['oppervlakte'];
    $prijs = (int)$data['prijs'];
    
    // Validate villa exists
    $existingVilla = VillaManager::getVilla($villaId);
    if (!$existingVilla) {
        sendErrorResponse(
            "Villa with ID $villaId not found",
            'not_found',
            [],
            404
        );
    }
    
    // Update the villa
    if (!VillaManager::updateVilla(
        $villaId,
        $straat,
        $postCode,
        $kamers,
        $badkamers,
        $slaapkamers,
        $oppervlakte,
        $prijs
    )) {
        throw new Exception("Failed to update villa ID: $villaId");
    }
    
    // Process labels if provided
    if (isset($data['labels'])) {
        // Remove all existing labels first
        LabelManager::removeAllVillaLabels($villaId);
        
        // Parse labels (handle both array and comma-separated string formats)
        $labels = is_array($data['labels']) ? $data['labels'] : explode(',', $data['labels']);
        
        // Add each label
        foreach ($labels as $labelName) {
            $labelName = trim($labelName);
            if (!empty($labelName)) {
                $labelId = LabelManager::getOrCreateLabel($labelName);
                if ($labelId) {
                    LabelManager::assignLabelToVilla($villaId, $labelId);
                }
            }
        }
    }
    
    // Commit transaction
    Database::commit();
    
    // Return success response
    sendSuccessResponse(
        'Villa successfully updated',
        ['villa_id' => $villaId]
    );
    
} catch (PDOException $e) {
    // Rollback transaction on database error
    Database::rollback();
    
    // Log the error
    logApiError('update_villa', $e->getMessage());
    
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
    logApiError('update_villa', $e->getMessage());
    
    // Return error response
    sendErrorResponse(
        $isProduction ? 'A server error occurred' : $e->getMessage(),
        'server_error',
        [],
        500
    );
}
?> 