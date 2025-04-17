<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vakantie Villas - Woningen</title>
    <link rel="stylesheet" href="../styles/woningen.css">
    <link rel="stylesheet" href="../includes/header.css">
    <link rel="stylesheet" href="../includes/footer.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="../script/script.js" defer></script>
    <script src="../scripts/responsive.js" defer></script>
</head>

<body>
    
<div class="header-container">
   <?php include '../includes/header.php'; ?>
</div>

<?php
require_once __DIR__ . '/../../db/class/database.php';
require_once __DIR__ . '/../../db/class/filter.php';

// --- Initialize Filter Values ---
$filters = [
    'zoekterm' => isset($_GET['zoekterm']) ? trim($_GET['zoekterm']) : '',
    'min_price' => isset($_GET['min_price']) ? (int)$_GET['min_price'] : 0,
    'max_price' => isset($_GET['max_price']) ? (int)$_GET['max_price'] : 5000000, // Set to max by default
    'eigenschappen' => $_GET['eigenschappen'] ?? [],
    'ligging' => $_GET['ligging'] ?? [],
    'min_area' => isset($_GET['min_area']) ? (int)$_GET['min_area'] : 0,
    'max_area' => isset($_GET['max_area']) ? (int)$_GET['max_area'] : 2000, // Increased from 1000 to 2000
    'kamers' => isset($_GET['kamers']) ? (int)$_GET['kamers'] : 0,
    'slaapkamers' => isset($_GET['slaapkamers']) ? (int)$_GET['slaapkamers'] : 0,
    'badkamers' => isset($_GET['badkamers']) ? (int)$_GET['badkamers'] : 0,
];

// Debug search parameters
if (!empty($filters['zoekterm'])) {
    error_log("woningen.php: Search term: " . $filters['zoekterm']);
    // Add extra debug output to confirm it's being passed to the filter correctly
    error_log("woningen.php: Filter array: " . print_r($filters, true));
}

// --- Fetch Filter Options ---
$filterHandler = new Filter($filters);
$featureOptions = $filterHandler->getFeatureOptions();
$locationOptions = $filterHandler->getLocationOptions();

// --- Fetch Filtered Villas ---
$properties = $filterHandler->getFilteredVillas();

