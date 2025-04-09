document.addEventListener("DOMContentLoaded", function() {
    const filterForm = document.getElementById('filter-form');

    // --- Range Sliders --- 
    const priceMinSlider = document.getElementById('price-slider-min');
    const priceMaxSlider = document.getElementById('price-slider-max');
    const priceDisplay = document.getElementById('price-display');

    const areaMinSlider = document.getElementById('area-slider-min');
    const areaMaxSlider = document.getElementById('area-slider-max');
    const areaDisplay = document.getElementById('area-display');

    function formatPrice(value) {
        return '€' + parseInt(value).toLocaleString('nl-NL');
    }

    function updatePriceDisplay() {
        if (!priceMinSlider || !priceMaxSlider || !priceDisplay) return;
        let minPrice = parseInt(priceMinSlider.value);
        let maxPrice = parseInt(priceMaxSlider.value);

        // Ensure min is less than or equal to max
        if (minPrice > maxPrice) {
             // Option 1: Swap values
             // [priceMinSlider.value, priceMaxSlider.value] = [priceMaxSlider.value, priceMinSlider.value];
             // minPrice = parseInt(priceMinSlider.value);
             // maxPrice = parseInt(priceMaxSlider.value);
             
             // Option 2: Set min to max if it goes over (simpler ux for two sliders)
             priceMinSlider.value = maxPrice;
             minPrice = maxPrice;
        }
        priceDisplay.textContent = `${formatPrice(minPrice)} - ${formatPrice(maxPrice)}`;
    }
    
    function updateAreaDisplay() {
        if (!areaMinSlider || !areaMaxSlider || !areaDisplay) return;
        let minArea = parseInt(areaMinSlider.value);
        let maxArea = parseInt(areaMaxSlider.value);

        if (minArea > maxArea) {
            // Option 2: Set min to max if it goes over
            areaMinSlider.value = maxArea;
            minArea = maxArea;
        }
        areaDisplay.textContent = `${minArea}m² - ${maxArea}m²`;
    }

    // Add event listeners for sliders
    [priceMinSlider, priceMaxSlider].forEach(slider => {
        if (slider) {
            slider.addEventListener('input', updatePriceDisplay);
            // slider.addEventListener('change', () => filterForm?.submit()); // Optional: auto-submit
        }
    });
    [areaMinSlider, areaMaxSlider].forEach(slider => {
        if (slider) {
            slider.addEventListener('input', updateAreaDisplay);
             // slider.addEventListener('change', () => filterForm?.submit()); // Optional: auto-submit
        }
    });

    // Initial display update
    updatePriceDisplay();
    updateAreaDisplay();

    // --- Steppers --- 
    const stepperButtons = document.querySelectorAll('.stepper button');

    stepperButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const input = document.getElementById(targetId);
            if (!input) return;

            let currentValue = parseInt(input.value) || 0;
            const min = parseInt(input.min) || 0;
            // const max = parseInt(input.max) || Infinity; // Add max if needed

            if (this.classList.contains('stepper-plus')) {
                currentValue++;
            } else if (this.classList.contains('stepper-minus')) {
                currentValue = Math.max(min, currentValue - 1);
            }
            
            input.value = currentValue;
            // Optional: Trigger form submit after changing stepper value
            // filterForm?.submit(); 
        });
    });
    
     // --- Optional: Auto-submit on checkbox change ---
    // const checkboxes = document.querySelectorAll('aside.filters input[type="checkbox"]');
    // checkboxes.forEach(checkbox => {
    //     checkbox.addEventListener('change', () => {
    //         filterForm?.submit();
    //     });
    // });
    
});
