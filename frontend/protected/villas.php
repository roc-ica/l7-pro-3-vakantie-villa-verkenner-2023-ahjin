<?php
include 'components/header.php';
include_once '../../db/class/database.php';
$conn = (new Database())->getConnection();

// Villa toevoegen
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['straat'])) {
    if (isset($_POST['id'])) {  // Als we een id hebben, dan bewerken we de villa
        // Villa bijwerken
        $stmt = $conn->prepare("UPDATE villas SET straat = :straat, post_c = :post_c, kamers = :kamers, 
                                badkamers = :badkamers, slaapkamers = :slaapkamers, oppervlakte = :oppervlakte, prijs = :prijs 
                                WHERE id = :id");
        $stmt->execute([
            'id' => $_POST['id'],
            'straat' => $_POST['straat'],
            'post_c' => $_POST['post_c'],
            'kamers' => $_POST['kamers'],
            'badkamers' => $_POST['badkamers'],
            'slaapkamers' => $_POST['slaapkamers'],
            'oppervlakte' => $_POST['oppervlakte'],
            'prijs' => $_POST['prijs']
        ]);
    } else {
        // Villa toevoegen
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
    }
    header("Location: villas.php");
    exit();
}

// Villa verwijderen
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM villas WHERE id = :id");
    $stmt->execute(['id' => $_GET['delete']]);
    header("Location: villas.php");
    exit();
}

// Villa bewerken (we halen de gegevens op voor de geselecteerde villa)
$villaToEdit = null;
if (isset($_GET['edit'])) {
    $stmt = $conn->prepare("SELECT * FROM villas WHERE id = :id");
    $stmt->execute(['id' => $_GET['edit']]);
    $villaToEdit = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Villas ophalen voor weergave
$stmt = $conn->query("SELECT * FROM villas");
$villas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../protected/styles/villas.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Villa Admin Panel</title>
</head>
<body>
<h2>Villa Toevoegen / Bewerken</h2>

<form method="post">
    <?php if ($villaToEdit): ?>
        <input type="hidden" name="id" value="<?= $villaToEdit['id'] ?>"> <!-- Hidden field for villa ID -->
    <?php endif; ?>
    <input type="text" name="straat" placeholder="Straatnaam" required value="<?= $villaToEdit['straat'] ?? '' ?>">
    <input type="text" name="post_c" placeholder="Postcode" required value="<?= $villaToEdit['post_c'] ?? '' ?>">
    <input type="number" name="kamers" placeholder="Kamers" required value="<?= $villaToEdit['kamers'] ?? '' ?>">
    <input type="number" name="badkamers" placeholder="Badkamers" required value="<?= $villaToEdit['badkamers'] ?? '' ?>">
    <input type="number" name="slaapkamers" placeholder="Slaapkamers" required value="<?= $villaToEdit['slaapkamers'] ?? '' ?>">
    <input type="number" step="0.01" name="oppervlakte" placeholder="Oppervlakte (m²)" required value="<?= $villaToEdit['oppervlakte'] ?? '' ?>">
    <input type="number" name="prijs" placeholder="Prijs (€)" required value="<?= $villaToEdit['prijs'] ?? '' ?>">
    <button type="submit"><?= $villaToEdit ? 'Bewerken' : 'Toevoegen' ?></button>
</form>

<h2>Overzicht van Villa's</h2>
<table border="1">
    <tr>
        <th>Straat</th>
        <th>Postcode</th>
        <th>Kamers</th>
        <th>Badkamers</th>
        <th>Slaapkamers</th>
        <th>Oppervlakte (m²)</th>
        <th>Prijs (€)</th>
        <th>Actie</th>
    </tr>
    <?php foreach ($villas as $villa): ?>
        <tr>
            <td><?= htmlspecialchars($villa['straat']) ?></td>
            <td><?= htmlspecialchars($villa['post_c']) ?></td>
            <td><?= $villa['kamers'] ?></td>
            <td><?= $villa['badkamers'] ?></td>
            <td><?= $villa['slaapkamers'] ?></td>
            <td><?= $villa['oppervlakte'] ?></td>
            <td>€ <?= number_format($villa['prijs'], 0, ',', '.') ?></td>
            <td>
                <a href="?edit=<?= $villa['id'] ?>">Bewerken</a> |
                <a href="?delete=<?= $villa['id'] ?>" onclick="return confirm('Weet je zeker dat je deze villa wilt verwijderen?');">Verwijderen</a>
            </td>
        </tr>
    <?php endforeach; ?>
</table>
</body>
</html>
