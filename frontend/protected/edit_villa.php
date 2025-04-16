<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include_once '../../db/class/database.php';
require_once __DIR__ . '../../../db/class/sessions.php';

// Check if Database class exists
if (!class_exists('Database')) {
    die("Database class not found. Check the path to database.php");
}

// Try to create database connection with error handling
try {
    $db = new Database();
    $conn = $db->getConnection();

    // Check if connection was successful
    if (!$conn) {
        throw new Exception("Database connection failed. Check your database credentials and server status.");
    }
} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}

// Secure the page
if (!SessionManager::validateAdminSession()) {
    header('Location: login.php?error=auth');
    exit;
}

// --- Initialization ---
$villa = null;
$existingImages = [];
$selectedFeatures = [];
$selectedLocations = [];
$featureOptions = [];
$locationOptions = [];
$pageTitle = "Nieuwe Woning Toevoegen";
$formAction = "edit_villa.php"; // Default action is add
$villaId = null;
$message = '';

// --- Determine Mode (Add vs Edit) ---
if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $villaId = (int)$_GET['id'];
    $pageTitle = "Woning Bewerken";
    $formAction = "edit_villa.php?id=" . $villaId;

    // --- Fetch Existing Data (Edit Mode) ---
    if ($conn) {
        try {
            // Fetch villa details
    $stmt = $conn->prepare("SELECT * FROM villas WHERE id = :id");
            $stmt->bindParam(':id', $villaId, PDO::PARAM_INT);
            $stmt->execute();
    $villa = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$villa) {
                header("Location: villas.php?error=notfound");
                exit;
            }

            // Fetch existing images
            $imgStmt = $conn->prepare("SELECT id, image_path, is_hoofdfoto, is_main FROM villa_images WHERE villa_id = :id ORDER BY id");
            $imgStmt->bindParam(':id', $villaId, PDO::PARAM_INT);
            $imgStmt->execute();
            $existingImages = $imgStmt->fetchAll(PDO::FETCH_ASSOC);

            // Fetch selected features
            $featStmt = $conn->prepare("SELECT option_id FROM villa_feature_options WHERE villa_id = :id");
            $featStmt->bindParam(':id', $villaId, PDO::PARAM_INT);
            $featStmt->execute();
            $selectedFeatures = $featStmt->fetchAll(PDO::FETCH_COLUMN, 0);

            // Fetch selected locations
            $locStmt = $conn->prepare("SELECT option_id FROM villa_location_options WHERE villa_id = :id");
            $locStmt->bindParam(':id', $villaId, PDO::PARAM_INT);
            $locStmt->execute();
            $selectedLocations = $locStmt->fetchAll(PDO::FETCH_COLUMN, 0);

        } catch (PDOException $e) {
            error_log("Error fetching villa data for edit: " . $e->getMessage());
            $message = "<p class='error-message'>Fout bij ophalen van villa gegevens.</p>";
            // Optionally redirect or disable form
        }
    }
}

