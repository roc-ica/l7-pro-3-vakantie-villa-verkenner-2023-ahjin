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
            <div class="villa-card">
                <div class="villa-image">
                    <img src="../../assets/img/featured-villa-1.jpg" alt="Luxe villa aan het meer">
                    <div class="villa-price">€ 1.250.000</div>
                </div>
                <div class="villa-info">
                    <h3>Eldfjall Hús</h3>
                    <p>Deze moderne villa biedt een adembenemend uitzicht op de IJslandse bergen. Met 4 slaapkamers, een privé hot tub en grote ramen die de natuurlijke schoonheid naar binnen brengen.</p>
                    <div class="villa-features">
                        <span>4 slaapkamers</span>
                        <span>3 badkamers</span>
                        <span>250m²</span>
                    </div>
                    <a href="woningen.php" class="more-info">Meer informatie</a>
                </div>
            </div>
            
            <div class="villa-card">
                <div class="villa-image">
                    <img src="../../assets/img/featured-villa-2.jpg" alt="Moderne villa aan het strand">
                    <div class="villa-price">€ 875.000</div>
                </div>
                <div class="villa-info">
                    <h3>Jökull Vista</h3>
                    <p>Gelegen aan de spectaculaire zuidkust van IJsland, biedt deze villa directe toegang tot zwarte zandstranden en is perfect om het noorderlicht te spotten tijdens de wintermaanden.</p>
                    <div class="villa-features">
                        <span>3 slaapkamers</span>
                        <span>2 badkamers</span>
                        <span>180m²</span>
                    </div>
                    <a href="woningen.php" class="more-info">Meer informatie</a>
                </div>
            </div>
            
            <div class="villa-card">
                <div class="villa-image">
                    <img src="../../assets/img/featured-villa-3.jpg" alt="Traditionele IJslandse villa">
                    <div class="villa-price">€ 1.450.000</div>
                </div>
                <div class="villa-info">
                    <h3>Norðurljós Húsið</h3>
                    <p>Deze unieke villa combineert traditionele IJslandse architectuur met moderne luxe, gelegen in de beroemde Golden Circle regio met gemakkelijke toegang tot geisers en watervallen.</p>
                    <div class="villa-features">
                        <span>5 slaapkamers</span>
                        <span>4 badkamers</span>
                        <span>320m²</span>
                    </div>
                    <a href="woningen.php" class="more-info">Meer informatie</a>
                </div>
            </div>
        </div>
    </section>

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
                
                <div class="review" id="review2">
                    <div class="review-text">
                        <p>"Als iemand die vaak tijd doorbrengt in IJsland voor werk, was ik op zoek naar een tweede huis. Deze website biedt de meest complete collectie van kwaliteitsvilla's die ik heb gezien. De gedetailleerde beschrijvingen en virtual tours gaven me het vertrouwen om een aankoop te doen zonder het pand fysiek te bezoeken."</p>
                        <div class="reviewer">
                            <strong>Kaj Rover</strong> - Gekocht in Akureyri, Januari 2023
                        </div>
                    </div>
                </div>
                
                <div class="review" id="review3">
                    <div class="review-text">
                        <p>"Villa Verkenner is een geweldige oplossing voor het vinden van unieke woningen in IJsland. De klantenservice was uitzonderlijk toen ik vragen had over specifieke eigenschappen van de villa waarin ik geïnteresseerd was. Ze gingen echt de extra mijl om alle informatie te verzamelen die ik nodig had."</p>
                        <div class="reviewer">
                            <strong>Musie Mulugeta</strong> - Gekocht in Vik, Maart 2023
                        </div>
                    </div>
                </div>
                
                <div class="review" id="review4">
                    <div class="review-text">
                        <p>"Als architect waardeer ik de focus op details en de kwaliteit van de villa's die op deze website worden aangeboden. Het was een plezier om door de verschillende stijlen te bladeren en inspiratie op te doen. Uiteindelijk heb ik een prachtig modern huis gevonden dat perfect past bij mijn levensstijl."</p>
                        <div class="reviewer">
                            <strong>Artem Kosikhin</strong> - Gekocht in Selfoss, Februari 2023
                        </div>
                    </div>
                </div>
                
                <div class="review" id="review5">
                    <div class="review-text">
                        <p>"Wat deze website onderscheidt is de nauwkeurigheid van de informatie. Alles wat werd vermeld over onze villa was precies zoals beschreven. Geen verrassingen, geen teleurstellingen. Het proces van bezichtiging tot aankoop was naadloos en het team was altijd beschikbaar om te helpen. Een 5-sterren ervaring!"</p>
                        <div class="reviewer">
                            <strong>Colin Poort</strong> - Gekocht in Húsavík, December 2022
                        </div>
                    </div>
                </div>
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
