<?php
/**
 * VillaVerkenner Database API
 * Modern implementation with proper error handling and security
 */

// Set error reporting based on environment
$isProduction = !isset($_SERVER['ENVIRONMENT']) || $_SERVER['ENVIRONMENT'] !== 'development';
if ($isProduction) {
    error_reporting(0); // Suppress all errors in production
} else {
    error_reporting(E_ALL); // Show all errors in development
}

require_once __DIR__ . '/logger/server_logger.php';

/**
 * Database class for managing all database interactions
 */
class Database {
    private static ?PDO $connection = null;
    private static string $dbPath;
    private static array $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_TIMEOUT => 5, // 5 seconds timeout
    ];

    /**
     * Get a database connection
     * 
     * @return PDO The database connection
     */
    public static function getConnection(): PDO {
        if (self::$connection === null) {
            self::connect();
        }
        return self::$connection;
    }

    /**
     * Connect to the database
     * 
     * @return void
     * @throws PDOException If connection fails
     */
    private static function connect(): void {
        try {
            // Set the database path
            self::$dbPath = __DIR__ . '/villaVerkenner.db';

            // Check and fix database file and directory permissions if needed
            self::checkAndFixPermissions();
            
            // Create the database connection
            self::$connection = new PDO('sqlite:' . self::$dbPath, null, null, self::$options);
            
        } catch (PDOException $e) {
            error_log('Database connection error: ' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * Check and fix database permissions
     */
    private static function checkAndFixPermissions(): void {
        // Check if database file exists
        if (!file_exists(self::$dbPath)) {
            error_log('Database file not found: ' . self::$dbPath);
            return;
        }
        
        // Check file permissions
        if (!is_writable(self::$dbPath)) {
            error_log('Database file is not writable: ' . self::$dbPath);
            @chmod(self::$dbPath, 0666);
        }
        
        // Check directory permissions
        $dbDir = __DIR__;
        if (!is_writable($dbDir)) {
            error_log('Database directory is not writable: ' . $dbDir);
            @chmod($dbDir, 0777);
        }
    }
    
    /**
     * Begin a database transaction
     */

    // In PHP biedt de PDO (PHP Data Objects) extensie een consistente interface voor toegang tot databases. 
    // De methode beginTransaction() start een transactie door de autocommit-modus uit te schakelen. 
    // Dit betekent dat wijzigingen in de database niet worden opgeslagen totdat je expliciet commit() aanroept.
    // Als er een fout optreedt, kun je wijzigingen terugdraaien met rollBack().
    public static function beginTransaction(): void {
        self::getConnection()->beginTransaction();
    }
    
    /**
     * Commit a database transaction
     */
    public static function commit(): void {
        self::getConnection()->commit();
    }
    
    /**
     * Rollback a database transaction
     */
    public static function rollback(): void {
        if (self::$connection !== null && self::$connection->inTransaction()) {
            self::$connection->rollBack();
        }
    }
    
    /**
     * Close the database connection
     */
    public static function close(): void {
        self::$connection = null;
    }
}

/**
 * Villa operations
 */
class VillaManager {
    /**
     * Get all villas from the database
     * 
     * @return array All villas
     */
    public static function getAllVillas(): array {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM villas");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Error getting villas: ' . $e->getMessage());
            return [];
        } finally {
            Database::close();
        }
    }
    
    /**
     * Get a specific villa by ID
     * 
     * @param int $id Villa ID
     * @return array|null Villa data or null if not found
     */
    public static function getVilla(int $id): ?array {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM villas WHERE id = :id");
            $stmt->execute([':id' => $id]);
            $villa = $stmt->fetch();
            return $villa ?: null;
        } catch (PDOException $e) {
            error_log('Error getting villa: ' . $e->getMessage());
            return null;
        } finally {
            Database::close();
        }
    }
    
    /**
     * Insert a new villa
     * 
     * @param string $straat Street
     * @param string $post_c Postal code
     * @param int $kamers Rooms
     * @param int $badkamers Bathrooms
     * @param int $slaapkamers Bedrooms
     * @param float $oppervlakte Surface area
     * @param int $prijs Price
     * @return int|null The inserted villa ID or null on failure
     */
    public static function insertVilla(
        string $straat, 
        string $post_c, 
        int $kamers, 
        int $badkamers, 
        int $slaapkamers, 
        float $oppervlakte, 
        int $prijs
    ): ?int {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                INSERT INTO villas (straat, post_c, kamers, badkamers, slaapkamers, oppervlakte, prijs) 
                VALUES (:straat, :post_c, :kamers, :badkamers, :slaapkamers, :oppervlakte, :prijs)
            ");
            $stmt->execute([
                ':straat' => $straat,
                ':post_c' => $post_c,
                ':kamers' => $kamers,
                ':badkamers' => $badkamers,
                ':slaapkamers' => $slaapkamers,
                ':oppervlakte' => $oppervlakte,
                ':prijs' => $prijs
            ]);
            
            $lastId = $db->lastInsertId();
            
            ServerLogger::log("New villa added: ID $lastId, $straat, $post_c, $kamers rooms, $badkamers bathrooms, $slaapkamers bedrooms, $oppervlakte m², €$prijs");
            
            return (int)$lastId;
        } catch (PDOException $e) {
            error_log('Error inserting villa: ' . $e->getMessage());
            return null;
        } finally {
            Database::close();
        }
    }
    
    /**
     * Update an existing villa
     * 
     * @param int $id Villa ID
     * @param string $straat Street
     * @param string $post_c Postal code
     * @param int $kamers Rooms
     * @param int $badkamers Bathrooms
     * @param int $slaapkamers Bedrooms
     * @param float $oppervlakte Surface area
     * @param int $prijs Price
     * @return bool Whether the update was successful
     */
    public static function updateVilla(
        int $id,
        string $straat, 
        string $post_c, 
        int $kamers, 
        int $badkamers, 
        int $slaapkamers, 
        float $oppervlakte, 
        int $prijs
    ): bool {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                UPDATE villas 
                SET straat = :straat, post_c = :post_c, kamers = :kamers, 
                    badkamers = :badkamers, slaapkamers = :slaapkamers, 
                    oppervlakte = :oppervlakte, prijs = :prijs 
                WHERE id = :id
            ");
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
            
            return true;
        } catch (PDOException $e) {
            error_log('Error updating villa: ' . $e->getMessage());
            return false;
        } finally {
            Database::close();
        }
    }
    
    /**
     * Delete a villa
     * 
     * @param int $id Villa ID
     * @return bool Whether the deletion was successful
     */
    public static function deleteVilla(int $id): bool {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("DELETE FROM villas WHERE id = :id");
            $stmt->execute([':id' => $id]);
            
            ServerLogger::log("Villa deleted: ID $id");
            
            return true;
        } catch (PDOException $e) {
            error_log('Error deleting villa: ' . $e->getMessage());
            return false;
        } finally {
            Database::close();
        }
    }
    
    /**
     * Filter villas based on various criteria
     * 
     * @param int|null $kamers Number of rooms
     * @param float|null $oppervlakte Surface area
     * @param int|null $prijs Price
     * @param string|null $label Label
     * @param int|null $badkamers Number of bathrooms
     * @param int|null $slaapkamers Number of bedrooms
     * @return array Filtered villas
     */
    public static function filterVillas(
        ?int $kamers = null, 
        ?float $oppervlakte = null, 
        ?int $prijs = null, 
        ?string $label = null, 
        ?int $badkamers = null, 
        ?int $slaapkamers = null
    ): array {
        try {
            $db = Database::getConnection();
            
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

            $sql = "
                SELECT DISTINCT v.* 
                FROM villas v
                LEFT JOIN villa_labels vl ON v.id = vl.villa_id
                LEFT JOIN labels l ON vl.label_id = l.id
            ";
                    
            if (!empty($conditions)) {
                $sql .= " WHERE " . implode(" AND ", $conditions);
            }

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Error filtering villas: ' . $e->getMessage());
            return [];
        } finally {
            Database::close();
        }
    }
    
    /**
     * Get primary image for a villa
     * 
     * @param int $villaId Villa ID
     * @return string|null Image path or null if no image exists
     */
    public static function getPrimaryImage(int $villaId): ?string {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                SELECT image_path 
                FROM villa_images 
                WHERE villa_id = :villa_id 
                ORDER BY id ASC 
                LIMIT 1
            ");
            $stmt->execute([':villa_id' => $villaId]);
            $result = $stmt->fetch();
            
            return $result ? $result['image_path'] : null;
        } catch (PDOException $e) {
            error_log('Error getting primary image: ' . $e->getMessage());
            return null;
        } finally {
            Database::close();
        }
    }
}

