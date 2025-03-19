<?php 
// Suppress warnings that could break JSON output
error_reporting(0);

include_once 'logger/server_logger.php';

function connect_db() {
    try {
        // Use absolute path to the database file
        $dbPath = __DIR__ . '/villaVerkenner.db';
        
        // Check if the database file exists
        if (!file_exists($dbPath)) {
            error_log('Database file not found: ' . $dbPath);
        }
        
        // Check permissions and try to fix them
        if (file_exists($dbPath)) {
            if (!is_writable($dbPath)) {
                error_log('Database file is not writable: ' . $dbPath);
                // Try to make it writable
                @chmod($dbPath, 0666);
            }
            
            // Also check the directory
            $dbDir = __DIR__;
            if (!is_writable($dbDir)) {
                error_log('Database directory is not writable: ' . $dbDir);
                // Try to make it writable
                @chmod($dbDir, 0777);
            }
        }
        
        // Connect to the database with proper flags to handle shared access
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_TIMEOUT => 5, // 5 seconds timeout
        ];
        
        $db = new PDO('sqlite:' . $dbPath, null, null, $options);
        return $db;
    } catch (PDOException $e) {
        error_log('Database connection error: ' . $e->getMessage());
        throw $e; // Re-throw to let the calling code handle it
    }
}

