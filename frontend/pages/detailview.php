<?php
require_once __DIR__ . '/../../db/class/database.php';

// Get Villa ID from URL
$villaId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($villaId <= 0) {
    // Redirect or show error if ID is invalid
    header("Location: ../woningen.php"); // Redirect back to the listings
    exit;
}

$db = new Database();
$conn = $db->getConnection();
$villaDetails = null;
$villaImages = [];
$featureOptions = [];
$locationOptions = [];

if ($conn) {
    // Fetch villa details using a prepared statement
    $stmt = $conn->prepare("SELECT * FROM villas WHERE id = :id");
    $stmt->bindParam(':id', $villaId, PDO::PARAM_INT);
    $stmt->execute();
    $villaDetails = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($villaDetails) {
        // Fetch villa images
        $imgStmt = $conn->prepare("SELECT image_path, is_hoofdfoto, is_main FROM villa_images WHERE villa_id = :id ORDER BY is_hoofdfoto DESC, is_main DESC, id ASC");
        $imgStmt->bindParam(':id', $villaId, PDO::PARAM_INT);
        $imgStmt->execute();
        $villaImages = $imgStmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch associated feature options (Eigenschappen)
        $featStmt = $conn->prepare("
            SELECT fo.name 
            FROM feature_options fo
            JOIN villa_feature_options vfo ON fo.id = vfo.option_id
            WHERE vfo.villa_id = :id
            ORDER BY fo.name
        ");
        $featStmt->bindParam(':id', $villaId, PDO::PARAM_INT);
        $featStmt->execute();
        $featureOptions = $featStmt->fetchAll(PDO::FETCH_COLUMN, 0); // Fetch just the names

        // Fetch associated location options (Ligging)
        $locStmt = $conn->prepare("
            SELECT lo.name 
            FROM location_options lo
            JOIN villa_location_options vlo ON lo.id = vlo.option_id
            WHERE vlo.villa_id = :id
            ORDER BY lo.name
        ");
        $locStmt->bindParam(':id', $villaId, PDO::PARAM_INT);
        $locStmt->execute();
        $locationOptions = $locStmt->fetchAll(PDO::FETCH_COLUMN, 0); // Fetch just the names

    } else {
        // Villa not found, redirect or show error
         header("Location: ../woningen.php?error=notfound"); 
         exit;
    }

    $db->closeConnection($conn);

} else {
    // Database connection error
    echo "Database connection could not be established.";
    exit; // Or handle more gracefully
}

// Combine address parts
$addressParts = array_filter([$villaDetails['straat'], $villaDetails['post_c'], $villaDetails['plaatsnaam']]);
$address = implode(', ', $addressParts);

// Handle form submission for information request (Ticket)
$formMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_inquiry'])) {
    // Get and validate form data
    $name = trim($_POST['gebruiker_naam'] ?? '');
    $email = filter_var(trim($_POST['email'] ?? ''), FILTER_VALIDATE_EMAIL);
    $phone = trim($_POST['telefoon'] ?? ''); // Optional
    $message = trim($_POST['bericht'] ?? '');
    $subject = "Informatieaanvraag Villa #" . $villaId . ": " . $villaDetails['titel'];

    if (!empty($name) && $email && !empty($message)) {
        $conn = $db->getConnection();
        if ($conn) {
            try {
                $ticketStmt = $conn->prepare("
                    INSERT INTO tickets (villa_id, onderwerp, bericht, gebruiker_naam, email, telefoon, status, prioriteit, datum_aangemaakt)
                    VALUES (:villa_id, :onderwerp, :bericht, :naam, :email, :telefoon, 'open', 'gemiddeld', NOW())
                ");
                $ticketStmt->bindParam(':villa_id', $villaId, PDO::PARAM_INT);
                $ticketStmt->bindParam(':onderwerp', $subject);
                $ticketStmt->bindParam(':bericht', $message);
                $ticketStmt->bindParam(':naam', $name);
                $ticketStmt->bindParam(':email', $email);
                $ticketStmt->bindParam(':telefoon', $phone); // Bind optional phone
                
                if ($ticketStmt->execute()) {
                    // Also add the first message to ticket_messages
                    $ticket_id = $conn->lastInsertId();
                    $messageStmt = $conn->prepare("
                        INSERT INTO ticket_messages (ticket_id, sender_type, message, created_at)
                        VALUES (:ticket_id, 'customer', :message, NOW())
                    ");
                    $messageStmt->bindParam(':ticket_id', $ticket_id);
                    $messageStmt->bindParam(':message', $message);
                    $messageStmt->execute();
                    
                    $formMessage = "<p class='success-message'>Bedankt voor uw aanvraag! We nemen zo snel mogelijk contact met u op.</p>";
                } else {
                     $formMessage = "<p class='error-message'>Er is een fout opgetreden bij het versturen van uw aanvraag. Probeer het later opnieuw.</p>";
                }
            } catch (PDOException $e) {
                 error_log("Ticket submission error: " . $e->getMessage());
                 $formMessage = "<p class='error-message'>Databasefout bij het verwerken van uw aanvraag.</p>";
            } finally {
                $db->closeConnection($conn);
            }
        } else {
            $formMessage = "<p class='error-message'>Kan geen verbinding maken met de database voor uw aanvraag.</p>";
        }
    } else {
         $formMessage = "<p class='error-message'>Vul alstublieft uw naam, een geldig e-mailadres en een bericht in.</p>";
    }
}

?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Details: <?= htmlspecialchars($villaDetails['titel']) ?> - Vakantie Villas</title>
    <link rel="stylesheet" href="../styles/detailview.css">
    <link rel="stylesheet" href="../includes/header.css">
    <link rel="stylesheet" href="../includes/footer.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Add FontAwesome or similar for icons if needed -->
     <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

<?php include '../includes/header.php'; ?>

<main class="detail-container">
    <div class="detail-content">
        
        <section class="villa-gallery">
            <?php if (!empty($villaImages)): ?>
                <div class="main-image">
                    <?php 
                        // Find the main image or use the first one
                        $mainImgPath = '../assets/img/default-villa.jpg'; // Default
                        foreach ($villaImages as $img) {
                            if ($img['is_hoofdfoto'] == 1 || $img['is_main'] == 1) {
                                $mainImgPath = '../uploads/' . basename($img['image_path']); 
                                break;
                            } elseif (empty($mainImgPath) || $mainImgPath === '../assets/img/default-villa.jpg') {
                                // Use first image as fallback if no main image set yet
                                $mainImgPath = '../uploads/' . basename($img['image_path']);
                            }
                        }
                    ?>
                    <img src="<?= htmlspecialchars($mainImgPath) ?>" alt="Hoofdfoto van <?= htmlspecialchars($villaDetails['titel']) ?>" id="galleryMainImage">
                </div>
                <?php if (count($villaImages) > 1): ?>
                <div class="thumbnail-strip">
                    <?php foreach ($villaImages as $img): ?>
                        <?php $thumbPath = '../uploads/' . basename($img['image_path']); ?>
                         <img src="<?= htmlspecialchars($thumbPath) ?>" 
                              alt="Thumbnail <?= htmlspecialchars($villaDetails['titel']) ?>" 
                              class="thumbnail <?= ($thumbPath == $mainImgPath) ? 'active' : '' ?>"
                              onclick="changeMainImage('<?= htmlspecialchars($thumbPath) ?>', this)">
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            <?php else: ?>
                <div class="main-image">
                     <img src="../assets/img/default-villa.jpg" alt="Geen afbeelding beschikbaar">
                </div>
            <?php endif; ?>
        </section>

        <section class="villa-info">
            <h1><?= htmlspecialchars($villaDetails['titel']) ?></h1>
             <?php if($villaDetails['verkocht']): ?>
                <span class="status-badge sold">Verkocht</span>
            <?php else: ?>
                 <span class="status-badge available">Beschikbaar</span>
            <?php endif; ?>
            <p class="address"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($address) ?></p>
            <p class="price">€ <?= number_format($villaDetails['prijs'], 0, ',', '.') ?></p>
            
            <div class="key-features">
                 <span><i class="fas fa-door-open"></i> <?= htmlspecialchars($villaDetails['kamers']) ?> kamers</span>
                 <span><i class="fas fa-bed"></i> <?= htmlspecialchars($villaDetails['slaapkamers']) ?> slaapkamers</span>
                 <span><i class="fas fa-bath"></i> <?= htmlspecialchars($villaDetails['badkamers']) ?> badkamers</span>
                 <span><i class="fas fa-ruler-combined"></i> <?= htmlspecialchars($villaDetails['oppervlakte']) ?> m²</span>
            </div>

            <h2>Omschrijving</h2>
            <p class="description">
                <?= !empty($villaDetails['omschrijving']) ? nl2br(htmlspecialchars($villaDetails['omschrijving'])) : 'Geen omschrijving beschikbaar.' ?>
            </p>

            <div class="options-lists">
                <div class="options-list">
                    <h2><i class="fas fa-star"></i> Eigenschappen</h2>
                    <?php if (!empty($featureOptions)): ?>
                        <ul>
                            <?php foreach ($featureOptions as $option): ?>
                                <li><?= htmlspecialchars($option) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>Geen specifieke eigenschappen opgegeven.</p>
                    <?php endif; ?>
                </div>
                 <div class="options-list">
                    <h2><i class="fas fa-compass"></i> Ligging</h2>
                     <?php if (!empty($locationOptions)): ?>
                        <ul>
                            <?php foreach ($locationOptions as $option): ?>
                                <li><?= htmlspecialchars($option) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    <?php else: ?>
                        <p>Geen specifieke liggingsopties opgegeven.</p>
                    <?php endif; ?>
                </div>
            </div>
             <div class="actions">
                <a href="generate_flyer.php?id=<?= $villaId ?>" class="btn btn-primary" target="_blank"><i class="fas fa-file-pdf"></i> Download Flyer</a>
                <button onclick="scrollToForm()" class="btn btn-secondary"><i class="fas fa-envelope"></i> Meer Informatie Aanvragen</button>
            </div>
        </section>

        <section class="inquiry-form-section" id="inquiry-form">
            <h2>Meer Informatie Aanvragen</h2>
            <?= $formMessage ?> <!-- Display success/error messages here -->
             <form action="detailview.php?id=<?= $villaId ?>#inquiry-form" method="POST">
                <input type="hidden" name="villa_id" value="<?= $villaId ?>">
                <div class="form-group">
                    <label for="gebruiker_naam">Naam *</label>
                    <input type="text" id="gebruiker_naam" name="gebruiker_naam" required>
                </div>
                <div class="form-group">
                    <label for="email">E-mailadres *</label>
                    <input type="email" id="email" name="email" required>
                </div>
                 <div class="form-group">
                    <label for="telefoon">Telefoonnummer (optioneel)</label>
                    <input type="tel" id="telefoon" name="telefoon">
                </div>
                <div class="form-group">
                    <label for="bericht">Bericht *</label>
                    <textarea id="bericht" name="bericht" rows="5" required></textarea>
                </div>
                <button type="submit" name="submit_inquiry" class="btn btn-submit">Verstuur Aanvraag</button>
            </form>
        </section>
    </div>

</main>

<?php include '../includes/footer.php'; ?>

<script>
    function changeMainImage(newSrc, thumbnailElement) {
        document.getElementById('galleryMainImage').src = newSrc;
        // Update active thumbnail
        document.querySelectorAll('.thumbnail-strip img').forEach(img => img.classList.remove('active'));
        thumbnailElement.classList.add('active');
    }

    function scrollToForm() {
        const formElement = document.getElementById('inquiry-form');
        if (formElement) {
            formElement.scrollIntoView({ behavior: 'smooth' });
        }
    }
</script>

</body>
</html> 