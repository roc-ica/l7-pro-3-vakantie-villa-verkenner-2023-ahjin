document.addEventListener('DOMContentLoaded', function () {
    const navLinks = document.querySelectorAll('.nav-link');

    // Add bounce effect on hover
    navLinks.forEach(link => {
        link.addEventListener('mouseenter', function () {
            this.style.transition = 'color 0.3s ease';
            this.animate(
                [
                    { transform: 'translateY(0)' },
                    { transform: 'translateY(-3px)' },
                    { transform: 'translateY(0)' }
                ],
                {
                    duration: 300,
                    easing: 'ease-out'
                }
            );
        });
    });

    // Highlight active nav link based on current page
    const currentPath = window.location.pathname.split('/').pop(); // Get the current file name

    navLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href === currentPath || (href !== '/' && currentPath.includes(href))) {
            link.classList.add('active');
        }
    });

    // Smooth scroll for footer links pointing to sections on the same page
    const footerLinks = document.querySelectorAll('.footer-column a[href^="#"]');

    footerLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            const targetId = this.getAttribute('href');
            const targetEl = document.querySelector(targetId);
            if (targetEl) {
                e.preventDefault();
                const offset = targetEl.getBoundingClientRect().top + window.scrollY - 100;
                window.scrollTo({
                    top: offset,
                    behavior: 'smooth'
                });
            }
        });
    });
});
