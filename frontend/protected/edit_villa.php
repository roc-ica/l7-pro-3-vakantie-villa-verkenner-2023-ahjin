<?php
include 'components/header.php';
include_once '../../db/class/database.php';
$conn = (new Database())->getConnection();

// Controleer of een villa ID is opgegeven
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Villa ID ontbreekt.");
}

$id = $_GET['id'];

// Villa ophalen
$stmt = $conn->prepare("SELECT * FROM villas WHERE id = :id");
$stmt->execute(['id' => $id]);
$villa = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$villa) {
    die("Villa niet gevonden.");
}

// Villa bijwerken
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $stmt = $conn->prepare("UPDATE villas SET straat = :straat, post_c = :post_c, kamers = :kamers, 
                            badkamers = :badkamers, slaapkamers = :slaapkamers, oppervlakte = :oppervlakte, prijs = :prijs 
                            WHERE id = :id");

    $stmt->execute([
        'id' => $id,
        'straat' => $_POST['straat'],
        'post_c' => $_POST['post_c'],
        'kamers' => $_POST['kamers'],
        'badkamers' => $_POST['badkamers'],
        'slaapkamers' => $_POST['slaapkamers'],
        'oppervlakte' => $_POST['oppervlakte'],
        'prijs' => $_POST['prijs']
    ]);

    header("Location: villas.php");
    exit();
}

// Afbeelding ophalen
$stmt = $conn->prepare("SELECT image_path FROM villa_images WHERE villa_id = :villa_id LIMIT 1");
$stmt->execute(['villa_id' => $id]);
$image = $stmt->fetch(PDO::FETCH_ASSOC);
$imagePath = $image ? $image['image_path'] : 'villa-placeholder.jpg'; // Gebruik placeholder als er geen afbeelding is

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
        <form method="post" class="villa-form">
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
            <button type="submit" class="submit-btn">Opslaan</button>
        </form>
    </div>
</div>

</body>
</html>
