document.addEventListener('DOMContentLoaded', function() {
    // Create toggle button for filters on mobile
    const filtersSection = document.querySelector('aside.filters');
    
    if (filtersSection) {
        // Create toggle button
        const toggleButton = document.createElement('button');
        toggleButton.className = 'filter-toggle';
        
        // Add icon and text for better UI
        toggleButton.innerHTML = '<span class="filter-icon">⚙️</span> Filter opties <span class="toggle-arrow">▼</span>';
        
        // Create a container for the content
        const filterContent = document.createElement('div');
        filterContent.className = 'filter-content';
        
        // Move all filter content into the container
        while (filtersSection.children.length > 0) {
            if (filtersSection.children[0].className !== 'filter-toggle') {
                filterContent.appendChild(filtersSection.children[0]);
            } else {
                break;
            }
        }
        
        // Add toggle and content container to the filters section
        filtersSection.insertBefore(toggleButton, filtersSection.firstChild);
        filtersSection.appendChild(filterContent);
        
        // Initial state based on screen size
        if (window.innerWidth <= 900) {
            filterContent.classList.remove('show');
        } else {
            filterContent.classList.add('show');
            toggleButton.classList.add('collapsed');
            toggleButton.querySelector('.toggle-arrow').textContent = '▲';
        }
        
        // Toggle functionality
        toggleButton.addEventListener('click', function() {
            filterContent.classList.toggle('show');
            toggleButton.classList.toggle('collapsed');
            
            // Update arrow direction
            const arrow = toggleButton.querySelector('.toggle-arrow');
            if (filterContent.classList.contains('show')) {
                arrow.textContent = '▲';
            } else {
                arrow.textContent = '▼';
            }
        });
        
        // Handle resize events
        window.addEventListener('resize', function() {
            if (window.innerWidth > 900) {
                filterContent.classList.add('show');
                toggleButton.querySelector('.toggle-arrow').textContent = '▲';
            } else if (!toggleButton.classList.contains('collapsed')) {
                filterContent.classList.remove('show');
                toggleButton.querySelector('.toggle-arrow').textContent = '▼';
            }
        });
    }
    
    // Adjust container padding on very small screens
    function adjustContainerPadding() {
        const container = document.querySelector('.container');
        if (container && window.innerWidth < 400) {
            container.style.padding = '5px';
        } else if (container) {
            container.style.padding = '';
        }
    }
    
    // Initial call and resize listener
    adjustContainerPadding();
    window.addEventListener('resize', adjustContainerPadding);
    
    // Mobile menu toggle if needed in the future
    // Code for mobile menu toggle can be added here
}); 