// Only use direct query if absolutely no filters are applied
if (empty($filters['zoekterm']) && 
    $filters['min_price'] <= 0 && 
    $filters['max_price'] >= 5000000 && 
    empty($filters['eigenschappen']) && 
    empty($filters['ligging']) && 
    $filters['min_area'] <= 0 && 
    $filters['max_area'] >= 1000 && 
    $filters['kamers'] <= 0 && 
    $filters['slaapkamers'] <= 0 && 
    $filters['badkamers'] <= 0) {
    
    // Create database connection
    $db = new Database();
    $conn = $db->getConnection();
    
    // Fetch all villas with their images, features and locations
    $query = "SELECT v.*, 
              (SELECT vi.image_path 
               FROM villa_images vi 
               WHERE vi.villa_id = v.id 
               LIMIT 1) as image,
              (SELECT GROUP_CONCAT(fo.name SEPARATOR ', ') 
               FROM villa_feature_options vfo
               JOIN feature_options fo ON vfo.option_id = fo.id
               WHERE vfo.villa_id = v.id) as features,
              (SELECT GROUP_CONCAT(lo.name SEPARATOR ', ') 
               FROM villa_location_options vlo
               JOIN location_options lo ON vlo.option_id = lo.id
               WHERE vlo.villa_id = v.id) as locations
              FROM villas v 
              GROUP BY v.id";
              
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $properties = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Add after database connection and before formatting properties
$itemsPerPage = 6;
$currentPage = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$totalItems = count($properties);
$totalPages = $totalItems > 0 ? ceil($totalItems / $itemsPerPage) : 1;
$offset = ($currentPage - 1) * $itemsPerPage;

// Slice the properties array for pagination
$paginatedProperties = array_slice($properties, $offset, $itemsPerPage);

// --- Format Data for Display ---
$formattedProperties = [];
foreach ($paginatedProperties as $row) {
    // Combine address parts correctly
    $addressParts = array_filter([$row['straat'], $row['post_c'], $row['plaatsnaam']]);
    $address = implode(', ', $addressParts);

    // Determine image path (handle potential relative paths from DB)
    $imagePath = '../assets/img/default-villa.jpg'; // Default image
    if (!empty($row['image'])) {
        // Just store the original path from the database
        // The actual path transformation will happen when rendering
        $imagePath = $row['image']; 
    }

    $formattedProperties[] = [
        'id' => $row['id'],
        'image' => $imagePath,
        'title' => htmlspecialchars($row['titel']),
        'address' => htmlspecialchars($address),
        'price' => '€ ' . number_format($row['prijs'], 0, ',', '.'),
        'features' => !empty($row['features']) ? explode(', ', $row['features']) : [],
        'locations' => !empty($row['locations']) ? explode(', ', $row['locations']) : [],
        'size' => htmlspecialchars($row['oppervlakte']) . 'm²',
        'rooms' => $row['kamers'],
        'bedrooms' => $row['slaapkamers'],
        'bathrooms' => $row['badkamers']
    ];
}

// Build query string for pagination links, preserving filters
$queryParams = $_GET;
if (isset($queryParams['page'])) {
    unset($queryParams['page']); // Remove existing page parameter
}
$queryString = http_build_query($queryParams);
$queryString = preg_replace('/%5B\d+%5D/', '%5B%5D', $queryString);

// Add a proper separator for the URL
$separator = !empty($queryString) ? '&' : '';

?>

<div class="container">
    <aside class="filters">
        <h3>Filters</h3>
        <form id="filter-form" method="GET" action="woningen.php">
            <div class="search-bar">
                <input type="text" name="zoekterm" placeholder="Zoek op titel, adres, postcode..." value="<?= htmlspecialchars($filters['zoekterm']) ?>">
                <button type="submit" class="search-button">Zoek</button>
            </div>
            
            <div class="filter-section">
                <h4>Prijs (€)</h4>
                <div class="price-range">
                    <span id="price-display">
                    </span>
                    <div class="slider-container">
                        <label for="price-slider-min" class="sr-only">Min Prijs</label>
                        <input type="range" id="price-slider-min" name="min_price" min="0" max="5000000" step="10000" value="<?= htmlspecialchars($filters['min_price'] ?: 0) ?>">
                        <label for="price-slider-max" class="sr-only">Max Prijs</label>
                        <input type="range" id="price-slider-max" name="max_price" min="0" max="5000000" step="10000" value="<?= htmlspecialchars($filters['max_price'] ?: 5000000) ?>">
                    </div>
                </div>
                
                <h4>Eigenschappen</h4>
                <ul>
                    <?php foreach ($featureOptions as $option): ?>
                        <li>
                            <label>
                                <input type="checkbox" name="eigenschappen[]" value="<?= htmlspecialchars($option['name']) ?>" <?= in_array($option['name'], $filters['eigenschappen']) ? 'checked' : '' ?>> 
                                <?= htmlspecialchars($option['name']) ?>
                            </label>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <h4>Ligging</h4>
                <ul>
                     <?php foreach ($locationOptions as $option): ?>
                        <li>
                            <label>
                                <input type="checkbox" name="ligging[]" value="<?= htmlspecialchars($option['name']) ?>" <?= in_array($option['name'], $filters['ligging']) ? 'checked' : '' ?>> 
                                <?= htmlspecialchars($option['name']) ?>
                            </label>
                        </li>
                    <?php endforeach; ?>
                </ul>

                <div class="area-range">
                    <h4>Oppervlakte (m²)</h4>
                     <span id="area-display">
                     </span>
                     <div class="slider-container">
                         <label for="area-slider-min" class="sr-only">Min Oppervlakte</label>
                         <input type="range" id="area-slider-min" name="min_area" min="0" max="2000" step="10" value="<?= htmlspecialchars($filters['min_area'] ?: 0) ?>">
                         <label for="area-slider-max" class="sr-only">Max Oppervlakte</label>
                         <input type="range" id="area-slider-max" name="max_area" min="0" max="2000" step="10" value="<?= htmlspecialchars($filters['max_area'] ?: 2000) ?>">
                    </div>
                </div>

                <div class="properties">
                    <h4>Aantal</h4>
                    <div class="stepper">
                        <label for="kamers-input">Kamers:</label> 
                        <button type="button" class="stepper-minus" data-target="kamers-input" aria-label="Minder kamers">-</button>
                        <input type="number" name="kamers" id="kamers-input" value="<?= htmlspecialchars($filters['kamers']) ?>" min="0" readonly>
                        <button type="button" class="stepper-plus" data-target="kamers-input" aria-label="Meer kamers">+</button>
                    </div>
                    <div class="stepper">
                         <label for="slaapkamers-input">Slaapkamers:</label> 
                         <button type="button" class="stepper-minus" data-target="slaapkamers-input" aria-label="Minder slaapkamers">-</button>
                         <input type="number" name="slaapkamers" id="slaapkamers-input" value="<?= htmlspecialchars($filters['slaapkamers']) ?>" min="0" readonly>
                         <button type="button" class="stepper-plus" data-target="slaapkamers-input" aria-label="Meer slaapkamers">+</button>
                    </div>
                    <div class="stepper">
                        <label for="badkamers-input">Badkamers:</label> 
                        <button type="button" class="stepper-minus" data-target="badkamers-input" aria-label="Minder badkamers">-</button>
                        <input type="number" name="badkamers" id="badkamers-input" value="<?= htmlspecialchars($filters['badkamers']) ?>" min="0" readonly>
                        <button type="button" class="stepper-plus" data-target="badkamers-input" aria-label="Meer badkamers">+</button>
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
                    <a href="detailview.php?id=<?= $property['id'] ?>" class="card-link-wrapper">
                        <?php 
                        // Prepare the image path correctly
                        $displayImagePath = $property['image'];
                        if (!empty($displayImagePath) && strpos($displayImagePath, '../') === false) {
                            // If the path doesn't already have a proper prefix, add it
                            $displayImagePath = '../uploads/' . basename($displayImagePath);
                        }
                        ?>
                        <img src="<?= htmlspecialchars($displayImagePath); ?>" alt="<?= $property['title']; ?>">
                        <div class="card-content">
                            <h3><?= $property['title']; ?></h3>
                            <p class="address"><?= $property['address']; ?></p>
                            <p class="price"><?= $property['price']; ?></p>
                            <div class="features-summary">
                                <span><i class="icon-bed"></i> <?= $property['bedrooms'] ?> slpk</span>
                                <span><i class="icon-bath"></i> <?= $property['bathrooms'] ?> badk</span>
                                <span><i class="icon-area"></i> <?= $property['size']; ?></span>
                            </div>
                            <div class="tags">
                                <?php 
                                // Combine features and locations for tags, limit display if needed
                                $allTags = array_merge($property['features'], $property['locations']);
                                $tagsToShow = array_slice($allTags, 0, 3); // Show max 3 tags initially
                                ?>
                                <?php foreach ($tagsToShow as $tag): ?>
                                    <span class="tag"><?= htmlspecialchars($tag); ?></span>
                                <?php endforeach; ?>
                                <?php if (count($allTags) > 3): ?>
                                    <span class="tag more-tags">+<?= count($allTags) - 3 ?></span>
                                <?php endif; ?>
                            </div>
                            <span class="view-more">Bekijk details →</span>
                        </div>
                    </a>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p class="no-results">Geen woningen gevonden die aan uw criteria voldoen. Probeer uw filters aan te passen.</p>
        <?php endif; ?>

        <?php if ($totalPages > 1): ?>
            <div class="pagination-container">
                <div class="pagination-info">
                    Pagina <?= $currentPage ?> van <?= $totalPages ?> (<?= $totalItems ?> resultaten)
                </div>
                 <div class="pagination-arrows">
                    <?php if ($currentPage > 1): ?>
                        <a href="?<?= $queryString . $separator ?>page=<?= $currentPage - 1 ?>" class="pagination-arrow prev" aria-label="Vorige pagina">← Vorige</a>
                    <?php else: ?>
                        <span class="pagination-arrow disabled prev">← Vorige</span>
                    <?php endif; ?>
                    
                    <?php if ($currentPage < $totalPages): ?>
                        <a href="?<?= $queryString . $separator ?>page=<?= $currentPage + 1 ?>" class="pagination-arrow next" aria-label="Volgende pagina">Volgende →</a>
                    <?php else: ?>
                        <span class="pagination-arrow disabled next">Volgende →</span>
                    <?php endif; ?>
                 </div>
            </div>
        <?php elseif (!empty($formattedProperties)): ?>
             <div class="end-list">einde lijst</div>
        <?php endif; ?>
    </main>
</div>
<?php include '../includes/footer.php'; ?>

<!-- JavaScript for functionality -->
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Filter toggle
        const filterButton = document.getElementById('filter-toggle');
        const filterOptions = document.querySelector('.filter-options');
        
        // Initialize - hide filter options by default
        filterOptions.style.display = 'none';
        
        // Update button text based on current state
        function updateButtonText() {
            if (filterOptions.style.display === 'none') {
                filterButton.textContent = '⚙️ Filter opties ▲';
            } else {
                filterButton.textContent = '⚙️ Filter opties ▼';
            }
        }
        
        // Set initial button text
        updateButtonText();
        
        // Add click event listener
        filterButton.addEventListener('click', function() {
            if (filterOptions.style.display === 'none') {
                filterOptions.style.display = 'block';
            } else {
                filterOptions.style.display = 'none';
            }
            updateButtonText();
        });
        
        // Counter buttons for Rooms, Bedrooms, Bathrooms
        setupCounter('kamers');
        setupCounter('slaapkamers');
        setupCounter('badkamers');
        
        function setupCounter(id) {
            const minusBtn = document.getElementById(`${id}-minus`);
            const plusBtn = document.getElementById(`${id}-plus`);
            const input = document.getElementById(id);
            
            minusBtn.addEventListener('click', function() {
                let value = parseInt(input.value);
                if (value > 0) {
                    input.value = value - 1;
                }
            });
            
            plusBtn.addEventListener('click', function() {
                let value = parseInt(input.value);
                input.value = value + 1;
            });
        }
    });
</script>

</body>
</html>
