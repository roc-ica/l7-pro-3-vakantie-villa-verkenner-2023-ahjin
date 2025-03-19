document.addEventListener("DOMContentLoaded", function() {
    let priceSlider = document.getElementById("price-slider");
    let priceDisplay = document.getElementById("price-display");

    function updatePriceDisplay() {
        let value = parseInt(priceSlider.value);

        // Je kunt de min en max d.m.v. het 'value' van de slider aanpassen
        let min = 10000;  // Startwaarde
        let max = value;  // Standaard maximum is de waarde van de slider

        // Display de prijs
        priceDisplay.innerHTML = `€${min.toLocaleString()} - €${max.toLocaleString()}`;
    }

    priceSlider.addEventListener("input", updatePriceDisplay);

    updatePriceDisplay();
});
