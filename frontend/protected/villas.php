<?php
include 'components/header.php';
include_once '../../db/class/database.php';
$conn = (new Database())->getConnection();

// Haal alle villa's op
$query = "
    SELECT v.id, v.straat, v.post_c, v.oppervlakte, v.prijs, 
           (SELECT image_path FROM villa_images WHERE villa_id = v.id LIMIT 1) AS image,
           GROUP_CONCAT(l.naam SEPARATOR ', ') AS labels
    FROM villas v
    LEFT JOIN villa_labels vl ON v.id = vl.villa_id
    LEFT JOIN labels l ON vl.label_id = l.id
    GROUP BY v.id
";

$result = $conn->query($query);
$villas = $result->fetchAll(PDO::FETCH_ASSOC);
$conn = null;
?>

<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Villa's beheren</title>
    <link rel="stylesheet" href="../protected/styles/villas.css">
</head>
<body>
<div class="container">
    <h1>Villa Beheer</h1>

    <div class="villa-form">
        <h2>Nieuwe villa toevoegen</h2>
        <form id="villaForm">
            <input type="text" id="straat" placeholder="Straatnaam" required>
            <input type="text" id="postcode" placeholder="Postcode" required>
            <input type="number" id="oppervlakte" placeholder="Oppervlakte (m²)" required>
            <input type="number" id="prijs" placeholder="Prijs (€)" required>
            <button type="submit">Villa toevoegen</button>
        </form>
    </div>

    <div class="villa-list">
        <h2>Bestaande villa's</h2>
        <div id="villaContainer">
            <?php foreach ($villas as $villa): ?>
                <div class="villa-item" data-id="<?= $villa['id'] ?>">
                    <img src="<?= $villa['image'] ?: '../../assets/img/default.png' ?>" alt="Villa">
                    <h3><?= htmlspecialchars($villa['straat']) ?>, <?= htmlspecialchars($villa['post_c']) ?></h3>
                    <p>Oppervlakte: <?= $villa['oppervlakte'] ?> m²</p>
                    <p>Prijs: € <?= number_format($villa['prijs'], 2, ',', '.') ?></p>
                    <button class="edit-btn">Bewerken</button>
                    <button class="delete-btn">Verwijderen</button>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<script>
    document.getElementById('villaForm').addEventListener('submit', function(e) {
        e.preventDefault();
        const data = {
            straat: document.getElementById('straat').value,
            postcode: document.getElementById('postcode').value,
            oppervlakte: document.getElementById('oppervlakte').value,
            prijs: document.getElementById('prijs').value
        };

        fetch('/db/api/add_villa.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify(data)
        }).then(res => res.json()).then(response => {
            if (response.success) location.reload();
            else alert('Fout bij toevoegen');
        });
    });

    document.querySelectorAll('.delete-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const id = this.closest('.villa-item').getAttribute('data-id');
            if (confirm('Weet je zeker dat je deze villa wilt verwijderen?')) {
                fetch('/db/api/delete_villa.php?id=' + id, {method: 'POST'})
                    .then(res => res.json()).then(response => {
                    if (response.success) location.reload();
                    else alert('Fout bij verwijderen');
                });
            }
        });
    });

    
</script>
</body>
</html>