/**
 * Label operations
 */
class LabelManager {
    /**
     * Get all labels
     * 
     * @return array All labels
     */
    public static function getAllLabels(): array {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM labels ORDER BY naam");
            $stmt->execute();
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Error getting labels: ' . $e->getMessage());
            return [];
        } finally {
            Database::close();
        }
    }
    
    /**
     * Create a new label
     * 
     * @param string $naam Label name
     * @return int|null Label ID or null on failure
     */
    public static function createLabel(string $naam): ?int {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("INSERT INTO labels (naam) VALUES (:naam)");
            $stmt->execute([':naam' => $naam]);
            $labelId = $db->lastInsertId();
            
            ServerLogger::log("New label added: $naam (ID: $labelId)");
            
            return (int)$labelId;
        } catch (PDOException $e) {
            error_log('Error creating label: ' . $e->getMessage());
            return null;
        } finally {
            Database::close();
        }
    }
    
    /**
     * Get or create a label
     * 
     * @param string $naam Label name
     * @return int Label ID
     */
    public static function getOrCreateLabel(string $naam): ?int {
        try {
            $db = Database::getConnection();
            
            // Try to get existing label
            $stmt = $db->prepare("SELECT id FROM labels WHERE naam = :naam");
            $stmt->execute([':naam' => $naam]);
            $label = $stmt->fetch();
            
            if ($label) {
                return (int)$label['id'];
            }
            
            // Create new label
            $stmt = $db->prepare("INSERT INTO labels (naam) VALUES (:naam)");
            $stmt->execute([':naam' => $naam]);
            $labelId = $db->lastInsertId();
            
            ServerLogger::log("New label added: $naam (ID: $labelId)");
            
            return (int)$labelId;
        } catch (PDOException $e) {
            error_log('Error getting or creating label: ' . $e->getMessage());
            return null;
        } finally {
            Database::close();
        }
    }
    
    /**
     * Get labels for a villa
     * 
     * @param int $villaId Villa ID
     * @return array Villa labels
     */
    public static function getVillaLabels(int $villaId): array {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                SELECT l.* 
                FROM labels l
                JOIN villa_labels vl ON l.id = vl.label_id
                WHERE vl.villa_id = :villa_id
                ORDER BY l.naam
            ");
            $stmt->execute([':villa_id' => $villaId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Error getting villa labels: ' . $e->getMessage());
            return [];
        } finally {
            Database::close();
        }
    }
    
    /**
     * Assign a label to a villa
     * 
     * @param int $villaId Villa ID
     * @param int $labelId Label ID
     * @return bool Whether the assignment was successful
     */
    public static function assignLabelToVilla(int $villaId, int $labelId): bool {
        try {
            $db = Database::getConnection();
            
            // Check if relationship already exists
            $stmt = $db->prepare("
                SELECT 1 FROM villa_labels 
                WHERE villa_id = :villa_id AND label_id = :label_id
            ");
            $stmt->execute([
                ':villa_id' => $villaId,
                ':label_id' => $labelId
            ]);
            
            if (!$stmt->fetch()) {
                $stmt = $db->prepare("
                    INSERT INTO villa_labels (villa_id, label_id) 
                    VALUES (:villa_id, :label_id)
                ");
                $stmt->execute([
                    ':villa_id' => $villaId,
                    ':label_id' => $labelId
                ]);
                
                ServerLogger::log("Label ID $labelId assigned to villa ID $villaId");
            }
            
            return true;
        } catch (PDOException $e) {
            error_log('Error assigning label to villa: ' . $e->getMessage());
            return false;
        } finally {
            Database::close();
        }
    }
    
    /**
     * Remove a label from a villa
     * 
     * @param int $villaId Villa ID
     * @param int $labelId Label ID
     * @return bool Whether the removal was successful
     */
    public static function removeLabelFromVilla(int $villaId, int $labelId): bool {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                DELETE FROM villa_labels 
                WHERE villa_id = :villa_id AND label_id = :label_id
            ");
            $stmt->execute([
                ':villa_id' => $villaId,
                ':label_id' => $labelId
            ]);
            
            ServerLogger::log("Label ID $labelId removed from villa ID $villaId");
            
            return true;
        } catch (PDOException $e) {
            error_log('Error removing label from villa: ' . $e->getMessage());
            return false;
        } finally {
            Database::close();
        }
    }
    
    /**
     * Remove all labels from a villa
     * 
     * @param int $villaId Villa ID
     * @return bool Whether the removal was successful
     */
    public static function removeAllVillaLabels(int $villaId): bool {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("DELETE FROM villa_labels WHERE villa_id = :villa_id");
            $stmt->execute([':villa_id' => $villaId]);
            
            ServerLogger::log("All labels removed from villa ID $villaId");
            
            return true;
        } catch (PDOException $e) {
            error_log('Error removing all labels from villa: ' . $e->getMessage());
            return false;
        } finally {
            Database::close();
        }
    }
}