// --- Fetch Options for Form (Always needed) ---
if ($conn) {
    try {
        $featureOptions = $conn->query("SELECT id, name FROM feature_options ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
        $locationOptions = $conn->query("SELECT id, name FROM location_options ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
         error_log("Error fetching options: " . $e->getMessage());
         $message .= "<p class='error-message'>Fout bij ophalen van opties.</p>";
    }
}

// --- Handle Form Submission (Add or Edit) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_villa'])) {
    if ($conn) {
        // Sanitize and retrieve form data
        $titel = trim($_POST['titel'] ?? '');
        $straat = trim($_POST['straat'] ?? '');
        $post_c = trim($_POST['post_c'] ?? '');
        $plaatsnaam = trim($_POST['plaatsnaam'] ?? '');
        $plaats = trim($_POST['plaats'] ?? ''); // Assuming 'plaats' is needed, adjust if not
        $kamers = filter_input(INPUT_POST, 'kamers', FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
        $badkamers = filter_input(INPUT_POST, 'badkamers', FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
        $slaapkamers = filter_input(INPUT_POST, 'slaapkamers', FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
        $oppervlakte = filter_input(INPUT_POST, 'oppervlakte', FILTER_VALIDATE_FLOAT);
        $prijs = filter_input(INPUT_POST, 'prijs', FILTER_VALIDATE_INT, ["options" => ["min_range" => 0]]);
        $omschrijving = trim($_POST['omschrijving'] ?? '');
        $verkocht = isset($_POST['verkocht']) ? 1 : 0;
        $featured = isset($_POST['featured']) ? 1 : 0;
        $features = $_POST['features'] ?? [];
        $locations = $_POST['locations'] ?? [];
        $mainImageId = isset($_POST['is_hoofdfoto']) ? (int)$_POST['is_hoofdfoto'] : null;
        $imagesToDelete = $_POST['delete_image'] ?? [];

        // Basic Validation (add more as needed)
        if (empty($titel) || empty($straat) || empty($post_c) || empty($plaatsnaam) || $kamers === false || $badkamers === false || $slaapkamers === false || $oppervlakte === false || $prijs === false) {
            $message = "<p class='error-message'>Vul alle vereiste velden correct in.</p>";
        } else {
            try {
                $conn->beginTransaction();

                // --- Insert or Update Villa --- 
                if ($villaId) { // Edit Mode
                    $updateStmt = $conn->prepare("
                        UPDATE villas SET 
                        titel = :titel, straat = :straat, post_c = :post_c, plaatsnaam = :plaatsnaam, plaats = :plaats,
                        kamers = :kamers, badkamers = :badkamers, slaapkamers = :slaapkamers, 
                        oppervlakte = :oppervlakte, prijs = :prijs, omschrijving = :omschrijving, 
                        verkocht = :verkocht, featured = :featured
                        WHERE id = :id
                    ");
                    $updateStmt->execute([
                        ':titel' => $titel, ':straat' => $straat, ':post_c' => $post_c, ':plaatsnaam' => $plaatsnaam, ':plaats' => $plaats,
                        ':kamers' => $kamers, ':badkamers' => $badkamers, ':slaapkamers' => $slaapkamers,
                        ':oppervlakte' => $oppervlakte, ':prijs' => $prijs, ':omschrijving' => $omschrijving,
                        ':verkocht' => $verkocht, ':featured' => $featured,
                        ':id' => $villaId
                    ]);
                     $currentVillaId = $villaId;
                } else { // Add Mode
                    $insertStmt = $conn->prepare("
                        INSERT INTO villas (titel, straat, post_c, plaatsnaam, plaats, kamers, badkamers, slaapkamers, oppervlakte, prijs, omschrijving, verkocht, featured)
                        VALUES (:titel, :straat, :post_c, :plaatsnaam, :plaats, :kamers, :badkamers, :slaapkamers, :oppervlakte, :prijs, :omschrijving, :verkocht, :featured)
                    ");
                     $insertStmt->execute([
                        ':titel' => $titel, ':straat' => $straat, ':post_c' => $post_c, ':plaatsnaam' => $plaatsnaam, ':plaats' => $plaats,
                        ':kamers' => $kamers, ':badkamers' => $badkamers, ':slaapkamers' => $slaapkamers,
                        ':oppervlakte' => $oppervlakte, ':prijs' => $prijs, ':omschrijving' => $omschrijving,
                        ':verkocht' => $verkocht, ':featured' => $featured
                    ]);
                    $currentVillaId = $conn->lastInsertId();
                }

                 // --- Update Options (Delete existing, Insert new) ---
                 $deleteFeatStmt = $conn->prepare("DELETE FROM villa_feature_options WHERE villa_id = :id");
                 $deleteFeatStmt->execute([':id' => $currentVillaId]);
                 $insertFeatStmt = $conn->prepare("INSERT INTO villa_feature_options (villa_id, option_id) VALUES (:vid, :oid)");
                 foreach ($features as $optionId) {
                     $insertFeatStmt->execute([':vid' => $currentVillaId, ':oid' => $optionId]);
                 }

                 $deleteLocStmt = $conn->prepare("DELETE FROM villa_location_options WHERE villa_id = :id");
                 $deleteLocStmt->execute([':id' => $currentVillaId]);
                 $insertLocStmt = $conn->prepare("INSERT INTO villa_location_options (villa_id, option_id) VALUES (:vid, :oid)");
                 foreach ($locations as $optionId) {
                     $insertLocStmt->execute([':vid' => $currentVillaId, ':oid' => $optionId]);
                 }
                
                // --- Handle Image Deletion ---
                 if (!empty($imagesToDelete)) {
                     $placeholders = implode(',', array_fill(0, count($imagesToDelete), '?'));
                     // Fetch paths before deleting from DB
                     $imgPathsStmt = $conn->prepare("SELECT image_path FROM villa_images WHERE id IN ($placeholders) AND villa_id = ?");
                     $imgParams = array_merge($imagesToDelete, [$currentVillaId]);
                     $imgPathsStmt->execute($imgParams);
                     $pathsToDelete = $imgPathsStmt->fetchAll(PDO::FETCH_COLUMN, 0);

                     // Delete from DB
                     $deleteImgStmt = $conn->prepare("DELETE FROM villa_images WHERE id IN ($placeholders) AND villa_id = ?");
                     if ($deleteImgStmt->execute($imgParams)) {
                         // Delete files from server
                         foreach ($pathsToDelete as $relPath) {
                            $fullPath = __DIR__ . '/../' . $relPath;
                             if (file_exists($fullPath)) {
                                 unlink($fullPath);
                             }
                         }
                     } else {
                          error_log("Failed to delete image records from DB for villa ID: " . $currentVillaId);
                     }
                 }
                
                // --- Handle Main Image Setting ---
                if ($mainImageId !== null) {
                    // First, set all images for this villa to not be main
                    $resetMainStmt = $conn->prepare("UPDATE villa_images SET is_hoofdfoto = 0, is_main = 0 WHERE villa_id = :vid");
                    $resetMainStmt->execute([':vid' => $currentVillaId]);
                    // Then, set the selected image as main
                    $setMainStmt = $conn->prepare("UPDATE villa_images SET is_hoofdfoto = 1, is_main = 1 WHERE id = :img_id AND villa_id = :vid");
                    $setMainStmt->execute([':img_id' => $mainImageId, ':vid' => $currentVillaId]);
                } elseif ($villaId && empty($imagesToDelete) && count($existingImages) == 0 && empty($_FILES['new_images']['name'][0])) {
                    // If editing, no new images, no deletions, and no main image was selected, 
                    // ensure at least one is main if possible (or handle case with no images)
                    // This logic might need refinement based on exact requirements
                } 

                // --- Handle New Image Uploads ---
                 $uploadDir = __DIR__ . '/../uploads/'; // Absolute path for moving files
                 $dbPathPrefix = 'uploads/'; // Relative path to store in DB
            if (!is_dir($uploadDir)) {
                     mkdir($uploadDir, 0775, true); // Use 0775 for permissions
                 }

                 if (isset($_FILES['new_images']) && is_array($_FILES['new_images']['name'])) {
                     $imageInsertStmt = $conn->prepare("INSERT INTO villa_images (villa_id, image_path, is_hoofdfoto, is_main) VALUES (:vid, :path, :main, :main)");
                     $totalFiles = count($_FILES['new_images']['name']);
                     $maxImages = 5; // Define the limit
                     $uploadedCount = 0; // Counter for uploaded images in this request
                     
                     // Fetch current number of images again AFTER potential deletions
                     $countStmt = $conn->prepare("SELECT COUNT(*) FROM villa_images WHERE villa_id = :id");
                     $countStmt->execute([':id' => $currentVillaId]);
                     $currentImageCount = $countStmt->fetchColumn();

                     for ($i = 0; $i < $totalFiles; $i++) {
                         // Check if adding this image exceeds the limit
                         if (($currentImageCount + $uploadedCount) >= $maxImages) {
                             $message .= "<p class='error-message'>Maximum aantal afbeeldingen ({$maxImages}) bereikt. Niet alle nieuwe afbeeldingen zijn geüpload.</p>";
                             error_log("Image upload limit reached for villa ID: " . $currentVillaId);
                             break; // Stop processing further uploads
                         }
                         
                         if ($_FILES['new_images']['error'][$i] === UPLOAD_ERR_OK) {
                             $tmpName = $_FILES['new_images']['tmp_name'][$i];
                             $fileName = basename($_FILES['new_images']['name'][$i]);
                             $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
                             $newFileName = 'villa_' . $currentVillaId . '_' . time() . '_' . uniqid() . '.' . $fileExtension;
            $destination = $uploadDir . $newFileName;
                             $dbPath = $dbPathPrefix . $newFileName;

            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                             if (in_array($fileExtension, $allowedExtensions)) {
                                 if (move_uploaded_file($tmpName, $destination)) {
                                     // Determine if this new upload should be the main image
                                     // Condition: No main image set yet AND this is the very first image for this villa (after deletions)
                                     $isMain = ($mainImageId === null && ($currentImageCount + $uploadedCount) == 0); 
                                     
                                     $imageInsertStmt->execute([
                                         ':vid' => $currentVillaId,
                                         ':path' => $dbPath,
                                         ':main' => $isMain ? 1 : 0
                                     ]);
                                     $uploadedCount++; // Increment successfully uploaded count
                                     
                                     // If we just inserted the first image, mark it as main
                                     if($isMain) $mainImageId = $conn->lastInsertId(); 
                                     
                                 } else {
                                     error_log("Failed to move uploaded file: " . $fileName . " to " . $destination . ". Check permissions and path.");
                                     $message .= "<p class='error-message'>Kon bestand '{$fileName}' niet verplaatsen. Controleer server permissies.</p>";
                                 }
                    } else {
                                  error_log("Invalid file type uploaded: " . $fileName);
                                  $message .= "<p class='error-message'>Ongeldig bestandstype: '{$fileName}'. Toegestaan: JPG, PNG, GIF, WEBP.</p>";
                             }
                         } elseif ($_FILES['new_images']['error'][$i] !== UPLOAD_ERR_NO_FILE) {
                             // Log other upload errors
                             error_log("Upload error for file " . ($i+1) . ": " . $_FILES['new_images']['error'][$i]);
                             $message .= "<p class='error-message'>Fout bij uploaden van bestand " . ($i+1) . ".</p>";
                         }
                     }
                      // After all uploads, if no main image is set, make the first available one main
                    if ($mainImageId === null) {
                        // Re-check if a main image exists now after uploads
                         $checkMainStmt = $conn->prepare("SELECT id FROM villa_images WHERE villa_id = :vid AND (is_hoofdfoto = 1 OR is_main = 1) LIMIT 1");
                         $checkMainStmt->execute([':vid' => $currentVillaId]);
                         if ($checkMainStmt->rowCount() == 0) {
                             // No main image set, find the first image (if any) and set it
                            $getFirstStmt = $conn->prepare("SELECT id FROM villa_images WHERE villa_id = :vid ORDER BY id ASC LIMIT 1");
                            $getFirstStmt->execute([':vid' => $currentVillaId]);
                            $firstImageId = $getFirstStmt->fetchColumn();
                            if($firstImageId) {
                                $setFirstMainStmt = $conn->prepare("UPDATE villa_images SET is_hoofdfoto = 1, is_main = 1 WHERE id = :img_id");
                                $setFirstMainStmt->execute([':img_id' => $firstImageId]);
                            }
                         }
                    }
                 }

                $conn->commit();
                header("Location: villas.php?success=" . ($villaId ? 'edited' : 'added'));
                exit;

            } catch (PDOException $e) {
                $conn->rollBack();
                error_log("Error saving villa: " . $e->getMessage());
                $message = "<p class='error-message'>Databasefout bij opslaan: " . $e->getMessage() . "</p>";
            } catch (Exception $e) {
                 $conn->rollBack(); // Rollback for general exceptions too
                 error_log("Error saving villa: " . $e->getMessage());
                 $message = "<p class='error-message'>Fout: " . $e->getMessage() . "</p>";
            }
        }
    } else {
        $message = "<p class='error-message'>Database connectie mislukt.</p>";
    }
}

$db->closeConnection($conn); // Close connection after processing or fetching

?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?> - Admin Dashboard</title>
    <link rel="stylesheet" href="styles/admin_main.css">
    <link rel="stylesheet" href="styles/admin_forms.css"> <!-- Specific styles for forms -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>

<body class="page-edit-villa">
    <main class="admin-content" style="margin-left: 0; width: 100%;">
        <header class="admin-header">
            <h1><?= $pageTitle ?></h1>
            <div class="admin-action-buttons">
                 <a href="admin.php" class="btn btn-secondary"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                 <a href="villas.php" class="btn btn-back"><i class="fas fa-arrow-left"></i> Terug naar Overzicht</a>
        </div>
        </header>

        <?= $message ?>

        <section class="form-container">
            <form action="<?= $formAction ?>" method="POST" enctype="multipart/form-data">
                <input type="hidden" name="submit_villa" value="1">

                <div class="form-section">
                    <h3>Basisgegevens</h3>
                     <div class="form-group">
                        <label for="titel">Titel *</label>
                        <input type="text" id="titel" name="titel" required value="<?= htmlspecialchars($villa['titel'] ?? '') ?>">
                    </div>
                    <div class="form-row">
                        <div class="form-group form-group-half">
                            <label for="straat">Straat *</label>
                            <input type="text" id="straat" name="straat" required value="<?= htmlspecialchars($villa['straat'] ?? '') ?>">
                        </div>
                         <div class="form-group form-group-quarter">
                            <label for="post_c">Postcode *</label>
                            <input type="text" id="post_c" name="post_c" required value="<?= htmlspecialchars($villa['post_c'] ?? '') ?>">
                        </div>
                        <div class="form-group form-group-quarter">
                            <label for="plaatsnaam">Plaatsnaam *</label>
                            <input type="text" id="plaatsnaam" name="plaatsnaam" required value="<?= htmlspecialchars($villa['plaatsnaam'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-row">
                         <div class="form-group form-group-third">
                            <label for="kamers">Totaal Kamers *</label>
                            <input type="number" id="kamers" name="kamers" required min="0" value="<?= htmlspecialchars($villa['kamers'] ?? 0) ?>">
                        </div>
                       <div class="form-group form-group-third">
                            <label for="slaapkamers">Slaapkamers *</label>
                            <input type="number" id="slaapkamers" name="slaapkamers" required min="0" value="<?= htmlspecialchars($villa['slaapkamers'] ?? 0) ?>">
                        </div>
                         <div class="form-group form-group-third">
                            <label for="badkamers">Badkamers *</label>
                            <input type="number" id="badkamers" name="badkamers" required min="0" value="<?= htmlspecialchars($villa['badkamers'] ?? 0) ?>">
                </div>
                    </div>
                     <div class="form-row">
                        <div class="form-group form-group-half">
                            <label for="oppervlakte">Oppervlakte (m²) *</label>
                            <input type="number" id="oppervlakte" name="oppervlakte" required min="0" step="0.01" value="<?= htmlspecialchars($villa['oppervlakte'] ?? '') ?>">
                        </div>
                        <div class="form-group form-group-half">
                            <label for="prijs">Prijs (€) *</label>
                            <input type="number" id="prijs" name="prijs" required min="0" value="<?= htmlspecialchars($villa['prijs'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="omschrijving">Omschrijving</label>
                        <textarea id="omschrijving" name="omschrijving" rows="6"><?= htmlspecialchars($villa['omschrijving'] ?? '') ?></textarea>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Status & Opties</h3>
                <div class="form-row">
                        <div class="form-group form-group-half checkbox-group">
                            <input type="checkbox" id="verkocht" name="verkocht" value="1" <?= ($villa['verkocht'] ?? 0) ? 'checked' : '' ?>>
                            <label for="verkocht">Markeer als Verkocht</label>
                        </div>
                         <div class="form-group form-group-half checkbox-group">
                            <input type="checkbox" id="featured" name="featured" value="1" <?= ($villa['featured'] ?? 0) ? 'checked' : '' ?>>
                            <label for="featured">Uitgelichte Woning (Homepage)</label>
                        </div>
                    </div>
                     <div class="form-row">
                        <div class="form-group form-group-half">
                            <label>Eigenschappen</label>
                            <div class="checkbox-list">
                            <?php foreach ($featureOptions as $option): ?>
                                <div class="checkbox-item">
                                    <input type="checkbox" id="feature_<?= $option['id'] ?>" name="features[]" value="<?= $option['id'] ?>" <?= in_array($option['id'], $selectedFeatures) ? 'checked' : '' ?>>
                                    <label for="feature_<?= $option['id'] ?>"><?= htmlspecialchars($option['name']) ?></label>
                                </div>
                            <?php endforeach; ?>
                            </div>
                        </div>
                         <div class="form-group form-group-half">
                            <label>Ligging</label>
                             <div class="checkbox-list">
                            <?php foreach ($locationOptions as $option): ?>
                                 <div class="checkbox-item">
                                    <input type="checkbox" id="location_<?= $option['id'] ?>" name="locations[]" value="<?= $option['id'] ?>" <?= in_array($option['id'], $selectedLocations) ? 'checked' : '' ?>>
                                    <label for="location_<?= $option['id'] ?>"><?= htmlspecialchars($option['name']) ?></label>
                                </div>
                            <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>

                 <div class="form-section">
                    <h3>Afbeeldingen Beheren</h3>
                    <div class="form-group">
                         <label for="new_images">Nieuwe Afbeeldingen Toevoegen (max. 5)</label>
                         <input type="file" id="new_images" name="new_images[]" multiple accept="image/*">
                         <small>Selecteer één of meerdere afbeeldingen (JPG, PNG, GIF, WEBP).</small>
                    </div>

                     <?php if (!empty($existingImages)): ?>
                    <div class="form-group">
                         <label>Huidige Afbeeldingen</label>
                         <div class="image-management-grid">
                             <?php foreach ($existingImages as $img): ?>
                                 <div class="image-preview-item">
                                     <img src="../<?= htmlspecialchars($img['image_path']) ?>" alt="Voorbeeld afbeelding">
                                     <div class="image-actions">
                                         <label class="main-image-label" title="Stel in als hoofdfoto">
                                             <input type="radio" name="is_hoofdfoto" value="<?= $img['id'] ?>" <?= ($img['is_hoofdfoto'] || $img['is_main']) ? 'checked' : '' ?>>
                                             <i class="fas fa-star"></i> Hoofd
                                         </label>
                                         <label class="delete-image-label" title="Verwijder afbeelding">
                                             <input type="checkbox" name="delete_image[]" value="<?= $img['id'] ?>">
                                             <i class="fas fa-trash-alt"></i> Verwijder
                                         </label>
                                     </div>
                                 </div>
                             <?php endforeach; ?>
                         </div>
                         <small>Selecteer 'Hoofd' om de hoofdafbeelding in te stellen. Vink 'Verwijder' aan om afbeeldingen te verwijderen bij opslaan.</small>
                    </div>
                     <?php else: ?>
                        <p>Er zijn nog geen afbeeldingen voor deze woning.</p>
                <?php endif; ?>
                </div>

                <div class="form-actions">
                    <button type="submit" name="submit_villa" value="1" class="btn btn-save"><i class="fas fa-save"></i> Opslaan</button>
                    <a href="villas.php" class="btn btn-cancel">Annuleren</a>
                </div>
            </form>
        </section>

    </main>
     <script>
        // Optional: Add JS for image preview or other interactions if needed
    </script>
</body>

</html>