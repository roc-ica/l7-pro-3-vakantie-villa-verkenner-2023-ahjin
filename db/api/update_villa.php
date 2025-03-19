<?php
// Suppress warnings and notices that could break JSON output
error_reporting(0);
header('Content-Type: application/json');

include_once '../database.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Method not allowed. Please use POST.']);
    exit;
}

// Check if we're getting form data or JSON
$isMultipart = isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'multipart/form-data') !== false;

if ($isMultipart) {
    // Handle form data
    $data = $_POST;
} else {
    // Handle JSON data
    $data = json_decode(file_get_contents('php://input'), true);
    
    // If JSON parse failed, check for regular POST data
    if (empty($data) && !empty($_POST)) {
        $data = $_POST;
    }
}

// Validate required fields
$requiredFields = ['id', 'straat', 'post_c', 'kamers', 'badkamers', 'slaapkamers', 'oppervlakte', 'prijs'];
$missingFields = [];

foreach ($requiredFields as $field) {
    if (empty($data[$field])) {
        $missingFields[] = $field;
    }
}

if (!empty($missingFields)) {
    http_response_code(400); // Bad Request 
    echo json_encode([
        'error' => 'Missing required fields',
        'missing_fields' => $missingFields
    ]);
    exit;
}

try {
    // Start transaction
    $pdo = connect_db();
    $pdo->beginTransaction();
    
    // Update the villa
    update_villa(
        (int)$data['id'],
        $data['straat'],
        $data['post_c'],
        (int)$data['kamers'],
        (int)$data['badkamers'],
        (int)$data['slaapkamers'],
        (float)$data['oppervlakte'],
        (int)$data['prijs']
    );
    
    // Process labels if provided
    if (!empty($data['labels'])) {
        // First remove all existing labels
        $stmt = $pdo->prepare("DELETE FROM villa_labels WHERE villa_id = :villa_id");
        $stmt->execute([':villa_id' => (int)$data['id']]);
        
        // Then add the new ones
        $labels = is_array($data['labels']) ? $data['labels'] : explode(',', $data['labels']);
        
        foreach ($labels as $labelName) {
            $labelName = trim($labelName);
            if (!empty($labelName)) {
                $labelId = get_or_create_label($labelName);
                assign_label_to_villa((int)$data['id'], $labelId);
            }
        }
    }
    
    // Commit transaction
    $pdo->commit();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Villa successfully updated',
        'villa_id' => (int)$data['id']
    ]);
    
} catch (PDOException $e) {
    // Rollback transaction
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'error' => 'Database error',
        'message' => $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'error' => 'Server error',
        'message' => $e->getMessage()
    ]);
}
?> 