/**
 * Image operations
 */
class ImageManager {
    /**
     * Save an image for a villa
     * 
     * @param int $villaId Villa ID
     * @param string $imagePath Image path
     * @return int|null Image ID or null on failure
     */
    public static function saveImage(int $villaId, string $imagePath): ?int {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("
                INSERT INTO villa_images (villa_id, image_path) 
                VALUES (:villa_id, :image_path)
            ");
            $stmt->execute([
                ':villa_id' => $villaId,
                ':image_path' => $imagePath
            ]);
            
            $imageId = $db->lastInsertId();
            
            ServerLogger::log("Image saved for villa ID $villaId: $imagePath (ID: $imageId)");
            
            return (int)$imageId;
        } catch (PDOException $e) {
            error_log('Error saving image: ' . $e->getMessage());
            return null;
        } finally {
            Database::close();
        }
    }
    
    /**
     * Get all images for a villa
     * 
     * @param int $villaId Villa ID
     * @return array Villa images
     */
    public static function getVillaImages(int $villaId): array {
        try {
            $db = Database::getConnection();
            $stmt = $db->prepare("SELECT * FROM villa_images WHERE villa_id = :villa_id");
            $stmt->execute([':villa_id' => $villaId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log('Error getting villa images: ' . $e->getMessage());
            return [];
        } finally {
            Database::close();
        }
    }
    
    /**
     * Delete an image
     * 
     * @param int $imageId Image ID
     * @return string|null Deleted image path or null if image wasn't found
     */
    public static function deleteImage(int $imageId): ?string {
        try {
            $db = Database::getConnection();
            
            // First get the image path to potentially delete the file
            $stmt = $db->prepare("SELECT image_path, villa_id FROM villa_images WHERE id = :id");
            $stmt->execute([':id' => $imageId]);
            $image = $stmt->fetch();
            
            if ($image) {
                // Delete from database
                $stmt = $db->prepare("DELETE FROM villa_images WHERE id = :id");
                $stmt->execute([':id' => $imageId]);
                
                ServerLogger::log("Image ID $imageId deleted from villa ID {$image['villa_id']}");
                
                return $image['image_path'];
            }
            
            return null;
        } catch (PDOException $e) {
            error_log('Error deleting image: ' . $e->getMessage());
            return null;
        } finally {
            Database::close();
        }
    }
}

// Backwards compatibility function wrappers
// These allow existing code to continue working without modification

function connect_db() {
    return Database::getConnection();
}

function insert_villa(string $straat, string $post_c, int $kamers, int $badkamers, int $slaapkamers, float $oppervlakte, int $prijs) {
    return VillaManager::insertVilla($straat, $post_c, $kamers, $badkamers, $slaapkamers, $oppervlakte, $prijs);
}

function get_villas() {
    return VillaManager::getAllVillas();
}

function get_villa(int $id) {
    return VillaManager::getVilla($id);
}

function update_villa(int $id, string $straat, string $post_c, int $kamers, int $badkamers, int $slaapkamers, float $oppervlakte, int $prijs) {
    return VillaManager::updateVilla($id, $straat, $post_c, $kamers, $badkamers, $slaapkamers, $oppervlakte, $prijs);
}

function delete_villa(int $id) {
    return VillaManager::deleteVilla($id);
}

function filter_villas(int $kamers = null, float $oppervlakte = null, int $prijs = null, string $label = null, int $badkamers = null, int $slaapkamers = null) {
    return VillaManager::filterVillas($kamers, $oppervlakte, $prijs, $label, $badkamers, $slaapkamers);
}

function get_labels() {
    return LabelManager::getAllLabels();
}

function create_label(string $naam) {
    return LabelManager::createLabel($naam);
}

function get_or_create_label(string $naam) {
    return LabelManager::getOrCreateLabel($naam);
}

function get_villa_labels(int $villaId) {
    return LabelManager::getVillaLabels($villaId);
}

function assign_label_to_villa(int $villaId, int $labelId) {
    return LabelManager::assignLabelToVilla($villaId, $labelId);
}

function remove_label_from_villa(int $villaId, int $labelId) {
    return LabelManager::removeLabelFromVilla($villaId, $labelId);
}

function save_villa_image(int $villaId, string $imagePath) {
    return ImageManager::saveImage($villaId, $imagePath);
}

function get_villa_images(int $villaId) {
    return ImageManager::getVillaImages($villaId);
}

function delete_villa_image(int $imageId) {
    return ImageManager::deleteImage($imageId);
}

function get_villa_primary_image($villa_id) {
    return VillaManager::getPrimaryImage((int)$villa_id);
}
?>
