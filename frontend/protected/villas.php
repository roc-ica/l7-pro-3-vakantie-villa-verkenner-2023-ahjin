<?php
include 'components/header.php';
include_once '../../db/class/database.php';
include_once '../../db/class/filter.php'; // Include Filter class to get labels

// Improve error handling for database connection
try {
    $db = new Database();
    $conn = $db->getConnection();
    $filter = new Filter(); // Instantiate Filter class
    $availableLabels = $filter->getAvailableLabels(); // Fetch labels
    
    if (!$conn) {
        throw new Exception("Database connection failed. Please check the server configuration.");
    }
    
    // Toggle featured status
    if (isset($_GET['toggle_featured'])) {
        try {
            $conn->beginTransaction();
            
            $villaId = $_GET['toggle_featured'];
            
            // Get current featured status
            $stmt = $conn->prepare("SELECT featured FROM villas WHERE id = :id");
            $stmt->execute(['id' => $villaId]);
            $currentStatus = $stmt->fetchColumn();
            
            // Toggle status
            $newStatus = $currentStatus ? 0 : 1;
            $stmt = $conn->prepare("UPDATE villas SET featured = :featured WHERE id = :id");
            $stmt->execute(['featured' => $newStatus, 'id' => $villaId]);
            
            $conn->commit();
            header("Location: villas.php");
            exit();
        } catch (Exception $e) {
            $conn->rollBack();
            echo "Error updating featured status: " . $e->getMessage();
        }
    }
    
    // Villa toevoegen
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['straat'])) {
        try {
            $conn->beginTransaction();

            // Stap 1: Villa opslaan
            $featured = isset($_POST['featured']) ? 1 : 0;
            $stmt = $conn->prepare("INSERT INTO villas (straat, post_c, kamers, badkamers, slaapkamers, oppervlakte, prijs, featured) 
                                   VALUES (:straat, :post_c, :kamers, :badkamers, :slaapkamers, :oppervlakte, :prijs, :featured)");
            $stmt->execute([
                'straat' => $_POST['straat'],
                'post_c' => $_POST['post_c'],
                'kamers' => $_POST['kamers'],
                'badkamers' => $_POST['badkamers'],
                'slaapkamers' => $_POST['slaapkamers'],
                'oppervlakte' => $_POST['oppervlakte'],
                'prijs' => $_POST['prijs'],
                'featured' => $featured
            ]);
            $villaId = $conn->lastInsertId();

            // Stap 2: Labels opslaan (if any selected)
            if (!empty($_POST['labels']) && is_array($_POST['labels'])) {
                 $stmtLabel = $conn->prepare("INSERT INTO villa_labels (villa_id, label_id) VALUES (:villa_id, :label_id)");
                 foreach ($_POST['labels'] as $labelId) {
                     $stmtLabel->execute(['villa_id' => $villaId, 'label_id' => $labelId]);
                 }
            }

            // Stap 3: Afbeelding uploaden
            if (!empty($_FILES['villa_image']['name'])) {
                $uploadDir = "../uploads/";
                $relativePath = "uploads/";
                
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                $fileExtension = pathinfo($_FILES["villa_image"]["name"], PATHINFO_EXTENSION);
                $newFileName = 'villa_' . $villaId . '_' . time() . '.' . $fileExtension;
                $targetFilePath = $uploadDir . $newFileName;
                $dbPath = $relativePath . $newFileName;

                if (move_uploaded_file($_FILES["villa_image"]["tmp_name"], $targetFilePath)) {
                    $stmt = $conn->prepare("INSERT INTO villa_images (villa_id, image_path) VALUES (:villa_id, :image_path)");
                    $stmt->execute(['villa_id' => $villaId, 'image_path' => $dbPath]);
                }
            }

            $conn->commit();
            header("Location: villas.php");
            exit();
        } catch (Exception $e) {
            $conn->rollBack();
            echo "Fout bij het toevoegen van villa: " . $e->getMessage();
        }
    }

    // Villa verwijderen
    if (isset($_GET['delete'])) {
        try {
            $conn->beginTransaction();
            
            // First delete related records
            $stmt = $conn->prepare("DELETE FROM villa_labels WHERE villa_id = :id");
            $stmt->execute(['id' => $_GET['delete']]);
            
            // Delete villa images (both from database and files)
            $stmt = $conn->prepare("SELECT image_path FROM villa_images WHERE villa_id = :id");
            $stmt->execute(['id' => $_GET['delete']]);
            $images = $stmt->fetchAll(PDO::FETCH_COLUMN);
            
            foreach ($images as $image) {
                $fullPath = '../' . $image;
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }
            
            $stmt = $conn->prepare("DELETE FROM villa_images WHERE villa_id = :id");
            $stmt->execute(['id' => $_GET['delete']]);
            
            // Finally delete the villa
            $stmt = $conn->prepare("DELETE FROM villas WHERE id = :id");
            $stmt->execute(['id' => $_GET['delete']]);
            
            $conn->commit();
            header("Location: villas.php");
            exit();
        } catch (Exception $e) {
            $conn->rollBack();
            echo "Error deleting villa: " . $e->getMessage();
        }
    }

    // Villas ophalen
    $stmt = $conn->query("SELECT * FROM villas");
    $villas = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    echo "Fout bij het verbinden met de database: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Villa Admin Panel</title>
    <link rel="stylesheet" href="../protected/styles/villas.css">
    <style>
        .featured-badge {
            background-color: #00a3a3;
            color: white;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            display: inline-block;
            margin-left: 10px;
        }
        .toggle-featured {
            background-color: #f0f0f0;
            border: 1px solid #ddd;
            color: #333;
            padding: 5px 10px;
            border-radius: 4px;
            text-decoration: none;
            font-size: 0.8rem;
            margin-right: 5px;
        }
        .toggle-featured:hover {
            background-color: #e0e0e0;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="titel-overzicht">
        <h1>Villa Beheer</h1>
        <p class="subtitle">Voeg nieuwe villa's toe, bewerk of verwijder ze.</p>
    </div>

    <div class="upload-section">
        <h2>Nieuwe Villa Toevoegen</h2>
        <form method="post" enctype="multipart/form-data" class="villa-form">
            <div class="form-group">
                <label for="straat">Straatnaam</label>
                <input type="text" name="straat" id="straat" required>
            </div>
            <div class="form-group">
                <label for="post_c">Postcode</label>
                <input type="text" name="post_c" id="post_c" required>
            </div>
            <div class="form-group">
                <label for="kamers">Kamers</label>
                <input type="number" name="kamers" id="kamers" required>
            </div>
            <div class="form-group">
                <label for="badkamers">Badkamers</label>
                <input type="number" name="badkamers" id="badkamers" required>
            </div>
            <div class="form-group">
                <label for="slaapkamers">Slaapkamers</label>
                <input type="number" name="slaapkamers" id="slaapkamers" required>
            </div>
            <div class="form-group">
                <label for="oppervlakte">Oppervlakte (m²)</label>
                <input type="number" name="oppervlakte" id="oppervlakte" required>
            </div>
            <div class="form-group">
                <label for="prijs">Prijs (€)</label>
                <input type="number" name="prijs" id="prijs" required>
            </div>
            <div class="form-group checkbox-group">
                <input type="checkbox" name="featured" id="featured">
                <label for="featured">Uitgelichte villa (tonen op homepage)</label>
            </div>
            <div class="form-group">
                <label for="villa_image">Afbeelding uploaden</label>
                <input type="file" name="villa_image" id="villa_image" accept="image/*">
            </div>
            <div class="form-group">
                <label>Labels</label>
                <div class="labels-checkbox-group">
                    <?php foreach ($availableLabels as $label): ?>
                        <label class="label-checkbox">
                            <input type="checkbox" name="labels[]" value="<?= $label['id'] ?>">
                            <?= htmlspecialchars($label['naam']) ?>
                        </label>
                    <?php endforeach; ?>
                     <?php if (empty($availableLabels)): ?>
                        <p>Geen labels beschikbaar. Voeg eerst labels toe in de database.</p>
                    <?php endif; ?>
                </div>
            </div>
            <button type="submit" class="submit-btn">Toevoegen</button>
        </form>
    </div>

    <div class="villa-grid">
        <?php foreach ($villas as $villa): ?>
            <div class="villa-card">
                <div class="villa-photo">
                    <?php
                    $stmtImg = $conn->prepare("SELECT image_path FROM villa_images WHERE villa_id = :villa_id LIMIT 1");
                    $stmtImg->execute(['villa_id' => $villa['id']]);
                    $image = $stmtImg->fetch(PDO::FETCH_ASSOC);
                    $imagePath = $image ? '../' . $image['image_path'] : '../../assets/img/placeholder-villa.jpg';
                    ?>
                    <img src="<?= htmlspecialchars($imagePath) ?>" alt="Villa Afbeelding">
                    <?php if ($villa['featured']): ?>
                        <span class="featured-badge">Uitgelicht</span>
                    <?php endif; ?>
                </div>
                <div class="villa-info">
                    <h3><?= htmlspecialchars($villa['straat']) ?></h3>
                    <p><?= htmlspecialchars($villa['post_c']) ?></p>
                    <p><?= $villa['kamers'] ?> Kamers - <?= $villa['badkamers'] ?> Badkamers - <?= $villa['slaapkamers'] ?> Slaapkamers</p>
                    <p><?= $villa['oppervlakte'] ?> m² - €<?= number_format($villa['prijs'], 0, ',', '.') ?></p>
                    <p class="villa-tags"><strong>Labels:</strong> 
                        <?php 
                        // Fetch and display labels for this villa
                        $stmtLabels = $conn->prepare(
                            "SELECT l.naam 
                             FROM labels l 
                             JOIN villa_labels vl ON l.id = vl.label_id 
                             WHERE vl.villa_id = :villa_id"
                        );
                        $stmtLabels->execute(['villa_id' => $villa['id']]);
                        $tags = $stmtLabels->fetchAll(PDO::FETCH_COLUMN);
                        echo !empty($tags) ? htmlspecialchars(implode(', ', $tags)) : 'Geen';
                        ?>
                    </p>
                    <div class="villa-actions">
                        <a href="?toggle_featured=<?= $villa['id'] ?>" class="toggle-featured">
                            <?= $villa['featured'] ? 'Verwijder uitlichting' : 'Maak uitgelicht' ?>
                        </a>
                        <a href="edit_villa.php?id=<?= $villa['id'] ?>" class="action-btn edit-btn">Bewerken</a>
                        <a href="?delete=<?= $villa['id'] ?>" class="action-btn delete-btn" id="deleteLink">Verwijderen</a>
                    </div>
                </div>
                <div id="deleteModal" class="modal">
                    <div class="modal-content">
                        <span class="close-btn">&times;</span>
                        <p>Weet je zeker dat je deze villa wilt verwijderen?</p>
                        <button id="confirmDelete" class="confirm-btn">Ja, Verwijderen</button>
                        <button id="cancelDelete" class="cancel-btn">Annuleren</button>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<script>
    // Get the modal and the delete link
    const modal = document.getElementById("deleteModal");
    const deleteLink = document.getElementById("deleteLink");

    // Get the buttons inside the modal
    const confirmDelete = document.getElementById("confirmDelete");
    const cancelDelete = document.getElementById("cancelDelete");

    // Get the close button inside the modal
    const closeBtn = document.getElementsByClassName("close-btn")[0];

    // When the user clicks the delete link, show the modal
    deleteLink.onclick = function(event) {
        event.preventDefault(); // Prevent the default action (i.e., following the link)
        modal.style.display = "block"; // Show the modal
    }

    // When the user clicks on the close button, hide the modal
    closeBtn.onclick = function() {
        modal.style.display = "none";
    }

    // When the user clicks "Annuleren", hide the modal
    cancelDelete.onclick = function() {
        modal.style.display = "none";
    }

    // When the user clicks "Ja, Verwijderen", proceed with the delete action
    confirmDelete.onclick = function() {
        window.location.href = deleteLink.href; // Redirect to the delete URL
    }
</script>
</body>
</html>