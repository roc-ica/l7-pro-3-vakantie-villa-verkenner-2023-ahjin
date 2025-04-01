<?php
include 'components/header.php';
include_once '../../db/class/database.php';
$conn = (new Database())->getConnection();

// Villa toevoegen
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['straat'])) {
    try {
        $conn->beginTransaction();

        // Stap 1: Villa opslaan
        $stmt = $conn->prepare("INSERT INTO villas (straat, post_c, kamers, badkamers, slaapkamers, oppervlakte, prijs) 
                               VALUES (:straat, :post_c, :kamers, :badkamers, :slaapkamers, :oppervlakte, :prijs)");
        $stmt->execute([
            'straat' => $_POST['straat'],
            'post_c' => $_POST['post_c'],
            'kamers' => $_POST['kamers'],
            'badkamers' => $_POST['badkamers'],
            'slaapkamers' => $_POST['slaapkamers'],
            'oppervlakte' => $_POST['oppervlakte'],
            'prijs' => $_POST['prijs']
        ]);
        $villaId = $conn->lastInsertId();

        // Stap 2: Afbeelding uploaden
        if (!empty($_FILES['villa_image']['name'])) {
            $targetDir = "uploads/";
            if (!is_dir($targetDir)) {
                mkdir($targetDir, 0777, true);
            }

            $fileName = time() . "_" . basename($_FILES["villa_image"]["name"]);
            $targetFilePath = $targetDir . $fileName;

            if (move_uploaded_file($_FILES["villa_image"]["tmp_name"], $targetFilePath)) {
                $stmt = $conn->prepare("INSERT INTO villa_images (villa_id, image_path) VALUES (:villa_id, :image_path)");
                $stmt->execute(['villa_id' => $villaId, 'image_path' => $targetFilePath]);
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
    $stmt = $conn->prepare("DELETE FROM villas WHERE id = :id");
    $stmt->execute(['id' => $_GET['delete']]);
    header("Location: villas.php");
    exit();
}

// Villas ophalen
$stmt = $conn->query("SELECT * FROM villas");
$villas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Villa Admin Panel</title>
    <link rel="stylesheet" href="../protected/styles/villas.css">
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
            <div class="form-group">
                <label for="villa_image">Afbeelding uploaden</label>
                <input type="file" name="villa_image" id="villa_image" accept="image/*">
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
                    $imagePath = $image ? $image['image_path'] : 'villa-placeholder.jpg';
                    ?>
                    <img src="<?= htmlspecialchars($imagePath) ?>" alt="Villa Afbeelding">
                </div>
                <div class="villa-info">
                    <h3><?= htmlspecialchars($villa['straat']) ?></h3>
                    <p><?= htmlspecialchars($villa['post_c']) ?></p>
                    <p><?= $villa['kamers'] ?> Kamers - <?= $villa['badkamers'] ?> Badkamers - <?= $villa['slaapkamers'] ?> Slaapkamers</p>
                    <p><?= $villa['oppervlakte'] ?> m² - €<?= number_format($villa['prijs'], 0, ',', '.') ?></p>
                    <div class="villa-actions">
                        <a href="edit_villa.php?id=<?= $villa['id'] ?>" class="action-btn edit-btn">Bewerken</a>
                        <a href="?delete=<?= $villa['id'] ?>" class="action-btn delete-btn" onclick="return confirm('Weet je zeker dat je deze villa wilt verwijderen?');">Verwijderen</a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
