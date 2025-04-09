<?php
// Include the database class
require_once __DIR__ . '../../../db/class/database.php';

// Create a database instance
$db = new Database();
$conn = $db->getConnection();

// Array to store featured villas
$featuredVillas = [];

// Check if connection is successful
if ($conn) {
    try {
        // Prepare and execute query to get featured villas
        $stmt = $conn->prepare("
            SELECT v.*, 
                   (SELECT vi.image_path FROM villa_images vi WHERE vi.villa_id = v.id LIMIT 1) as image_path 
            FROM villas v 
            WHERE v.featured = 1
            ORDER BY v.id DESC 
            LIMIT 3
        ");
        $stmt->execute();
        
        // Fetch all villas
        $featuredVillas = $stmt->fetchAll();
        
        // If there are fewer than 3 featured villas, get the most recent ones to fill in
        if (count($featuredVillas) < 3) {
            $neededVillas = 3 - count($featuredVillas);
            
            // Get IDs of already fetched villas to exclude them
            $excludeIds = array_map(function($villa) {
                return $villa['id'];
            }, $featuredVillas);
            
            $placeholders = implode(',', array_fill(0, count($excludeIds), '?'));
            $excludeQuery = count($excludeIds) > 0 ? "AND v.id NOT IN ($placeholders)" : "";
            
            $stmt = $conn->prepare("
                SELECT v.*, 
                       (SELECT vi.image_path FROM villa_images vi WHERE vi.villa_id = v.id LIMIT 1) as image_path 
                FROM villas v 
                WHERE v.featured = 0 $excludeQuery
                ORDER BY v.id DESC 
                LIMIT $neededVillas
            ");
            
            if (count($excludeIds) > 0) {
                $stmt->execute($excludeIds);
            } else {
                $stmt->execute();
            }
            
            $additionalVillas = $stmt->fetchAll();
            $featuredVillas = array_merge($featuredVillas, $additionalVillas);
        }
        
    } catch (PDOException $e) {
        // Log error but don't display to users
        error_log("Error fetching villas: " . $e->getMessage());
    } finally {
        // Close the connection
        $db->closeConnection($conn);
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Villa Verkenner - Ontdek de mooiste vakantiewoningen in IJsland</title>
    <link rel="stylesheet" href="../styles/homepage.css">
    <link rel="stylesheet" href="../includes/header.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="../includes/script.js" defer></script>
</head>

<body>
    <?php include_once '../includes/header.php'; ?>

    <!-- Hero Banner Section -->
    <section class="hero-banner">
        <div class="overlay"></div>
        <div class="banner-content">
            <h1>Ontdek de schoonheid van IJsland</h1>
            <p>Luxe vakantievilla's te midden van adembenemende landschappen</p>
            <a href="woningen.php" class="cta-button">Bekijk alle villa's</a>
        </div>
    </section>

    <!-- Featured Villas Section -->
    <section class="featured-villas">
        <div class="section-title">
            <h2>Uitgelichte Villa's</h2>
            <p>Ontdek onze meest exclusieve vakantiewoningen</p>
        </div>
        
        <div class="villas-container">
            <?php if (count($featuredVillas) > 0): ?>
                <?php foreach ($featuredVillas as $villa): ?>
                    <div class="villa-card">
                        <div class="villa-image">
                            <?php if (!empty($villa['image_path'])): ?>
                                <img src="<?php echo htmlspecialchars($villa['image_path']); ?>" alt="<?php echo htmlspecialchars($villa['straat']); ?>">
                            <?php else: ?>
                                <img src="../../assets/img/placeholder-villa.jpg" alt="Villa afbeelding niet beschikbaar">
                            <?php endif; ?>
                            <div class="villa-price">€ <?php echo number_format($villa['prijs'], 0, ',', '.'); ?></div>
                        </div>
                        <div class="villa-info">
                            <h3><?php echo htmlspecialchars($villa['straat']); ?></h3>
                            <p>
                                <?php 
                                // Generate a description if none exists in the database
                                echo "Deze prachtige villa in " . htmlspecialchars($villa['post_c']) . 
                                     " biedt luxe en comfort in het hart van IJsland.";
                                ?>
                            </p>
                            <div class="villa-features">
                                <span><?php echo htmlspecialchars($villa['slaapkamers']); ?> slaapkamers</span>
                                <span><?php echo htmlspecialchars($villa['badkamers']); ?> badkamers</span>
                                <span><?php echo htmlspecialchars($villa['oppervlakte']); ?>m²</span>
                            </div>
                            <a href="woning-detail.php?id=<?php echo $villa['id']; ?>" class="more-info">Meer informatie</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <!-- Fallback content if no villas are found -->
                <div class="no-villas">
                    <p>Er zijn momenteel geen uitgelichte villa's beschikbaar. Bekijk onze <a href="woningen.php">volledige collectie</a>.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Rest of your HTML remains the same -->
    
    <!-- Night Scape Call to Action -->
    <section class="night-scape">
        <div class="overlay"></div>
        <div class="night-content">
            <h2>Ontdek Magische Momenten</h2>
            <p>Verken de vele luxe villa's die we u kunnen aanbieden in het land van vuur en ijs. 
            Geniet van het noorderlicht vanuit uw eigen jacuzzi of word wakker met uitzicht op gletsjers en vulkanen. 
            Laat ons u helpen de perfecte accommodatie te vinden voor uw IJslandse avontuur.</p>
            <a href="woningen.php" class="explore-button">Ontdekken</a>
        </div>
    </section>

    <!-- Reviews Section -->
    <section class="reviews-section">
        <div class="section-title">
            <h2>Wat Onze Klanten Zeggen</h2>
            <p>Ervaringen van tevreden villa-zoekers</p>
        </div>
        
        <div class="reviews-container">
            <div class="review-tabs">
                <button class="review-tab active" data-review="review1">Dof Jansen</button>
                <button class="review-tab" data-review="review2">Kaj Rover</button>
                <button class="review-tab" data-review="review3">Musie Mulugeta</button>
                <button class="review-tab" data-review="review4">Artem Kosikhin</button>
                <button class="review-tab" data-review="review5">Colin Poort</button>
            </div>
            
            <div class="review-content">
                <div class="review active" id="review1">
                    <div class="review-text">
                        <p>"Villa Verkenner heeft ons geholpen onze droomvilla in IJsland te vinden! De website is ongelooflijk gebruiksvriendelijk en de filterfuncties maakten het gemakkelijk om precies te vinden wat we zochten. Binnen een week na het vinden van de villa waren alle details geregeld. Zeer aan te bevelen!"</p>
                        <div class="reviewer">
                            <strong>Dof Jansen</strong> - Gekocht in Reykjavik, April 2023
                        </div>
                    </div>
                </div>
                
                <!-- Other reviews remain the same -->
                
            </div>
        </div>
    </section>

    <?php include_once '../includes/footer.php'; ?>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const reviewTabs = document.querySelectorAll('.review-tab');
            const reviews = document.querySelectorAll('.review');
            
            reviewTabs.forEach(tab => {
                tab.addEventListener('click', function() {
                    // Remove active class from all tabs and reviews
                    reviewTabs.forEach(t => t.classList.remove('active'));
                    reviews.forEach(r => r.classList.remove('active'));
                    
                    // Add active class to clicked tab
                    this.classList.add('active');
                    
                    // Show corresponding review
                    const reviewId = this.getAttribute('data-review');
                    document.getElementById(reviewId).classList.add('active');
                });
            });
        });
    </script>
</body>

</html>