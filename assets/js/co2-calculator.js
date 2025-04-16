document.addEventListener('DOMContentLoaded', function() {
    // Initialize the existing slider functionality from script.js
    initializeSliders();
    
    // Store references to DOM elements we'll need
    const carbonMeterFill = document.getElementById('carbon-meter-fill');
    const comparisonBar = document.getElementById('comparison-bar');
    const comparisonValue = document.getElementById('comparison-value');
    
    // Update the sliders to show units in the value displays
    updateSliderValueDisplays();
    
    // Apply animations and transitions
    setTimeout(() => {
        updateCarbonMeter();
        updateComparisonChart();
    }, 500);
    
    // Set up theme toggle
    const themeToggle = document.getElementById('theme-toggle');
    themeToggle.addEventListener('click', toggleTheme);
    
    // Set up share functionality
    const shareBtn = document.getElementById('share-btn');
    shareBtn.addEventListener('click', shareResults);

    // Call the function once on page load
    updateSectionImpacts();
    
    // Update whenever a slider changes
    document.querySelectorAll('.slider').forEach(slider => {
        slider.addEventListener('input', updateSectionImpacts);
    });
    
    // Update when radio options change
    document.querySelectorAll('input[type="radio"]').forEach(radio => {
        radio.addEventListener('change', updateSectionImpacts);
    });
    
    // Add click handler for eco-action links
    document.querySelectorAll('.action-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Future implementation: Show a modal with eco tips specific to each section
            const section = this.closest('.calculator-section').classList[1].replace('-section', '');
            alert(`Here are tips to reduce your ${section} carbon footprint! (You can replace this with a more sophisticated modal in the future)`);
        });
    });
    
    // Initialize diet and energy source selectors
    const dietRadios = document.querySelectorAll('input[name="diet_type"]');
    dietRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'vegetarian') {
                document.getElementById('meat-slider').value = 0;
                document.getElementById('meat-value').textContent = '0 meals/week';
                document.getElementById('meat-slider').disabled = true;
            } else if (this.value === 'vegan') {
                document.getElementById('meat-slider').value = 0;
                document.getElementById('meat-value').textContent = '0 meals/week';
                document.getElementById('meat-slider').disabled = true;
            } else {
                document.getElementById('meat-slider').disabled = false;
            }
            updateSectionImpacts();
        });
    });
    
    const energyRadios = document.querySelectorAll('input[name="energy_source"]');
    energyRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'renewable') {
                document.getElementById('electricity-slider').value = 0;
                document.getElementById('electricity-value').textContent = '0 kWh/month';
                document.getElementById('electricity-slider').disabled = true;
            } else {
                document.getElementById('electricity-slider').disabled = false;
            }
            updateSectionImpacts();
        });
    });

    // Add the new timeframe tabs setup
    setupTimeframeTabs();

    // Add the new mobile navigation menu setup
    const menuToggle = document.querySelector('.mobile-nav-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    menuToggle.addEventListener('click', function() {
        sidebar.classList.toggle('active');
    });
    
    // Close sidebar when clicking outside of it
    document.addEventListener('click', function(event) {
        if (!sidebar.contains(event.target) && !menuToggle.contains(event.target)) {
            sidebar.classList.remove('active');
        }
    });

    const content = document.querySelector('.content');
    
    // Function to toggle sidebar
    function toggleSidebar() {
        sidebar.classList.toggle('collapsed');
        content.classList.toggle('expanded');
    }
    
    // Check if the window is too narrow and collapse sidebar by default
    function checkWindowSize() {
        if (window.innerWidth < 1200) {
            sidebar.classList.add('collapsed');
            content.classList.add('expanded');
        }
    }
    
    // Run on page load
    checkWindowSize();
    
    // Toggle button click event
    menuToggle.addEventListener('click', toggleSidebar);
});

function updateSliderValueDisplays() {
    // Get all DOM elements with "value" in their IDs
    const valueDisplays = document.querySelectorAll('[id$="-value"]');
    
    // Update each value display with appropriate units
    valueDisplays.forEach(display => {
        const id = display.id;
        const value = display.textContent;
        
        if (id === 'car-value') {
            display.textContent = addUnits(value, 'km/week');
        } else if (id === 'public-value') {
            display.textContent = addUnits(value, 'trips/week');
        } else if (id === 'flight-value') {
            display.textContent = addUnits(value, 'hours/year');
        } else if (id === 'electricity-value') {
            display.textContent = addUnits(value, 'kWh/month');
        } else if (id === 'gas-value') {
            display.textContent = addUnits(value, 'm³/month');
        } else if (id === 'meat-value') {
            display.textContent = addUnits(value, 'meals/week');
        } else if (id === 'local-value') {
            display.textContent = value + '%';
        }
    });
}

