<?php
// Suppress warnings and notices that could break JSON output
error_reporting(0);
header('Content-Type: application/json');

include_once '../database.php';

// Debug mode - if 'debug' parameter is set, return system info instead of processing the request
if (isset($_GET['debug'])) {
    $dbPath = __DIR__ . '/../villaVerkenner.db';
    $info = [
        'debug' => true,
        'api_dir' => __DIR__,
        'db_expected_path' => $dbPath,
        'db_exists' => file_exists($dbPath),
        'db_readable' => file_exists($dbPath) && is_readable($dbPath),
        'db_writable' => file_exists($dbPath) && is_writable($dbPath),
        'upload_dir' => __DIR__ . '/../../uploads/villas/',
        'upload_dir_exists' => file_exists(__DIR__ . '/../../uploads/villas/'),
        'php_version' => PHP_VERSION,
        'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown',
        'free_disk_space' => disk_free_space('/') !== false ? disk_free_space('/') : 'Unknown',
    ];
    echo json_encode($info);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(['error' => 'Method not allowed. Please use POST.']);
    exit;
}

// Create images directory if it doesn't exist
$uploadDir = __DIR__ . '/../../uploads/villas/';
if (!file_exists($uploadDir)) {
    try {
        // Create directory with more permissive permissions (0777)
        if (!@mkdir($uploadDir, 0777, true)) {
            // If directory creation fails, log the error but continue
            error_log('Failed to create upload directory: ' . $uploadDir);
        } else {
            // Set permissions explicitly after creation for extra safety
            @chmod($uploadDir, 0777);
        }
    } catch (Exception $e) {
        error_log('Exception creating directory: ' . $e->getMessage());
    }
}

// Check if we're getting form data or JSON
$isMultipart = isset($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'multipart/form-data') !== false;

if ($isMultipart) {
    // Handle form data with file uploads
    $data = $_POST;
    $files = $_FILES;
} else {
    // Handle JSON data (no file uploads)
    $data = json_decode(file_get_contents('php://input'), true);
    $files = [];
    
    // If JSON parse failed, check for regular POST data
    if (empty($data) && !empty($_POST)) {
        $data = $_POST;
    }
}

// Validate required fields
$requiredFields = ['straat', 'post_c', 'kamers', 'badkamers', 'slaapkamers', 'oppervlakte', 'prijs'];
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
    
    // Insert the villa
    $villaId = insert_villa(
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
        $labels = is_array($data['labels']) ? $data['labels'] : explode(',', $data['labels']);
        
        foreach ($labels as $labelName) {
            $labelName = trim($labelName);
            if (!empty($labelName)) {
                $labelId = get_or_create_label($labelName);
                assign_label_to_villa($villaId, $labelId);
            }
        }
    }
    
    // Process image uploads
    $uploadedImages = [];
    
    if ($isMultipart && !empty($files['images'])) {
        // Handle multiple image uploads
        $imageFiles = is_array($files['images']['name']) ? $files['images'] : [$files['images']];
        
        for ($i = 0; $i < count($imageFiles['name']); $i++) {
            if ($imageFiles['error'][$i] === UPLOAD_ERR_OK) {
                $tempName = $imageFiles['tmp_name'][$i];
                $originalName = $imageFiles['name'][$i];
                $ext = pathinfo($originalName, PATHINFO_EXTENSION);
                
                // Generate a unique filename
                $newFileName = 'villa_' . $villaId . '_' . uniqid() . '.' . $ext;
                $targetPath = $uploadDir . $newFileName;
                
                // Move the file
                if (move_uploaded_file($tempName, $targetPath)) {
                    // Save image path to database
                    $relativePath = '/uploads/villas/' . $newFileName;
                    $imageId = save_villa_image($villaId, $relativePath);
                    $uploadedImages[] = [
                        'id' => $imageId,
                        'path' => $relativePath
                    ];
                }
            }
        }
    }
    
    // Commit transaction
    $pdo->commit();
    
    // Return success response
    echo json_encode([
        'success' => true,
        'message' => 'Villa successfully added',
        'villa_id' => $villaId,
        'uploaded_images' => $uploadedImages
    ]);
    
} catch (PDOException $e) {
    // Rollback transaction
    if (isset($pdo)) {
        $pdo->rollBack();
    }
    
    // Check for the readonly database error
    if (strpos($e->getMessage(), 'readonly database') !== false) {
        // Log the error with additional details
        $dbPath = __DIR__ . '/../villaVerkenner.db';
        $errorDetails = [
            'message' => $e->getMessage(),
            'db_exists' => file_exists($dbPath),
            'db_readable' => file_exists($dbPath) && is_readable($dbPath),
            'db_writable' => file_exists($dbPath) && is_writable($dbPath),
            'db_permissions' => file_exists($dbPath) ? substr(sprintf('%o', fileperms($dbPath)), -4) : 'N/A',
            'db_dir_writable' => is_writable(__DIR__ . '/..')
        ];
        error_log('Readonly database error: ' . json_encode($errorDetails));
        
        // Try to fix permissions
        if (file_exists($dbPath)) {
            @chmod($dbPath, 0666);
            error_log('Attempted to fix permissions: ' . (is_writable($dbPath) ? 'Success' : 'Failed'));
        }
        
        http_response_code(500); // Internal Server Error
        echo json_encode([
            'error' => 'Database error',
            'message' => 'The database is read-only. Please contact the administrator.',
            'details' => 'This is likely a permissions issue with the database file.'
        ]);
    } else {
        http_response_code(500); // Internal Server Error
        echo json_encode([
            'error' => 'Database error',
            'message' => $e->getMessage()
        ]);
    }
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode([
        'error' => 'Server error',
        'message' => $e->getMessage()
    ]);
}
?> 