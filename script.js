document.addEventListener('DOMContentLoaded', function() {
    // Price slider functionality
    const minPriceSlider = document.getElementById('price-slider-min');
    const maxPriceSlider = document.getElementById('price-slider-max');
    const priceDisplay = document.getElementById('price-display');
    
    if (minPriceSlider && maxPriceSlider && priceDisplay) {
        // Format price with dots for thousands (Dutch format)
        const formatPrice = (price) => {
            return '€ ' + price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
        };
        
        // Update price display
        const updatePriceDisplay = () => {
            const minPrice = parseInt(minPriceSlider.value);
            const maxPrice = parseInt(maxPriceSlider.value);
            
            // Ensure min doesn't exceed max
            if (minPrice > maxPrice) {
                minPriceSlider.value = maxPrice;
            }
            
            priceDisplay.textContent = `${formatPrice(minPriceSlider.value)} - ${formatPrice(maxPriceSlider.value)}`;
        };
        
        // Initialize display
        updatePriceDisplay();
        
        // Add event listeners
        minPriceSlider.addEventListener('input', updatePriceDisplay);
        maxPriceSlider.addEventListener('input', updatePriceDisplay);
    }
    
    // Area slider functionality
    const minAreaSlider = document.getElementById('area-slider-min');
    const maxAreaSlider = document.getElementById('area-slider-max');
    const areaDisplay = document.getElementById('area-display');
    
    if (minAreaSlider && maxAreaSlider && areaDisplay) {
        // Update area display
        const updateAreaDisplay = () => {
            const minArea = parseInt(minAreaSlider.value);
            const maxArea = parseInt(maxAreaSlider.value);
            
            // Ensure min doesn't exceed max
            if (minArea > maxArea) {
                minAreaSlider.value = maxArea;
            }
            
            areaDisplay.textContent = `${minAreaSlider.value}m² - ${maxAreaSlider.value}m²`;
        };
        
        // Initialize display
        updateAreaDisplay();
        
        // Add event listeners
        minAreaSlider.addEventListener('input', updateAreaDisplay);
        maxAreaSlider.addEventListener('input', updateAreaDisplay);
    }
    
    // Stepper buttons functionality
    const stepperButtons = document.querySelectorAll('.stepper-plus, .stepper-minus');
    
    stepperButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.dataset.target;
            const inputField = document.getElementById(targetId);
            
            if (inputField) {
                let value = parseInt(inputField.value || 0);
                
                if (this.classList.contains('stepper-plus')) {
                    value += 1;
                } else {
                    value = Math.max(0, value - 1);
                }
                
                inputField.value = value;
            }
        });
    });
}); 