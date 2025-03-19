document.addEventListener('DOMContentLoaded', function() {

    const currentPage = window.location.pathname;
    
    if (currentPage.includes('admin')) {
        document.querySelector('header nav ul li:first-child').classList.add('active-page');
    } else if (currentPage.includes('tickets')) {
        document.querySelector('header nav ul.right-ul li:first-child').classList.add('active-page');
    } else if (currentPage.includes('villas')) {
        document.querySelector('header nav ul.right-ul li:nth-child(2)').classList.add('active-page');
    }
    
    document.querySelectorAll('svg').forEach(svg => {
        svg.addEventListener('error', function() {
            const parent = this.parentNode;
            
            if (parent.classList.contains('villa-icon')) {
                this.outerHTML = '<span style="font-size: 1.5rem;">🏠</span>';
            } else if (parent.classList.contains('ticket-icon')) {
                this.outerHTML = '<span style="font-size: 1.5rem;">🎟️</span>';
            } else if (parent.classList.contains('monitor-icon')) {
                this.outerHTML = '<span style="font-size: 1.5rem;">🖥️</span>';
            } else if (parent.classList.contains('server-icon')) {
                this.outerHTML = '<span style="font-size: 1.5rem;">🖧</span>';
            } else if (parent.classList.contains('logout')) {
                this.outerHTML = '<span style="font-size: 1.5rem;">🚪</span>';
            } else if (parent.classList.contains('logo')) {
                this.outerHTML = '<span style="font-size: 1.5rem;">📊</span>';
            }
        });
    });
    
   /* =========================================
    ADMIN PAGE FUNCTIONALITY
    ========================================= */
    
    // Add hover effects for cards if they exist (admin page)
    const villaCard = document.querySelector('.villa-management');
    const ticketCard = document.querySelector('.ticket-management');
    
    if (villaCard) {
        villaCard.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 8px 24px rgba(0, 0, 0, 0.15)';
        });
        
        villaCard.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.1)';
        });
    }
    
    if (ticketCard) {
        ticketCard.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-5px)';
            this.style.boxShadow = '0 8px 24px rgba(0, 0, 0, 0.15)';
        });
        
        ticketCard.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
            this.style.boxShadow = '0 4px 12px rgba(0, 0, 0, 0.1)';
        });
    }
});