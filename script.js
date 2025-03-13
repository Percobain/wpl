// CO2 Calculator Script

// Initialize all sliders
const sliders = {
    car: { id: 'car-slider', value: 100, factor: 0.12 }, // 0.12 kg CO2 per km
    public: { id: 'public-slider', value: 5, factor: 1.5 }, // 1.5 kg CO2 per trip
    flight: { id: 'flight-slider', value: 10, factor: 90 }, // 90 kg CO2 per hour
    electricity: { id: 'electricity-slider', value: 250, factor: 0.5 }, // 0.5 kg CO2 per kWh
    gas: { id: 'gas-slider', value: 50, factor: 2.0 }, // 2.0 kg CO2 per m³
    meat: { id: 'meat-slider', value: 7, factor: 6.0 }, // 6.0 kg CO2 per meat meal
    local: { id: 'local-slider', value: 30, factor: -0.1 } // -0.1 kg CO2 per % of local food
};

// Initialize value displays
function initializeSliders() {
    for (const key in sliders) {
        const slider = document.getElementById(sliders[key].id);
        if (slider) {
            const display = document.getElementById(`${key}-value`);
            if (display) {
                display.textContent = slider.value + (key === 'local' ? '%' : '');
                sliders[key].value = parseInt(slider.value);
            }
        }
    }
    
    // Calculate initial emissions
    calculateEmissions();
    
    // Set up event listeners for all sliders
    for (const key in sliders) {
        const slider = document.getElementById(sliders[key].id);
        if (slider) {
            slider.addEventListener('input', function() {
                const display = document.getElementById(`${key}-value`);
                display.textContent = this.value + (key === 'local' ? '%' : '');
                sliders[key].value = parseInt(this.value);
                calculateEmissions();
            });
        }
    }
}

function calculateEmissions() {
    // Calculate total emissions
    let total = 0;
    
    // Transport emissions
    const carEmissions = sliders.car.value * sliders.car.factor * 52; // weekly to yearly
    const publicEmissions = sliders.public.value * sliders.public.factor * 52; // weekly to yearly
    const flightEmissions = sliders.flight.value * sliders.flight.factor; // already yearly
    
    // Home energy emissions
    const electricityEmissions = sliders.electricity.value * sliders.electricity.factor * 12; // monthly to yearly
    const gasEmissions = sliders.gas.value * sliders.gas.factor * 12; // monthly to yearly
    
    // Food emissions
    const meatEmissions = sliders.meat.value * sliders.meat.factor * 52; // weekly to yearly
    const localFoodReduction = sliders.local.value * sliders.local.factor * meatEmissions / 100; // percentage reduction
    
    // Sum up all emissions
    total = carEmissions + publicEmissions + flightEmissions + 
            electricityEmissions + gasEmissions + meatEmissions + localFoodReduction;
    
    // Display rounded result
    const totalElement = document.getElementById('total-emissions');
    if (totalElement) {
        totalElement.textContent = `${Math.round(total)} kg CO2/year`;
    }
    
    // Set impact rating
    const impactRating = document.getElementById('impact-rating');
    if (impactRating) {
        if (total < 5000) {
            impactRating.textContent = 'Low Carbon Impact - Great job!';
            impactRating.className = 'impact-rating impact-low';
        } else if (total < 10000) {
            impactRating.textContent = 'Medium Carbon Impact - Room for improvement';
            impactRating.className = 'impact-rating impact-medium';
        } else {
            impactRating.textContent = 'High Carbon Impact - Consider reducing';
            impactRating.className = 'impact-rating impact-high';
        }
    }
    
    // Generate eco tips based on highest emissions
    updateEcoTips(carEmissions, publicEmissions, flightEmissions, electricityEmissions, gasEmissions, meatEmissions);
}

function updateEcoTips(carEmissions, publicEmissions, flightEmissions, electricityEmissions, gasEmissions, meatEmissions) {
    const emissions = {
        car: { value: carEmissions, tip: "Consider carpooling, using an electric vehicle, or combining trips to reduce car emissions." },
        flight: { value: flightEmissions, tip: "Choose trains over planes for shorter trips when possible and consider carbon offsetting for necessary flights." },
        electricity: { value: electricityEmissions, tip: "Switch to energy-efficient appliances, LED bulbs, and consider renewable energy sources." },
        gas: { value: gasEmissions, tip: "Improve home insulation, lower your thermostat in winter, and consider a heat pump system." },
        meat: { value: meatEmissions, tip: "Try plant-based alternatives and consider meat-free days to reduce your dietary carbon footprint." }
    };
    
    // Find highest emission source
    let highestSource = null;
    let highestValue = 0;
    
    for (const source in emissions) {
        if (emissions[source].value > highestValue) {
            highestValue = emissions[source].value;
            highestSource = source;
        }
    }
    
    // Display relevant tip
    const ecoSuggestion = document.getElementById('eco-suggestion');
    if (ecoSuggestion && highestSource) {
        ecoSuggestion.textContent = emissions[highestSource].tip;
    }
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', initializeSliders);