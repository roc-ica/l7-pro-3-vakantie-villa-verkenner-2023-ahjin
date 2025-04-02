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
            // Team members data
            const teamMembers = [{
                    name: 'Jan-Willem',
                    title: 'Marketingmanager',
                    image: '../../assets/img/Jan-Willem.png'
                },
                {
                    name: 'Peter',
                    title: 'Vastgoedadviseur',
                    image: '../../assets/img/Peter.png'
                },
                {
                    name: 'Henry',
                    title: 'Klantenservice',
                    image: '../../assets/img/King-Henry.png'
                },
                {
                    name: 'Hendrick',
                    title: 'Fotograaf',
                    image: '../../assets/img/Hendrick.png'
                },
                {
                    name: 'Patrick',
                    title: 'Nigerian Prince Scammer',
                    image: '../../assets/img/Nigerian Prince Scammer.png'
                },
                {
                    name: 'Einstein',
                    title: 'Eigenaar',
                    image: '../../assets/img/Einstein.png'
                }
            ];

            let currentMemberIndex = 0;

            // Elements
            const teamBackground = document.getElementById('team-background');
            const scrollingName = document.getElementById('scrolling-name');
            const featuredMemberImg = document.getElementById('featured-member-img');
            const memberName = document.getElementById('member-name');
            const memberTitle = document.getElementById('member-title');
            const teamCursor = document.getElementById('team-cursor');
            const thumbnails = document.querySelectorAll('.team-thumbnails img');

            // Initialize scrolling name
            updateScrollingName();

            // Custom cursor following mouse
            if (teamBackground && teamCursor) {
                let cursorDirection = 'right'; // default direction

                teamBackground.addEventListener('mousemove', function(e) {
                    const rect = teamBackground.getBoundingClientRect();
                    const x = e.clientX - rect.left;
                    const y = e.clientY - rect.top;

                    // Update cursor position
                    teamCursor.style.left = x + 'px';
                    teamCursor.style.top = y + 'px';

                    // Check if cursor is on left or right side of screen
                    const midpoint = rect.width / 2;
                    if (x < midpoint && cursorDirection === 'right') {
                        cursorDirection = 'left';
                        teamCursor.querySelector('img').style.transform = 'scaleX(-1)';
                    } else if (x >= midpoint && cursorDirection === 'left') {
                        cursorDirection = 'right';
                        teamCursor.querySelector('img').style.transform = 'scaleX(1)';
                    }
                });

                // Click to navigate
                teamBackground.addEventListener('click', function(e) {
                    if (cursorDirection === 'right') {
                        nextMember();
                    } else {
                        prevMember();
                    }
                });
            }

            // Thumbnail click handlers
            thumbnails.forEach(thumbnail => {
                thumbnail.addEventListener('click', function(e) {
                    e.stopPropagation(); // Prevent the background click from firing
                    const index = parseInt(this.getAttribute('data-index'));
                    updateMember(index);
                });
            });

            // Scrolling background text animation
            function updateScrollingName() {
                const currentName = teamMembers[currentMemberIndex].name;
                let repeatedName = '';

                // Repeat the name many more times to ensure continuous scrolling
                for (let i = 0; i < 50; i++) { // Increased from 20 to 50
                    repeatedName += currentName + ' ';
                }

                scrollingName.textContent = repeatedName;
                animateScrollingName();
            }

            function animateScrollingName() {
                let position = -400; // Start more to the left
                const scrollSpeed = 1;

                function step() {
                    position -= scrollSpeed;
                    scrollingName.style.transform = `translateY(-75%) translateX(${position}px)`;
                    requestAnimationFrame(step);
                }

                requestAnimationFrame(step);
            }
            // Navigation functions
            function nextMember() {
                let newIndex = currentMemberIndex + 1;
                if (newIndex >= teamMembers.length) {
                    newIndex = 0;
                }
                updateMember(newIndex);
            }

            function prevMember() {
                let newIndex = currentMemberIndex - 1;
                if (newIndex < 0) {
                    newIndex = teamMembers.length - 1;
                }
                updateMember(newIndex);
            }

            function updateMember(index) {
                // Update active thumbnail
                thumbnails.forEach(thumb => thumb.classList.remove('active'));
                thumbnails[index].classList.add('active');

                // Update featured member
                currentMemberIndex = index;
                featuredMemberImg.src = teamMembers[index].image;
                memberName.textContent = teamMembers[index].name;
                memberTitle.textContent = teamMembers[index].title;

                // Update scrolling name
                updateScrollingName();
            }

            // Initialize first member as active
            thumbnails[0].classList.add('active');
        });
    </script>
</body>

</html>