<?php
require_once __DIR__ . '/../../db/class/database.php';

// Get Villa ID from URL
$villaId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($villaId <= 0) {
    // Redirect if ID is invalid
    header("Location: ../woningen.php");
    exit;
}

// Create database connection and fetch villa data
$db = new Database();
$conn = $db->getConnection();
$villaDetails = null;
$villaImages = [];
$featureOptions = [];
$locationOptions = [];

if (!$conn) {
    die("Database connection failed");
}

try {
    // Fetch villa details
    $stmt = $conn->prepare("SELECT * FROM villas WHERE id = :id");
    $stmt->bindParam(':id', $villaId, PDO::PARAM_INT);
    $stmt->execute();
    $villaDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$villaDetails) {
        header("Location: ../woningen.php?error=notfound");
        exit;
    }

    // Fetch villa images
    $imgStmt = $conn->prepare("SELECT image_path, is_hoofdfoto, is_main FROM villa_images WHERE villa_id = :id ORDER BY is_hoofdfoto DESC, is_main DESC, id ASC");
    $imgStmt->bindParam(':id', $villaId, PDO::PARAM_INT);
    $imgStmt->execute();
    $villaImages = $imgStmt->fetchAll(PDO::FETCH_ASSOC);

    // Fetch features
    $featStmt = $conn->prepare("
        SELECT fo.name 
        FROM feature_options fo
        JOIN villa_feature_options vfo ON fo.id = vfo.option_id
        WHERE vfo.villa_id = :id
        ORDER BY fo.name
    ");
    $featStmt->bindParam(':id', $villaId, PDO::PARAM_INT);
    $featStmt->execute();
    $featureOptions = $featStmt->fetchAll(PDO::FETCH_COLUMN, 0);

    // Fetch locations
    $locStmt = $conn->prepare("
        SELECT lo.name 
        FROM location_options lo
        JOIN villa_location_options vlo ON lo.id = vlo.option_id
        WHERE vlo.villa_id = :id
        ORDER BY lo.name
    ");
    $locStmt->bindParam(':id', $villaId, PDO::PARAM_INT);
    $locStmt->execute();
    $locationOptions = $locStmt->fetchAll(PDO::FETCH_COLUMN, 0);

    // Generate HTML output instead for now
    // Format address
    $addressParts = array_filter([$villaDetails['straat'], $villaDetails['post_c'], $villaDetails['plaatsnaam']]);
    $address = implode(', ', $addressParts);

    // Find main image
    $mainImagePath = '../assets/img/default-villa.jpg';
    foreach ($villaImages as $img) {
        if ($img['is_hoofdfoto'] == 1 || $img['is_main'] == 1) {
            $mainImagePath = '../uploads/' . basename($img['image_path']); 
            break;
        } elseif (empty($mainImgPath) || $mainImgPath === '../assets/img/default-villa.jpg') {
            $mainImagePath = '../uploads/' . basename($img['image_path']);
        }
    }

    // Start output
    header('Content-Type: text/html; charset=utf-8');
    ?>
    <!DOCTYPE html>
    <html lang="nl">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Flyer: <?= htmlspecialchars($villaDetails['titel']) ?></title>
        <style>
            * {
                box-sizing: border-box;
                font-family: 'Arial', sans-serif;
            }
            body {
                padding: 20px;
                max-width: 800px;
                margin: 0 auto;
                background-color: #f5f5f5;
            }
            .flyer-container {
                background-color: white;
                padding: 20px;
                box-shadow: 0 0 10px rgba(0,0,0,0.1);
            }
            .header {
                display: flex;
                justify-content: space-between;
                align-items: center;
                margin-bottom: 20px;
                border-bottom: 2px solid #eee;
                padding-bottom: 10px;
            }
            .logo {
                font-size: 24px;
                font-weight: bold;
                color: #333;
            }
            .title {
                text-align: center;
                font-size: 22px;
                margin: 20px 0;
                color: #333;
            }
            .main-image {
                width: 100%;
                height: 300px;
                object-fit: cover;
                margin-bottom: 20px;
            }
            .info-section {
                margin-bottom: 20px;
            }
            .info-section h2 {
                color: #333;
                border-bottom: 1px solid #eee;
                padding-bottom: 5px;
            }
            .property-specs {
                display: flex;
                flex-wrap: wrap;
                margin-bottom: 15px;
            }
            .property-spec {
                width: 50%;
                padding: 5px 0;
            }
            .property-spec strong {
                margin-right: 5px;
            }
            .property-list ul {
                padding-left: 20px;
            }
            .contact-section {
                background-color: #f9f9f9;
                padding: 15px;
                border-radius: 5px;
                margin-top: 20px;
            }
            .thumbnail-section {
                display: flex;
                flex-wrap: wrap;
                gap: 10px;
                margin-top: 20px;
            }
            .thumbnail {
                width: calc(33.333% - 10px);
                height: 100px;
                object-fit: cover;
            }
            .price {
                font-size: 20px;
                color: #cc3300;
                margin: 15px 0;
            }
            .address {
                font-size: 16px;
                color: #666;
                margin-bottom: 20px;
            }
            .print-button {
                display: block;
                margin: 20px auto;
                padding: 10px 20px;
                background-color: #007bff;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
                font-size: 16px;
            }
            @media print {
                body {
                    background-color: white;
                    padding: 0;
                }
                .flyer-container {
                    box-shadow: none;
                }
                .print-button {
                    display: none;
                }
            }
        </style>
    </head>
    <body>
        <button class="print-button" onclick="window.print()">Flyer afdrukken</button>
        
        <div class="flyer-container">
            <div class="header">
                <div class="logo">Vakantie Villa Verkenner</div>
                <div>Uw specialist in vakantiewoningen</div>
            </div>
            
            <div class="title"><?= htmlspecialchars($villaDetails['titel']) ?></div>
            
            <img src="<?= htmlspecialchars($mainImagePath) ?>" alt="Hoofdfoto" class="main-image">
            
            <div class="address"><?= htmlspecialchars($address) ?></div>
            
            <div class="price">€ <?= number_format($villaDetails['prijs'], 0, ',', '.') ?></div>
            
            <div class="info-section">
                <h2>Specificaties</h2>
                <div class="property-specs">
                    <div class="property-spec"><strong>Kamers:</strong> <?= htmlspecialchars($villaDetails['kamers']) ?></div>
                    <div class="property-spec"><strong>Slaapkamers:</strong> <?= htmlspecialchars($villaDetails['slaapkamers']) ?></div>
                    <div class="property-spec"><strong>Badkamers:</strong> <?= htmlspecialchars($villaDetails['badkamers']) ?></div>
                    <div class="property-spec"><strong>Oppervlakte:</strong> <?= htmlspecialchars($villaDetails['oppervlakte']) ?> m²</div>
                </div>
            </div>
            
            <div class="info-section">
                <h2>Omschrijving</h2>
                <p><?= nl2br(htmlspecialchars($villaDetails['omschrijving'] ?: 'Geen omschrijving beschikbaar.')) ?></p>
            </div>
            
            <div class="info-section property-list">
                <h2>Eigenschappen</h2>
                <?php if (!empty($featureOptions)): ?>
                    <ul>
                        <?php foreach ($featureOptions as $feature): ?>
                            <li><?= htmlspecialchars($feature) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Geen specifieke eigenschappen opgegeven.</p>
                <?php endif; ?>
            </div>
            
            <div class="info-section property-list">
                <h2>Ligging</h2>
                <?php if (!empty($locationOptions)): ?>
                    <ul>
                        <?php foreach ($locationOptions as $location): ?>
                            <li><?= htmlspecialchars($location) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <p>Geen specifieke liggingsopties opgegeven.</p>
                <?php endif; ?>
            </div>
            
            <?php if (count($villaImages) > 1): ?>
                <div class="info-section">
                    <h2>Meer afbeeldingen</h2>
                    <div class="thumbnail-section">
                        <?php foreach (array_slice($villaImages, 0, 6) as $img): ?>
                            <?php $thumbPath = '../uploads/' . basename($img['image_path']); ?>
                            <img src="<?= htmlspecialchars($thumbPath) ?>" alt="Afbeelding" class="thumbnail">
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
            
            <div class="contact-section">
                <h2>Contact Vakantie Villa Verkenner</h2>
                <p><strong>Telefoonnummer:</strong> +31 (0)12 345 67 89</p>
                <p><strong>E-mail:</strong> info@vakantievillaverkenner.nl</p>
                <p><strong>Website:</strong> www.vakantievillaverkenner.nl</p>
            </div>
        </div>
        
        <script>
            // Automatically open print dialog when page loads
            window.addEventListener('load', function() {
                // Give a small delay to ensure everything is loaded
                setTimeout(function() {
                    // Uncomment this line to auto-print when opened
                    // window.print();
                }, 1000);
            });
        </script>
    </body>
    </html>
    <?php

} catch (Exception $e) {
    echo "<div style='padding: 20px; background-color: #f8d7da; border: 1px solid #f5c6cb; border-radius: 5px; margin: 20px;'>";
    echo "<h3 style='color: #721c24;'>Error generating flyer</h3>";
    echo "<p>".htmlspecialchars($e->getMessage())."</p>";
    echo "</div>";
} finally {
    $db->closeConnection($conn);
}
?> 