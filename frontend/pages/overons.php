<!DOCTYPE html>
<html lang="nl">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vakantie Villas - IJsland</title>
    <link rel="stylesheet" href="../styles/overons.css">
    <link rel="stylesheet" href="../includes/header.css">
    <link rel="stylesheet" href="../includes/footer.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
    <?php include '../includes/header.php'; ?>
    
    <section class="hero-content-wrapper">
        <div class="hero-content">
            <h1>Wij zijn <br><span class="highlight">Vakantie</span><br><span class="highlight">Villas</span></h1>
            <p>Dé specialist in luxe vakantiewoningen. Vind jouw droomverblijf in IJsland en ervaar de magie van dit unieke land!</p>
        </div>
        <div class="hero-image">
            <img src="../../assets/img/ijsland-header.png" alt="Winter scene in Iceland">
        </div>
    </section>

    <!-- Nature Section -->
    <section class="nature">
        <div class="container nature-container">
            <div class="nature-content">
                <h2>Ontdek de prachtige natuur van IJsland!</h2>
                <p>Van uitgestrekte lavavelden en kristalheldere meren tot het magische noorderlicht – beleef IJsland vanuit jouw eigen vakantiewoning!</p>
                <a href="#" class="cta-button">Vind jouw droomwoning</a>
            </div>
            <div class="nature-images">
                <img src="../../assets/img/ijsland-view1.png" alt="Iceland landscape with water and flowers">
                <img src="../../assets/img/ijsland-view2.png" alt="Northern lights over mountain in Iceland">
                <img src="../../assets/img/ijsland-view3.png" alt="Northern lights reflected in water in Iceland">
            </div>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team">
        <div class="container">
            <h2>Wij zijn "IJsland"</h2>

            <div class="team-background" id="team-background">
                <div class="scrolling-name" id="scrolling-name"></div>

                <div class="team-member-featured">
                    <img id="featured-member-img" src="../../assets/img/Jan-Willem.png" alt="Team member">
                    <div class="member-info">
                        <div class="member-label">
                            <h3 id="member-name">Jan-Willem</h3>
                            <p id="member-title">Marketingmanager</p>
                        </div>
                    </div>
                </div>

                <div class="team-cursor" id="team-cursor">
                    <img src="../../assets/img/arrow.png" alt="Cursor">
                </div>

                <div class="team-thumbnails">
                    <img src="../../assets/img/Jan-Willem.png" alt="Team member thumbnail" data-index="0" class="active">
                    <img src="../../assets/img/Peter.png" alt="Team member thumbnail" data-index="1">
                    <img src="../../assets/img/King-Henry.png" alt="Team member thumbnail" data-index="2">
                    <img src="../../assets/img/Hendrick.png" alt="Team member thumbnail" data-index="3">
                    <img src="../../assets/img/Nigerian Prince Scammer.png" alt="Team member thumbnail" data-index="4">
                    <img src="../../assets/img/Einstein.png" alt="Team member thumbnail" data-index="5">
                </div>
            </div>
        </div>
    </section>

    <!-- Office Section -->
    <section class="office">
        <div class="floating-squares">
            <div class="square square-1"></div>
            <div class="square square-2"></div>
            <div class="square square-3"></div>
            <div class="square square-4"></div>
            <div class="square square-5"></div>
            <div class="square square-6"></div>
        </div>

        <div class="container">
            <div class="office-card">
                <div class="office-content">
                    <h2>Ons kantoor in IJsland</h2>
                    <p>Ons team staat klaar om jou te begeleiden bij het vinden van de perfecte vakantiewoning. Bezoek ons kantoor of neem contact op voor vrijblijvend advies!</p>
                </div>
                <div class="office-image">
                    <img src="../../assets/img/kantoor.png" alt="Office in Iceland">
                </div>
            </div>
        </div>
    </section>
    
    <?php include '../includes/footer.php'; ?>

    <script src="../script/overons.js"></script>
</body>

</html>