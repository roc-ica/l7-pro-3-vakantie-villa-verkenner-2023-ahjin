<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real Estate Website</title>
    <link rel="stylesheet" href="../styles/woningen.css">
    <link rel="stylesheet" href="../includes/head.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="../includes/script.js" defer></script>
</head>

<body>
<?php include_once '../includes/header.php'; ?>

<div class="container">
    <aside class="filters">
        <div class="search-bar">
            <input type="text" placeholder="zoeken">
        </div>
        <div class="filter-section">
            <h3>Filters</h3>
            <h4>Prijs (€)</h4>
            <div class="price-range">
                <span id="price-display">€10.000 - €10.000.000</span>
                <div class="slider-container">
                    <input type="range" id="price-slider" min="10000" max="10000000" step="1000" value="10000">
                </div>
                <script src="../script/script.js"></script>
            </div>
            <h4>Woning met:</h4>
            <ul>
                <li><label><input type="checkbox"> Zwembad</label></li>
                <li><label><input type="checkbox"> Winkels in de buurt</label></li>
                <li><label><input type="checkbox"> Entertainment in de buurt</label></li>
                <li><label><input type="checkbox"> Op een privépark</label></li>
            </ul>
            <h4>Ligging</h4>
            <ul>
                <li><label><input type="checkbox"> Bij het bos</label></li>
                <li><label><input type="checkbox"> Aan het water</label></li>
                <li><label><input type="checkbox"> Bij de stad</label></li>
                <li><label><input type="checkbox"> In het heuvelland</label></li>
            </ul>
            <div class="area-range">
                <h4>m²</h4>
                10m² - 1000m²
                <input type="range" min="10" max="1000">
            </div>
            <div class="properties">
                <h4>Eigenschappen</h4>
                <div class="rooms">
                    Kamers: <button>&lt;</button> 2 <button>&gt;</button>
                </div>
                <div class="bedrooms">
                    Slaapkamers: <button>&lt;</button> 2 <button>&gt;</button>
                </div>
                <div class="bathrooms">
                    Badkamers: <button>&lt;</button> 2 <button>&gt;</button>
                </div>
            </div>
        </div>
    </aside>

    <main class="properties-grid">
        <?php
        include '../../db/class/database.php';
        // Verkrijg de databaseverbinding
        $conn = (new Database())->getConnection();

        $properties = [];

        // SQL query voor villa's en afbeelding
        $query = "
            SELECT v.id, v.straat, v.post_c, v.oppervlakte, v.prijs, 
                   (SELECT image_path FROM villa_images WHERE villa_id = v.id LIMIT 1) AS image
            FROM villas v";
        $result = $conn->query($query);

        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {
            // Haal labels op
            $labels_query = "
                SELECT l.naam 
                FROM labels l 
                JOIN villa_labels vl ON l.id = vl.label_id 
                WHERE vl.villa_id = " . $row['id'];
            $labels_result = $conn->query($labels_query);

            $tags = [];
            while ($label = $labels_result->fetch(PDO::FETCH_ASSOC)) {
                $tags[] = $label['naam'];
            }

            // Voeg gegevens toe aan properties array
            $properties[] = [
                'image' => $row['image'] ?: '../../assets/img/default.png', // Fallback afbeelding
                'address' => $row['straat'] . ', ' . $row['post_c'],
                'price' => '€ ' . number_format($row['prijs'], 2, ',', '.'),
                'tags' => $tags,
                'size' => $row['oppervlakte'] . 'm²'
            ];
        }

        $conn = null; // Sluit de verbinding
        ?>

        <!-- HTML Output van de villa's -->
        <?php foreach ($properties as $property): ?>
            <div class="property-card">
                <img src="<?= $property['image']; ?>" alt="Property Image">
                <h3><?= $property['address']; ?></h3>
                <p class="price"><?= $property['price']; ?></p>
                <div class="tags">
                    <?php foreach ($property['tags'] as $tag): ?>
                        <span class="tag"><?= $tag; ?></span>
                    <?php endforeach; ?>
                </div>
                <p class="size"><?= $property['size']; ?></p>
                <a href="#" class="view-more">Bekijk meer →</a>
            </div>
        <?php endforeach; ?>
        <div class="end-list">einde lijst</div>
        <div class="pagination">
            <button>&lt;</button>
            <span>1</span>
            <button>&gt;</button>
        </div>
    </main>
</div>

<?php include_once '../includes/footer.php'; ?>
</body>
</html>
