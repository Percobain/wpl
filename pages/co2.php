<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CO2 Calculator - EcoLearn</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/components/co2.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header>
        <h3>EcoLearn CO2 Calculator</h3>
        <div>
            <span class="welcome-message">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
            <a href="../includes/logout.php" class="logout-btn">Logout</a>
        </div>
    </header>
    
    <?php
    if (isset($_SESSION['success_message'])) {
        echo '<div class="alert alert-success">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
        unset($_SESSION['success_message']);
    }
    if (isset($_SESSION['error_message'])) {
        echo '<div class="alert alert-danger">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
        unset($_SESSION['error_message']);
    }
    ?>
    
    <div class="calculator-container">
        <h1>CO2 Footprint Calculator</h1>
        
        <form action="save_calculator.php" method="POST">
            <div class="calculator-section">
                <h2>Transport</h2>
                <div class="slider-container">
                    <div class="slider-label">
                        <span>Car travel (km/week)</span>
                        <span class="value-display" id="car-value">0</span>
                    </div>
                    <input type="range" min="0" max="1000" value="100" class="slider" id="car-slider" name="car_travel">
                </div>
                <div class="slider-container">
                    <div class="slider-label">
                        <span>Public transport (trips/week)</span>
                        <span class="value-display" id="public-value">0</span>
                    </div>
                    <input type="range" min="0" max="30" value="5" class="slider" id="public-slider" name="public_transport">
                </div>
                <div class="slider-container">
                    <div class="slider-label">
                        <span>Flights (hours/year)</span>
                        <span class="value-display" id="flight-value">0</span>
                    </div>
                    <input type="range" min="0" max="100" value="10" class="slider" id="flight-slider" name="flights">
                </div>
            </div>
            
            <div class="calculator-section">
                <h2>Home Energy</h2>
                <div class="slider-container">
                    <div class="slider-label">
                        <span>Electricity (kWh/month)</span>
                        <span class="value-display" id="electricity-value">0</span>
                    </div>
                    <input type="range" min="0" max="1000" value="250" class="slider" id="electricity-slider" name="electricity">
                </div>
                <div class="slider-container">
                    <div class="slider-label">
                        <span>Natural Gas (m³/month)</span>
                        <span class="value-display" id="gas-value">0</span>
                    </div>
                    <input type="range" min="0" max="300" value="50" class="slider" id="gas-slider" name="natural_gas">
                </div>
            </div>
            
            <div class="calculator-section">
                <h2>Food & Consumption</h2>
                <div class="slider-container">
                    <div class="slider-label">
                        <span>Meat consumption (meals/week)</span>
                        <span class="value-display" id="meat-value">0</span>
                    </div>
                    <input type="range" min="0" max="21" value="7" class="slider" id="meat-slider" name="meat_consumption">
                </div>
                <div class="slider-container">
                    <div class="slider-label">
                        <span>Local food percentage</span>
                        <span class="value-display" id="local-value">0</span>
                    </div>
                    <input type="range" min="0" max="100" value="30" class="slider" id="local-slider" name="local_food">
                </div>
            </div>
            
            <div class="results">
                <h2>Your CO2 Footprint</h2>
                <div class="total-emissions" id="total-emissions">0 kg CO2/year</div>
                <div class="impact-rating" id="impact-rating">Calculating your impact...</div>
                <div class="eco-tips" id="eco-tips">
                    <p><strong>Eco Tips:</strong> <span id="eco-suggestion">Adjust the sliders to see personalized tips on reducing your carbon footprint.</span></p>
                </div>
            </div>
            
            <button type="submit" class="save-btn">Save</button>
        </form>
    </div>
</div>
    <script src="../script.js"></script>
</body>
</html>