function addUnits(value, unit) {
    // Remove any existing units first
    const valueOnly = value.replace(/[^0-9.]/g, '');
    return `${valueOnly} ${unit}`;
}

function updateCarbonMeter() {
    const totalEmissions = document.getElementById('total-emissions').textContent;
    const emissionsValue = parseInt(totalEmissions);
    let percentage;
    
    // Calculate percentage based on emissions
    if (emissionsValue < 2000) {
        percentage = 10;
    } else if (emissionsValue < 5000) {
        percentage = 30;
    } else if (emissionsValue < 10000) {
        percentage = 70;
    } else {
        percentage = 90;
    }
    
    // Update carbon meter
    const carbonMeterFill = document.getElementById('carbon-meter-fill');
    if (carbonMeterFill) {
        carbonMeterFill.style.width = `${percentage}%`;
    }
}

function updateComparisonChart() {
    const totalEmissions = document.getElementById('total-emissions').textContent;
    const emissionsValue = parseInt(totalEmissions);
    
    // Calculate percentage of global average (7500kg)
    const percentage = Math.min((emissionsValue / 7500) * 100, 100);
    
    // Update comparison bar
    const comparisonBar = document.getElementById('comparison-bar');
    if (comparisonBar) {
        comparisonBar.style.width = `${percentage}%`;
    }
    
    // Update comparison value
    const comparisonValue = document.getElementById('comparison-value');
    if (comparisonValue) {
        comparisonValue.textContent = `${emissionsValue} kg`;
        
        // Animate count up
        animateValue(comparisonValue, 0, emissionsValue, 1500);
    }
}

// Improved animateValue function that handles decimal values
function animateValue(obj, start, end, duration) {
    let startTimestamp = null;
    const step = (timestamp) => {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        
        // Handle decimal values appropriately
        const isDecimal = end % 1 !== 0;
        const current = progress * (end - start) + start;
        
        // Format with appropriate decimal places
        const formatted = isDecimal ? current.toFixed(1) : Math.floor(current);
        
        // Update display
        obj.textContent = formatted;
        
        if (progress < 1) {
            window.requestAnimationFrame(step);
        }
    };
    window.requestAnimationFrame(step);
}

function toggleTheme() {
    const body = document.body;
    body.classList.toggle('dark-theme');
    
    const themeIcon = document.querySelector('#theme-toggle i');
    if (body.classList.contains('dark-theme')) {
        themeIcon.className = 'fas fa-sun';
    } else {
        themeIcon.className = 'fas fa-moon';
    }
}

function shareResults() {
    const totalEmissions = document.getElementById('total-emissions').textContent;
    const impactRating = document.getElementById('impact-rating').textContent;
    
    const shareText = `I just calculated my carbon footprint: ${totalEmissions} CO2/year. ${impactRating} Calculate yours at EcoLife!`;
    
    // Check if Web Share API is available
    if (navigator.share) {
        navigator.share({
            title: 'My Carbon Footprint Results',
            text: shareText,
            url: window.location.href,
        })
        .catch(error => {
            console.error('Error sharing:', error);
            fallbackShare(shareText);
        });
    } else {
        fallbackShare(shareText);
    }
}

function fallbackShare(text) {
    // Create a temporary input to copy the text
    const input = document.createElement('textarea');
    input.value = text;
    document.body.appendChild(input);
    input.select();
    document.execCommand('copy');
    document.body.removeChild(input);
    
    // Show a message that text was copied
    alert('Results copied to clipboard! Share with your friends.');
}

// Override the existing calculateEmissions function to add animations
const originalCalculateEmissions = window.calculateEmissions || (() => {});

