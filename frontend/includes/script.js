document.addEventListener('DOMContentLoaded', function() {
    // Smooth hover effect for nav links
    const navLinks = document.querySelectorAll('.nav-link');
    
    navLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.style.transition = 'color 0.3s ease';
            // Add a slight bounce effect
            this.animate([
                { transform: 'translateY(0)' },
                { transform: 'translateY(-3px)' },
                { transform: 'translateY(0)' }
            ], {
                duration: 300,
                easing: 'ease-out'
            });
        });
    });
    
    // Add active class to current page in navigation
    const currentLocation = window.location.pathname;
    
    navLinks.forEach(link => {
        const linkPath = link.getAttribute('href');
        if (currentLocation === linkPath || currentLocation.includes(linkPath) && linkPath !== '/') {
            link.classList.add('active');
            link.style.color = '#00a3a3';
        }
    });
    
    // Smooth scroll for footer links
    const footerLinks = document.querySelectorAll('.footer-column a');
    
    footerLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            // Only for same-page links
            if (this.getAttribute('href').startsWith('#')) {
                e.preventDefault();
                const targetId = this.getAttribute('href');
                const targetElement = document.querySelector(targetId);
                
                if (targetElement) {
                    window.scrollTo({
                        top: targetElement.offsetTop - 100,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });
});
