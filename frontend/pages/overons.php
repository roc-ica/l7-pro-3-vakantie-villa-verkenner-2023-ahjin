<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vakantie Villas - IJsland</title>
    <link rel="stylesheet" href="../styles/overons.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Hero Section with Header -->
    <section class="hero">
        <div class="navbar-container">
            <div class="navbar">
                <div class="logo">
                    <a href="#"><img src="../../assets/img/logo.png" alt="Vakantie Villas Logo"></a>
                </div>
                <nav>
                    <ul class="nav-links">
                        <li><a href="#">Woningen</a></li>
                        <li><a href="#">Ons</a></li>
                        <li><a href="#">Contact</a></li>
                        <li><a href="#" class="register-btn">Register</a></li>
                        <li><a href="#" class="login-btn">Login</a></li>
                    </ul>
                </nav>
            </div>
        </div>

        <div class="hero-content-wrapper">
            <div class="hero-content">
                <h1>Wij zijn <br><span class="highlight">Vakantie</span><br>Villas</h1>
                <p>Dé specialist in luxe vakantiewoningen. Vind jouw droomverblijf in IJsland en ervaar de magie van dit unieke land!</p>
            </div>
            <div class="hero-image">
                <img src="../../assets/img/ijsland-header.png" alt="Winter scene in Iceland">
            </div>
        </div>
    </section>

    <!-- Nature Section -->
    <section class="nature">
        <div class="container">
            <h2>Ontdek de prachtige natuur van IJsland!</h2>
            <p>Van adembenemende watervallen tot mystieke noorderlicht en vulkanische landschappen - IJsland biedt unieke ervaringen voor ieder seizoen!</p>
            
            <div class="nature-gallery">
                <div class="gallery-nav prev">
                    <span>&lt;</span>
                </div>
                <div class="gallery-images">
                    <img src="images/nature-1.jpg" alt="Iceland landscape with water">
                    <img src="images/nature-2.jpg" alt="Northern lights in Iceland">
                    <img src="images/nature-3.jpg" alt="Green landscape in Iceland">
                </div>
                <div class="gallery-nav next">
                    <span>&gt;</span>
                </div>
            </div>
            
            <a href="#" class="cta-button">Vind jouw droomwoning</a>
        </div>
    </section>

    <!-- Team Section -->
    <section class="team">
        <div class="container">
            <h2>Wij zijn "IJsland"</h2>
            
            <div class="team-background">
                <div class="team-member-featured">
                    <img src="images/team-member.jpg" alt="Team member">
                    <div class="member-info">
                        <h3>Jan-Willem</h3>
                        <p>Eigenaar/Verhuurmakelaar</p>
                    </div>
                </div>
                
                <div class="team-cursor">
                    <img src="images/cursor.png" alt="Cursor">
                </div>
                
                <div class="team-thumbnails">
                    <img src="images/team-thumb-1.jpg" alt="Team member thumbnail">
                    <img src="images/team-thumb-2.jpg" alt="Team member thumbnail">
                    <img src="images/team-thumb-3.jpg" alt="Team member thumbnail">
                    <img src="images/team-thumb-4.jpg" alt="Team member thumbnail">
                    <img src="images/team-thumb-5.jpg" alt="Team member thumbnail">
                    <img src="images/team-thumb-6.jpg" alt="Team member thumbnail">
                </div>
            </div>
        </div>
    </section>

    <!-- Office Section -->
    <section class="office">
        <div class="container">
            <div class="office-content">
                <h2>Ons kantoor in IJsland</h2>
                <p>Gelegen in het centrum van Reykjavik, is ons kantoor de perfecte uitvalsbasis voor het verkennen van de mooiste vakantiewoningen in IJsland. Kom langs en ontdek wat wij voor u kunnen betekenen.</p>
            </div>
            <div class="office-image">
                <img src="images/office.jpg" alt="Office in Iceland">
            </div>
            
            <div class="geometric-shapes">
                <div class="shape shape-1"></div>
                <div class="shape shape-2"></div>
                <div class="shape shape-3"></div>
                <div class="shape shape-4"></div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer>
        <div class="container footer-container">
            <div class="footer-column">
                <h3>Contact</h3>
                <ul>
                    <li><a href="mailto:contact@vakantievillas.com">contact@vakantievillas.com</a></li>
                    <li>+31 6 1234 5678</li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3>Menu</h3>
                <ul>
                    <li><a href="#">Home</a></li>
                    <li><a href="#">Woningen</a></li>
                    <li><a href="#">Ons</a></li>
                    <li><a href="#">Contact</a></li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3>Support</h3>
                <ul>
                    <li><a href="mailto:support@vakantievillas.com">support@vakantievillas.com</a></li>
                    <li>+31 6 9876 5432</li>
                </ul>
            </div>
            
            <div class="footer-column">
                <h3>Adres</h3>
                <ul>
                    <li>Laugavegur 12</li>
                    <li>101 Reykjavík</li>
                    <li>IJsland</li>
                </ul>
            </div>
            
            <div class="footer-glow"></div>
        </div>
    </footer>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
    // Gallery navigation
    const prevBtn = document.querySelector('.gallery-nav.prev');
    const nextBtn = document.querySelector('.gallery-nav.next');
    const galleryImages = document.querySelector('.gallery-images');
    
    let currentPosition = 0;
    
    if (prevBtn && nextBtn && galleryImages) {
        nextBtn.addEventListener('click', function() {
            if (currentPosition > -200) {
                currentPosition -= 100;
                galleryImages.style.transform = `translateX(${currentPosition}px)`;
            }
        });
        
        prevBtn.addEventListener('click', function() {
            if (currentPosition < 0) {
                currentPosition += 100;
                galleryImages.style.transform = `translateX(${currentPosition}px)`;
            }
        });
    }
    
    // Team member selection
    const teamThumbnails = document.querySelectorAll('.team-thumbnails img');
    const featuredMember = document.querySelector('.team-member-featured img');
    const memberName = document.querySelector('.member-info h3');
    const memberTitle = document.querySelector('.member-info p');
    
    if (teamThumbnails.length && featuredMember && memberName && memberTitle) {
        const teamMembers = [
            { name: 'Jan-Willem', title: 'Eigenaar/Verhuurmakelaar' },
            { name: 'Lisa', title: 'Marketing Manager' },
            { name: 'Erik', title: 'Vastgoedadviseur' },
            { name: 'Sophie', title: 'Klantenservice' },
            { name: 'Thomas', title: 'Fotograaf' },
            { name: 'Anna', title: 'Locatiemanager' }
        ];
        
        teamThumbnails.forEach((thumbnail, index) => {
            if (index < teamMembers.length) {
                thumbnail.addEventListener('click', function() {
                    featuredMember.src = this.src;
                    memberName.textContent = teamMembers[index].name;
                    memberTitle.textContent = teamMembers[index].title;
                });
            }
        });
    }
});
    </script>
</body>
</html>