function insert_villa(string $straat, string $post_c, int $kamers, int $badkamers, int $slaapkamers, float $oppervlakte, int $prijs) {
    $pdo = connect_db(); 
    $stmt = $pdo->prepare("INSERT INTO villas (straat, post_c, kamers, badkamers, slaapkamers, oppervlakte, prijs) 
                           VALUES (:straat, :post_c, :kamers, :badkamers, :slaapkamers, :oppervlakte, :prijs)");
    $stmt->execute([
        ':straat' => $straat,
        ':post_c' => $post_c,
        ':kamers' => $kamers,
        ':badkamers' => $badkamers,
        ':slaapkamers' => $slaapkamers,
        ':oppervlakte' => $oppervlakte,
        ':prijs' => $prijs
    ]);
    $lastId = $pdo->lastInsertId(); 
    ServerLogger::log("New villa added: ID $lastId, $straat, $post_c, $kamers rooms, $badkamers bathrooms, $slaapkamers bedrooms, $oppervlakte m², €$prijs");
    $pdo = null; 
    return $lastId;
}

function get_villas() {
    $pdo = connect_db();
    $stmt = $pdo->prepare("SELECT * FROM villas");
    $stmt->execute();
    $villas = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $pdo = null;
    return $villas;
}

function get_villa(int $id) {
    $pdo = connect_db();
    $stmt = $pdo->prepare("SELECT * FROM villas WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $villa = $stmt->fetch(PDO::FETCH_ASSOC);
    $pdo = null;
    return $villa;
}

function update_villa(int $id, string $straat, string $post_c, int $kamers, int $badkamers, int $slaapkamers, float $oppervlakte, int $prijs) {
    $pdo = connect_db();
    $stmt = $pdo->prepare("UPDATE villas SET straat = :straat, post_c = :post_c, kamers = :kamers, badkamers = :badkamers, slaapkamers = :slaapkamers, oppervlakte = :oppervlakte, prijs = :prijs WHERE id = :id");
    $stmt->execute([
        ':id' => $id,
        ':straat' => $straat,
        ':post_c' => $post_c,
        ':kamers' => $kamers,
        ':badkamers' => $badkamers,
        ':slaapkamers' => $slaapkamers,
        ':oppervlakte' => $oppervlakte,
        ':prijs' => $prijs
    ]);
    ServerLogger::log("Villa updated: ID $id, $straat, $post_c, $kamers rooms, $badkamers bathrooms, $slaapkamers bedrooms, $oppervlakte m², €$prijs");
    $pdo = null;
}

function delete_villa(int $id) {
    $pdo = connect_db();
    $stmt = $pdo->prepare("DELETE FROM villas WHERE id = :id");
    $stmt->execute([':id' => $id]);
    ServerLogger::log("Villa deleted: ID $id");
    $pdo = null;
}

function filter_villas(
    int $kamers = null, 
    float $oppervlakte = null, 
    int $prijs = null, 
    string $label = null, 
    int $badkamers = null, 
    int $slaapkamers = null
) {
    $pdo = connect_db();
    
    // Build dynamic query
    $conditions = [];
    $params = [];
    
    if ($kamers !== null) {
        $conditions[] = "v.kamers >= :kamers";
        $params[':kamers'] = $kamers;
    }
    if ($oppervlakte !== null) {
        $conditions[] = "v.oppervlakte >= :oppervlakte";
        $params[':oppervlakte'] = $oppervlakte;
    }
    if ($prijs !== null) {
        $conditions[] = "v.prijs <= :prijs";
        $params[':prijs'] = $prijs;
    }
    if ($badkamers !== null) {
        $conditions[] = "v.badkamers >= :badkamers";
        $params[':badkamers'] = $badkamers;
    }
    if ($slaapkamers !== null) {
        $conditions[] = "v.slaapkamers >= :slaapkamers";
        $params[':slaapkamers'] = $slaapkamers;
    }
    if ($label !== null) {
        $conditions[] = "l.naam = :label";
        $params[':label'] = $label;
    }

    $sql = "SELECT DISTINCT v.* 
            FROM villas v
            LEFT JOIN villa_labels vl ON v.id = vl.villa_id
            LEFT JOIN labels l ON vl.label_id = l.id";
            
    if (!empty($conditions)) {
        $sql .= " WHERE " . implode(" AND ", $conditions);
    }

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

/**
 * Functions for villa labels
 */
function get_labels() {
    $pdo = connect_db();
    $stmt = $pdo->prepare("SELECT * FROM labels ORDER BY naam");
    $stmt->execute();
    $labels = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $pdo = null;
    return $labels;
}

function create_label(string $naam) {
    $pdo = connect_db();
    $stmt = $pdo->prepare("INSERT INTO labels (naam) VALUES (:naam)");
    $stmt->execute([':naam' => $naam]);
    $labelId = $pdo->lastInsertId();
    ServerLogger::log("New label added: $naam (ID: $labelId)");
    $pdo = null;
    return $labelId;
}

function get_or_create_label(string $naam) {
    $pdo = connect_db();
    $stmt = $pdo->prepare("SELECT id FROM labels WHERE naam = :naam");
    $stmt->execute([':naam' => $naam]);
    $label = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($label) {
        $labelId = $label['id'];
    } else {
        $stmt = $pdo->prepare("INSERT INTO labels (naam) VALUES (:naam)");
        $stmt->execute([':naam' => $naam]);
        $labelId = $pdo->lastInsertId();
        ServerLogger::log("New label added: $naam (ID: $labelId)");
    }
    
    $pdo = null;
    return $labelId;
}

function assign_label_to_villa(int $villaId, int $labelId) {
    $pdo = connect_db();
    // Check if relationship already exists
    $stmt = $pdo->prepare("SELECT 1 FROM villa_labels WHERE villa_id = :villa_id AND label_id = :label_id");
    $stmt->execute([
        ':villa_id' => $villaId,
        ':label_id' => $labelId
    ]);
    
    if (!$stmt->fetch()) {
        $stmt = $pdo->prepare("INSERT INTO villa_labels (villa_id, label_id) VALUES (:villa_id, :label_id)");
        $stmt->execute([
            ':villa_id' => $villaId,
            ':label_id' => $labelId
        ]);
        ServerLogger::log("Label ID $labelId assigned to villa ID $villaId");
    }
    
    $pdo = null;
}

function get_villa_labels(int $villaId) {
    $pdo = connect_db();
    $stmt = $pdo->prepare("
        SELECT l.* 
        FROM labels l
        JOIN villa_labels vl ON l.id = vl.label_id
        WHERE vl.villa_id = :villa_id
        ORDER BY l.naam
    ");
    $stmt->execute([':villa_id' => $villaId]);
    $labels = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $pdo = null;
    return $labels;
}

function remove_label_from_villa(int $villaId, int $labelId) {
    $pdo = connect_db();
    $stmt = $pdo->prepare("DELETE FROM villa_labels WHERE villa_id = :villa_id AND label_id = :label_id");
    $stmt->execute([
        ':villa_id' => $villaId,
        ':label_id' => $labelId
    ]);
    ServerLogger::log("Label ID $labelId removed from villa ID $villaId");
    $pdo = null;
}

/**
 * Functions for villa images
 */
function save_villa_image(int $villaId, string $imagePath) {
    $pdo = connect_db();
    $stmt = $pdo->prepare("INSERT INTO villa_images (villa_id, image_path) VALUES (:villa_id, :image_path)");
    $stmt->execute([
        ':villa_id' => $villaId,
        ':image_path' => $imagePath
    ]);
    $imageId = $pdo->lastInsertId();
    ServerLogger::log("Image saved for villa ID $villaId: $imagePath (ID: $imageId)");
    $pdo = null;
    return $imageId;
}

function get_villa_images(int $villaId) {
    $pdo = connect_db();
    $stmt = $pdo->prepare("SELECT * FROM villa_images WHERE villa_id = :villa_id");
    $stmt->execute([':villa_id' => $villaId]);
    $images = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $pdo = null;
    return $images;
}

function delete_villa_image(int $imageId) {
    $pdo = connect_db();
    
    // First get the image path to potentially delete the file
    $stmt = $pdo->prepare("SELECT image_path, villa_id FROM villa_images WHERE id = :id");
    $stmt->execute([':id' => $imageId]);
    $image = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($image) {
        // Delete from database
        $stmt = $pdo->prepare("DELETE FROM villa_images WHERE id = :id");
        $stmt->execute([':id' => $imageId]);
        ServerLogger::log("Image ID $imageId deleted from villa ID {$image['villa_id']}");
    }
    
    $pdo = null;
    return $image['image_path'] ?? null;
}

/**
 * Get the primary image for a villa
 */
function get_villa_primary_image($villa_id) {
    global $db;
    
    $query = "SELECT image_path FROM images WHERE villa_id = ? ORDER BY id ASC LIMIT 1";
    
    try {
        $stmt = $db->prepare($query);
        $stmt->bindParam(1, $villa_id, SQLITE3_INTEGER);
        $result = $stmt->execute();
        
        $row = $result->fetchArray(SQLITE3_ASSOC);
        return $row ? $row['image_path'] : null;
    } catch (Exception $e) {
        error_log("Error getting villa primary image: " . $e->getMessage());
        return null;
    }
}
?>
