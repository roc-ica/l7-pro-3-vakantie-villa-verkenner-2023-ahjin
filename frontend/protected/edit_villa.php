<?php
// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'components/header.php';
include_once '../../db/class/database.php';

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

// Controleer of een villa ID is opgegeven
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Villa ID ontbreekt.");
}

$id = $_GET['id'];

try {
    // Villa ophalen
    $stmt = $conn->prepare("SELECT * FROM villas WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $villa = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$villa) {
        die("Villa niet gevonden.");
    }

    // Check if featured column exists
    $columns = $conn->query("SHOW COLUMNS FROM villas LIKE 'featured'")->fetchAll();
    $featuredExists = !empty($columns);

    // If featured column doesn't exist, add it
    if (!$featuredExists) {
        $conn->exec("ALTER TABLE villas ADD COLUMN featured TINYINT DEFAULT 0");
        // Update bestaande rij met standaardwaarde
        $conn->prepare("UPDATE villas SET featured = 0 WHERE id = :id")->execute(['id' => $id]);
        $villa['featured'] = 0;
    }
    
    // Villa bijwerken
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $featured = isset($_POST['featured']) ? 1 : 0;

        // Prepare the SQL based on whether featured column exists
        if ($featuredExists) {
            $sql = "UPDATE villas SET straat = :straat, post_c = :post_c, kamers = :kamers, 
                    badkamers = :badkamers, slaapkamers = :slaapkamers, oppervlakte = :oppervlakte, 
                    prijs = :prijs, featured = :featured 
                    WHERE id = :id";
            $params = [
                'id' => $id,
                'straat' => $_POST['straat'],
                'post_c' => $_POST['post_c'],
                'kamers' => $_POST['kamers'],
                'badkamers' => $_POST['badkamers'],
                'slaapkamers' => $_POST['slaapkamers'],
                'oppervlakte' => $_POST['oppervlakte'],
                'prijs' => $_POST['prijs'],
                'featured' => $featured
            ];
        } else {
            $sql = "UPDATE villas SET straat = :straat, post_c = :post_c, kamers = :kamers, 
                    badkamers = :badkamers, slaapkamers = :slaapkamers, oppervlakte = :oppervlakte, 
                    prijs = :prijs 
                    WHERE id = :id";
            $params = [
                'id' => $id,
                'straat' => $_POST['straat'],
                'post_c' => $_POST['post_c'],
                'kamers' => $_POST['kamers'],
                'badkamers' => $_POST['badkamers'],
                'slaapkamers' => $_POST['slaapkamers'],
                'oppervlakte' => $_POST['oppervlakte'],
                'prijs' => $_POST['prijs']
            ];
        }

        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        if (isset($_FILES['villa_image']) && $_FILES['villa_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = '../uploads/';
            $relativePath = 'uploads/';  // This is what we store in database
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            $fileTmpPath = $_FILES['villa_image']['tmp_name'];
            $fileName = basename($_FILES['villa_image']['name']);
            $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);
            $newFileName = 'villa_' . $id . '_' . time() . '.' . $fileExtension;
            $destination = $uploadDir . $newFileName;

            // Check valid image
            $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (in_array(strtolower($fileExtension), $allowedExtensions)) {
                if (move_uploaded_file($fileTmpPath, $destination)) {
                    $stmt = $conn->prepare("SELECT image_path FROM villa_images WHERE villa_id = :villa_id");
                    $stmt->execute(['villa_id' => $id]);
                    $existing = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($existing) {
                        // Remove old file if exists
                        $oldFile = '../' . $existing['image_path'];
                        if (file_exists($oldFile)) {
                            unlink($oldFile);
                        }
                        $stmt = $conn->prepare("UPDATE villa_images SET image_path = :image_path WHERE villa_id = :villa_id");
                    } else {
                        $stmt = $conn->prepare("INSERT INTO villa_images (villa_id, image_path) VALUES (:villa_id, :image_path)");
                    }
                    // Store relative path in database
                    $dbPath = $relativePath . $newFileName;
                    $stmt->execute(['villa_id' => $id, 'image_path' => $dbPath]);
                }
            }
        }

        header("Location: villas.php");
        exit();
    }

    // Afbeelding ophalen
    $stmt = $conn->prepare("SELECT image_path FROM villa_images WHERE villa_id = :villa_id LIMIT 1");
    $stmt->execute(['villa_id' => $id]);
    $image = $stmt->fetch(PDO::FETCH_ASSOC);
    $imagePath = $image ? $image['image_path'] : 'villa-placeholder.jpg'; // Gebruik placeholder als er geen afbeelding is
} catch (PDOException $e) {
    die("Database Error: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Villa Bewerken</title>
    <link rel="stylesheet" href="../protected/styles/villas.css">
</head>

<body>

    <div class="container">
        <div class="titel-overzicht">
            <h1>Villa Bewerken</h1>
            <p class="subtitle">Pas de gegevens van deze villa aan.</p>
        </div>

        <div class="upload-section">
            <h2>Wijzig Villa Gegevens</h2>
            <form method="post" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group half">
                        <label for="straat">Straatnaam</label>
                        <input type="text" name="straat" id="straat" required value="<?= htmlspecialchars($villa['straat']) ?>">
                    </div>
                    <div class="form-group half">
                        <label for="post_c">Postcode</label>
                        <input type="text" name="post_c" id="post_c" required value="<?= htmlspecialchars($villa['post_c']) ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group third">
                        <label for="kamers">Kamers</label>
                        <input type="number" name="kamers" id="kamers" required value="<?= $villa['kamers'] ?>">
                    </div>
                    <div class="form-group third">
                        <label for="badkamers">Badkamers</label>
                        <input type="number" name="badkamers" id="badkamers" required value="<?= $villa['badkamers'] ?>">
                    </div>
                    <div class="form-group third">
                        <label for="slaapkamers">Slaapkamers</label>
                        <input type="number" name="slaapkamers" id="slaapkamers" required value="<?= $villa['slaapkamers'] ?>">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group half">
                        <label for="oppervlakte">Oppervlakte (m²)</label>
                        <input type="number" step="0.01" name="oppervlakte" id="oppervlakte" required value="<?= $villa['oppervlakte'] ?>">
                    </div>
                    <div class="form-group half">
                        <label for="prijs">Prijs (€)</label>
                        <input type="number" name="prijs" id="prijs" required value="<?= $villa['prijs'] ?>">
                    </div>
                </div>
                <?php if ($featuredExists): ?>
                    <div class="form-group checkbox-group">
                        <input type="checkbox" name="featured" id="featured" <?= $villa['featured'] ? 'checked' : '' ?>>
                        <label for="featured">Uitgelichte villa (tonen op homepage)</label>
                    </div>
                    <div class="form-group">
                        <label for="villa_image">Nieuwe afbeelding (optioneel)</label>
                        <input type="file" name="villa_image" id="villa_image" accept="image/*">
                    </div>

                    <div class="form-group">
                        <p>Huidige afbeelding:</p>
                        <img src="../<?= htmlspecialchars($imagePath) ?>" alt="Villa afbeelding" style="max-width: 300px;">
                    </div>
                <?php endif; ?>
                <div class="form-actions">
                    <button type="submit" class="submit-btn">Opslaan</button>
                    <a href="villas.php" class="cancel-btn">Annuleren</a>
                </div>

            </form>
        </div>
    </div>

    <style>
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 20px;
        }

        .cancel-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #f5f5f5;
            color: #333;
            border: 1px solid #ddd;
            border-radius: 4px;
            text-decoration: none;
            font-weight: 500;
            text-align: center;
        }

        .cancel-btn:hover {
            background-color: #e0e0e0;
        }
    </style>

</body>

</html>