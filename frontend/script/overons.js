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
// Slower flying squares with more rotation
const squares = document.querySelectorAll('.square');
const officeSection = document.querySelector('.office');

// Set initial positions and rotations
squares.forEach((square, index) => {
    // Assign different initial rotations
    const initialRotation = -15 + (index * 10);

    // Set initial vertical positions
    const verticalOffset = (index % 2 === 0) ? -10 : 10;

    square.style.transform = `rotate(${initialRotation}deg) translateY(${verticalOffset}px)`;
    square.dataset.rotation = initialRotation;
    square.dataset.verticalOffset = verticalOffset;

    // Add a unique rotation speed for each square
    square.dataset.rotationSpeed = 120 + (index * 30);

    // Add a continuous rotation animation
    square.style.transition = 'transform 0.5s ease-out';
});

// Track last scroll position to determine direction
let lastScrollTop = 0;
let scrollDirection = 'down';

// Handle scroll event
window.addEventListener('scroll', function() {
            if (!officeSection) return;

            // Determine scroll direction
            const st = window.pageYOffset || document.documentElement.scrollTop;
            scrollDirection = st > lastScrollTop ? 'down' : 'up';
            lastScrollTop = st;

            const officeSectionRect = officeSection.getBoundingClientRect();
            const viewportHeight = window.innerHeight;
            const sectionTop = officeSectionRect.top;

            // Only animate when approaching and passing the section
            // Expanded range for slower animation
            if (sectionTop < viewportHeight * 1.5 && sectionTop > -viewportHeight * 0.8) {
                // Calculate progress: 0 when section is at bottom of viewport, 1 when at top
                // Slowed down by dividing by a larger number
                const progress = Math.min(1, Math.max(0, 1 - (sectionTop / (viewportHeight * 1.5))));

                squares.forEach((square, index) => {
                    // Get initial values
                    const initialRotation = parseInt(square.dataset.rotation) || 0;
                    const verticalOffset = parseInt(square.dataset.verticalOffset) || 0;
                    const rotationSpeed = parseInt(square.dataset.rotationSpeed) || 120;

                    // Create a slower exponential movement
                    const exponentialFactor = Math.pow(progress, 1.5) * 0.7; // Reduced factor for slower movement

                    // Calculate horizontal movement (flying to the right) - SLOWER
                    const horizontalSpeed = 400 + (index * 50); // Reduced speed
                    const horizontalMove = exponentialFactor * horizontalSpeed;

                    // Calculate vertical movement - SLOWER
                    const verticalDirection = verticalOffset > 0 ? 1 : -1;
                    const verticalSpeed = 50 + (index * 10); // Reduced speed
                    const verticalMove = verticalOffset + (exponentialFactor * verticalSpeed * verticalDirection);

                    // Calculate continuous rotation - MORE PRONOUNCED
                    // Base rotation + continuous rotation based on progress
                    const baseRotation = initialRotation + (exponentialFactor * 60);

                    // Add continuous rotation effect
                    // This creates a spinning effect as they fly off
                    const continuousRotation = baseRotation + (progress * rotationSpeed * (index % 2 === 0 ? 1 : -1));

                    // Apply transformations
                    square.style.transform = `
            rotate(${continuousRotation}deg) 
            translateX(${horizontalMove}px) 
            translateY(${verticalMove}px)
        `;

                    // Adjust opacity to fade out as they fly away - SLOWER
                    const opacity = Math.max(0, 0.5 - (exponentialFactor * 0.4));
                    square.style.opacity = opacity;
                });
            } else {
                // Reset squares when not in view
                squares.forEach(square => {
                    square.style.transform = `rotate(0deg) translateX(0px) translateY(0px)`;
                    square.style.opacity = 1;
                });
            }})