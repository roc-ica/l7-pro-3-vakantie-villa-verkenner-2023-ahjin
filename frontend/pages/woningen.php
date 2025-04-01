<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real Estate Website</title>
    <link rel="stylesheet" href="../styles/woningen.css">
    <link rel="stylesheet" href="../includes/header.css">
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
            $properties = [
                [
                    'image' => '../../assets/img/house1.png',
                    'address' => 'Skólavörðustígur 101 Reykjavík',
                    'price' => '€ 1.203.320,-',
                    'tags' => ['zwembad', 'aan zee', 'winkels in de buurt', 'Entertainment'],
                    'size' => '500m²'
                ],
                [
                    'image' => '../../assets/img/house1.png',
                    'address' => 'Skólavörðustígur 101 Reykjavík',
                    'price' => '€ 1.203.320,-',
                    'tags' => ['zwembad', 'aan zee', 'winkels in de buurt', 'Entertainment'],
                    'size' => '500m²'
                ],
                [
                    'image' => '../../assets/img/house1.png',
                    'address' => 'Skólavörðustígur 101 Reykjavík',
                    'price' => '€ 1.203.320,-',
                    'tags' => ['zwembad', 'aan zee', 'winkels in de buurt', 'Entertainment'],
                    'size' => '500m²'
                ],
                [
                    'image' => '../../assets/img/house1.png',
                    'address' => 'Skólavörðustígur 101 Reykjavík',
                    'price' => '€ 1.203.320,-',
                    'tags' => ['zwembad', 'aan zee', 'winkels in de buurt', 'Entertainment'],
                    'size' => '500m²'
                ],
                [
                    'image' => '../../assets/img/house1.png',
                    'address' => 'Skólavörðustígur 101 Reykjavík',
                    'price' => '€ 1.203.320,-',
                    'tags' => ['zwembad', 'aan zee', 'winkels in de buurt', 'Entertainment'],
                    'size' => '500m²'
                ],
                [
                    'image' => '../../assets/img/house1.png',
                    'address' => 'Skólavörðustígur 101 Reykjavík',
                    'price' => '€ 1.203.320,-',
                    'tags' => ['zwembad', 'aan zee', 'winkels in de buurt', 'Entertainment'],
                    'size' => '500m²'
                ],
            ];

            foreach ($properties as $property) {
                echo '<div class="property-card">';
                echo '<img src="' . $property['image'] . '" alt="Property Image">';
                echo '<h3>' . $property['address'] . '</h3>';
                echo '<p class="price">' . $property['price'] . '</p>';
                echo '<div class="tags">';
                foreach ($property['tags'] as $tag) {
                    echo '<span class="tag">' . $tag . '</span>';
                }
                echo '</div>';
                echo '<p class="size">' . $property['size'] . '</p>';
                echo '<a href="#" class="view-more">bekijk meer →</a>';
                echo '</div>';
            }
            ?>
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