window.calculateEmissions = function() {
    // Call the original function
    originalCalculateEmissions();
    
    // Get and store the current yearly emissions value
    const totalElement = document.getElementById('total-emissions');
    // Using a more specific regex to extract just the number
    const numericValue = totalElement.textContent.match(/\d+(\.\d+)?/);
    originalYearlyEmissions = numericValue ? parseFloat(numericValue[0]) : 0;
    console.log("Updated original yearly emissions:", originalYearlyEmissions);
    
    // Reset to yearly view when recalculating
    const timeframeTabs = document.querySelectorAll('.emissions-tab');
    timeframeTabs.forEach(tab => {
        if (tab.getAttribute('data-timeframe') === 'year') {
            tab.click();
        }
    });
    
    // Add our enhancements
    updateCarbonMeter();
    updateComparisonChart();
    
    // Add animations to the total emissions counter
    animateValue(totalElement, 0, originalYearlyEmissions, 1000);
};

function updateSectionImpacts() {
    // Calculate transport impact
    const carValue = parseInt(document.getElementById('car-slider').value);
    const publicValue = parseInt(document.getElementById('public-slider').value);
    const flightValue = parseInt(document.getElementById('flight-slider').value);
    
    // Simple calculation for demo purposes
    const transportImpact = (carValue / 10) + (publicValue * 2) + (flightValue * 5);
    const transportPercentage = Math.round((transportImpact / 500) * 100);
    
    // Update transport section
    document.getElementById('transport-percentage').textContent = Math.min(transportPercentage, 100) + '%';
    const transportImpactEl = document.getElementById('transport-impact');
    
    if (transportImpact < 200) {
        transportImpactEl.innerHTML = '<div class="impact-icon"><i class="fas fa-leaf"></i></div><span class="impact-text">Low Impact</span>';
        transportImpactEl.className = 'impact-indicator low';
    } else if (transportImpact < 400) {
        transportImpactEl.innerHTML = '<div class="impact-icon"><i class="fas fa-leaf"></i></div><span class="impact-text">Moderate Impact</span>';
        transportImpactEl.className = 'impact-indicator medium';
    } else {
        transportImpactEl.innerHTML = '<div class="impact-icon"><i class="fas fa-cloud"></i></div><span class="impact-text">High Impact</span>';
        transportImpactEl.className = 'impact-indicator high';
    }
    
    // Calculate energy impact
    const electricityValue = parseInt(document.getElementById('electricity-slider').value);
    const gasValue = parseInt(document.getElementById('gas-slider').value);
    
    // Simple calculation for demo purposes
    const energyImpact = (electricityValue / 5) + (gasValue * 3);
    const energyPercentage = Math.round((energyImpact / 400) * 100);
    
    // Update energy section
    document.getElementById('energy-percentage').textContent = Math.min(energyPercentage, 100) + '%';
    const energyImpactEl = document.getElementById('energy-impact');
    
    if (energyImpact < 150) {
        energyImpactEl.innerHTML = '<div class="impact-icon"><i class="fas fa-leaf"></i></div><span class="impact-text">Low Impact</span>';
        energyImpactEl.className = 'impact-indicator low';
    } else if (energyImpact < 300) {
        energyImpactEl.innerHTML = '<div class="impact-icon"><i class="fas fa-leaf"></i></div><span class="impact-text">Moderate Impact</span>';
        energyImpactEl.className = 'impact-indicator medium';
    } else {
        energyImpactEl.innerHTML = '<div class="impact-icon"><i class="fas fa-cloud"></i></div><span class="impact-text">High Impact</span>';
        energyImpactEl.className = 'impact-indicator high';
    }
    
    // Calculate food impact
    const meatValue = parseInt(document.getElementById('meat-slider').value);
    const localValue = parseInt(document.getElementById('local-slider').value);
    
    // Simple calculation for demo purposes
    const foodImpact = (meatValue * 10) - (localValue * 0.7);
    const foodPercentage = Math.round((foodImpact / 150) * 100);
    
    // Update food section
    document.getElementById('food-percentage').textContent = Math.min(foodPercentage, 100) + '%';
    const foodImpactEl = document.getElementById('food-impact');
    
    if (foodImpact < 70) {
        foodImpactEl.innerHTML = '<div class="impact-icon"><i class="fas fa-leaf"></i></div><span class="impact-text">Low Impact</span>';
        foodImpactEl.className = 'impact-indicator low';
    } else if (foodImpact < 120) {
        foodImpactEl.innerHTML = '<div class="impact-icon"><i class="fas fa-leaf"></i></div><span class="impact-text">Moderate Impact</span>';
        foodImpactEl.className = 'impact-indicator medium';
    } else {
        foodImpactEl.innerHTML = '<div class="impact-icon"><i class="fas fa-cloud"></i></div><span class="impact-text">High Impact</span>';
        foodImpactEl.className = 'impact-indicator high';
    }
}

