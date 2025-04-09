<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Real Estate Website</title>
    <link rel="stylesheet" href="../styles/woningen.css">
    <link rel="stylesheet" href="../includes/header.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="../script/script.js"></script>
</head>

<body>
    
<div class="header-container">
   <?php include '../includes/header.php'; ?>
</div>

<?php
include '../../db/class/database.php';
include '../../db/class/filter.php';

// --- Initialize Filter Values ---
$filters = [
    'zoekterm' => $_GET['zoekterm'] ?? '',
    'min_price' => $_GET['min_price'] ?? 10000, // Default min price
    'max_price' => $_GET['max_price'] ?? 10000000, // Default max price
    'faciliteiten' => $_GET['faciliteiten'] ?? [],
    'ligging' => $_GET['ligging'] ?? [],
    'min_area' => $_GET['min_area'] ?? 10, // Default min area
    'max_area' => $_GET['max_area'] ?? 1000, // Default max area
    'kamers' => $_GET['kamers'] ?? 0,
    'slaapkamers' => $_GET['slaapkamers'] ?? 0,
    'badkamers' => $_GET['badkamers'] ?? 0,
];

// --- Fetch Filtered Villas ---
$filterHandler = new Filter($filters);
$properties = $filterHandler->getFilteredVillas();

// --- Format Data for Display ---
$formattedProperties = [];
foreach ($properties as $row) {
    $formattedProperties[] = [
        'image' => !empty($row['image']) ? '../uploads/' . basename($row['image']) : '../../assets/img/default.png', // Adjust path and check existence
        'address' => $row['straat'] . ', ' . $row['post_c'],
        'price' => '€ ' . number_format($row['prijs'], 0, ',', '.'), // Format without decimals
        'tags' => !empty($row['tags']) ? explode(', ', $row['tags']) : [],
        'size' => $row['oppervlakte'] . 'm²'
    ];
}

// Define labels for the filter sections
$faciliteitenLabels = ['Zwembad', 'Winkels in de buurt', 'Entertainment in de buurt', 'Op een privépark'];
$liggingLabels = ['Bij het bos', 'Aan het water', 'Bij de stad', 'In het heuvelland'];

?>

<div class="container">
    <aside class="filters">
        <form id="filter-form" method="GET" action="woningen.php">
            <div class="search-bar">
                <input type="text" name="zoekterm" placeholder="zoeken" value="<?= htmlspecialchars($filters['zoekterm']) ?>">
                <button type="submit" class="search-button">Zoek</button>
            </div>
            <div class="filter-section">
                <h3>Filters</h3>
                <h4>Prijs (€)</h4>
                <div class="price-range">
                    <span id="price-display">€<?= number_format($filters['min_price']) ?> - €<?= number_format($filters['max_price']) ?></span>
                    <div class="slider-container">
                        <input type="range" id="price-slider-min" name="min_price" min="10000" max="10000000" step="1000" value="<?= htmlspecialchars($filters['min_price']) ?>">
                        <input type="range" id="price-slider-max" name="max_price" min="10000" max="10000000" step="1000" value="<?= htmlspecialchars($filters['max_price']) ?>">
                    </div>
                </div>
                <h4>Woning met:</h4>
                <ul>
                    <?php foreach ($faciliteitenLabels as $label): ?>
                        <li>
                            <label>
                                <input type="checkbox" name="faciliteiten[]" value="<?= $label ?>" <?= in_array($label, $filters['faciliteiten']) ? 'checked' : '' ?>> 
                                <?= $label ?>
                            </label>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <h4>Ligging</h4>
                <ul>
                    <?php foreach ($liggingLabels as $label): ?>
                        <li>
                            <label>
                                <input type="checkbox" name="ligging[]" value="<?= $label ?>" <?= in_array($label, $filters['ligging']) ? 'checked' : '' ?>> 
                                <?= $label ?>
                            </label>
                        </li>
                    <?php endforeach; ?>
                </ul>
                <div class="area-range">
                    <h4>m²</h4>
                    <span id="area-display"><?= htmlspecialchars($filters['min_area']) ?>m² - <?= htmlspecialchars($filters['max_area']) ?>m²</span>
                    <div class="slider-container">
                        <input type="range" id="area-slider-min" name="min_area" min="10" max="1000" step="10" value="<?= htmlspecialchars($filters['min_area']) ?>">
                        <input type="range" id="area-slider-max" name="max_area" min="10" max="1000" step="10" value="<?= htmlspecialchars($filters['max_area']) ?>">
                    </div>
                </div>
                <div class="properties">
                    <h4>Eigenschappen</h4>
                    <div class="rooms stepper">
                        Kamers: 
                        <button type="button" class="stepper-minus" data-target="kamers-input">-</button>
                        <input type="number" name="kamers" id="kamers-input" value="<?= htmlspecialchars($filters['kamers']) ?>" min="0" readonly>
                        <button type="button" class="stepper-plus" data-target="kamers-input">+</button>
                    </div>
                    <div class="bedrooms stepper">
                        Slaapkamers: 
                        <button type="button" class="stepper-minus" data-target="slaapkamers-input">-</button>
                        <input type="number" name="slaapkamers" id="slaapkamers-input" value="<?= htmlspecialchars($filters['slaapkamers']) ?>" min="0" readonly>
                        <button type="button" class="stepper-plus" data-target="slaapkamers-input">+</button>
                    </div>
                    <div class="bathrooms stepper">
                        Badkamers: 
                        <button type="button" class="stepper-minus" data-target="badkamers-input">-</button>
                        <input type="number" name="badkamers" id="badkamers-input" value="<?= htmlspecialchars($filters['badkamers']) ?>" min="0" readonly>
                        <button type="button" class="stepper-plus" data-target="badkamers-input">+</button>
                    </div>
                </div>
                <button type="submit" class="filter-submit-btn">Pas Filters Toe</button>
                <a href="woningen.php" class="filter-reset-btn">Reset Filters</a>
            </div>
        </form>
    </aside>

    <main class="properties-grid">
        <?php if (!empty($formattedProperties)): ?>
            <?php foreach ($formattedProperties as $property): ?>
                <div class="property-card">
                    <img src="<?= htmlspecialchars($property['image']); ?>" alt="Property Image">
                    <h3><?= htmlspecialchars($property['address']); ?></h3>
                    <p class="price"><?= $property['price']; ?></p>
                    <div class="tags">
                        <?php foreach ($property['tags'] as $tag): ?>
                            <span class="tag"><?= htmlspecialchars($tag); ?></span>
                        <?php endforeach; ?>
                    </div>
                    <p class="size"><?= $property['size']; ?></p>
                    <a href="#" class="view-more">Bekijk meer →</a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-results">Geen woningen gevonden die aan uw criteria voldoen.</p>
        <?php endif; ?>

        <?php if (!empty($formattedProperties)): ?>
            <div class="end-list">einde lijst</div>
        <?php endif; ?>
    </main>
</div>

<?php include_once '../includes/footer.php'; ?>
</body>
</html>
