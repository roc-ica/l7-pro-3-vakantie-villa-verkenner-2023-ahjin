<?php
// Suppress warnings and notices that could break JSON output
error_reporting(0);
header('Content-Type: application/json');

include_once '../database.php';

// Check for valid request method
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && $_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Method not allowed. Please use POST or GET.']);
    exit;
}

// Check for villa ID
$villaId = isset($_GET['id']) ? intval($_GET['id']) : (isset($_POST['id']) ? intval($_POST['id']) : 0);

if ($villaId <= 0) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Missing or invalid villa ID']);
    exit;
}

try {
    // Start transaction
    $pdo = connect_db();
    $pdo->beginTransaction();
    
    // First, get any images associated with this villa
    $images = get_villa_images($villaId);
    
    // Delete any labels associated with this villa
    $stmt = $pdo->prepare("DELETE FROM villa_labels WHERE villa_id = :id");
    $stmt->execute([':id' => $villaId]);
    
    // Delete any images associated with this villa
    $stmt = $pdo->prepare("DELETE FROM villa_images WHERE villa_id = :id");
    $stmt->execute([':id' => $villaId]);
    
    // Finally, delete the villa itself
    delete_villa($villaId);
    
    // Commit transaction
    $pdo->commit();
    
    // Try to delete physical image files
    foreach ($images as $image) {
        $imagePath = $_SERVER['DOCUMENT_ROOT'] . $image['image_path'];
        if (file_exists($imagePath)) {
            @unlink($imagePath);
        }
    }
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Villa successfully deleted',
        'villa_id' => $villaId
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