// Global variable to store the original yearly emissions value
let originalYearlyEmissions = 0;

// Function to update emissions display based on timeframe
function updateEmissionsDisplay(timeframe) {
    // On first call, store the original yearly value if it hasn't been stored yet
    if (originalYearlyEmissions === 0) {
        const totalElement = document.getElementById('total-emissions');
        // Using a more specific regex to extract just the number
        const numericValue = totalElement.textContent.match(/\d+(\.\d+)?/);
        originalYearlyEmissions = numericValue ? parseFloat(numericValue[0]) : 0;
        console.log("Stored original yearly emissions:", originalYearlyEmissions);
    }
    
    let newValue;
    
    // Calculate the appropriate value based on timeframe using the original yearly value
    if (timeframe === 'year') {
        newValue = Math.round(originalYearlyEmissions);
    } else if (timeframe === 'month') {
        // Divide yearly value by 12 to get monthly
        newValue = Math.round(originalYearlyEmissions / 12);
    } else if (timeframe === 'day') {
        // Divide yearly value by 365 to get daily (with ONE decimal place)
        newValue = parseFloat((originalYearlyEmissions / 365).toFixed(1));
    }
    
    console.log(`Converting ${originalYearlyEmissions}kg/year to ${timeframe}: ${newValue}`);
    
    // Update the display with animation
    const totalElement = document.getElementById('total-emissions');
    
    // Check if our current value is from yearly calculation or not
    const currentText = totalElement.textContent;
    const currentNumeric = currentText.match(/\d+(\.\d+)?/);
    const currentValue = currentNumeric ? parseFloat(currentNumeric[0]) : 0;
    
    // Use a cleaner animation with the correct starting value
    animateValue(totalElement, currentValue, newValue, 500);
    
    // Also update the comparison chart to match the timeframe
    if (timeframe === 'year') {
        updateComparisonChartForTimeframe(newValue, 7500); // global average is 7500kg/year
    } else if (timeframe === 'month') {
        updateComparisonChartForTimeframe(newValue, Math.round(7500 / 12)); // monthly global average
    } else if (timeframe === 'day') {
        updateComparisonChartForTimeframe(newValue, parseFloat((7500 / 365).toFixed(1))); // daily global average
    }
}

// Helper function to update comparison chart based on timeframe
function updateComparisonChartForTimeframe(userValue, globalAverage) {
    const comparisonBar = document.getElementById('comparison-bar');
    const comparisonValue = document.getElementById('comparison-value');
    
    if (comparisonBar) {
        const percentage = Math.min((userValue / globalAverage) * 100, 100);
        comparisonBar.style.width = `${percentage}%`;
    }
    
    if (comparisonValue) {
        comparisonValue.textContent = `${userValue} kg`;
        animateValue(comparisonValue, 0, userValue, 1500);
    }
}

// Update the tab click handler to prevent form submission
function setupTimeframeTabs() {
    const timeframeTabs = document.querySelectorAll('.emissions-tab');
    const timeframeUnit = document.getElementById('timeframe-unit');
    
    // Add event listeners to each tab
    timeframeTabs.forEach(tab => {
        tab.addEventListener('click', function(e) {
            // Prevent default action - crucial to avoid form submission
            e.preventDefault();
            
            // Remove active class from all tabs
            timeframeTabs.forEach(t => t.classList.remove('active'));
            
            // Add active class to clicked tab
            this.classList.add('active');
            
            // Get the timeframe from data attribute
            const timeframe = this.getAttribute('data-timeframe');
            timeframeUnit.textContent = timeframe;
            
            // Calculate and display emissions based on timeframe
            updateEmissionsDisplay(timeframe);
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const mobileToggle = document.querySelector('.mobile-nav-toggle');
    const sidebar = document.querySelector('.sidebar');
    
    if (mobileToggle && sidebar) {
        mobileToggle.addEventListener('click', function() {
            sidebar.classList.toggle('active');
        });
        
        // Close sidebar when clicking outside
        document.addEventListener('click', function(e) {
            if (!sidebar.contains(e.target) && e.target !== mobileToggle) {
                sidebar.classList.remove('active');
            }
        });
    }
});