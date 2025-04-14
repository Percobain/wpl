<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch the latest saved calculation for this user
require_once '../includes/db.php';
$latest_calculation = null;

try {
    $stmt = $conn->prepare("
        SELECT * FROM co2_calculations 
        WHERE user_id = :user_id 
        ORDER BY created_at DESC 
        LIMIT 1
    ");
    $stmt->execute([':user_id' => $_SESSION['user_id']]);
    $latest_calculation = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    // Silently handle errors - we'll just start with default values
    // $_SESSION['error_message'] = "Error fetching calculation: " . $e->getMessage();
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="app-container">
        <nav class="sidebar">
            <div class="sidebar-header">
                <!-- <img src="../assets/images/eco-logo.png" alt="EcoLearn Logo" class="logo"> -->
                <h3>EcoLearn</h3>
            </div>
            <ul class="sidebar-menu">
                <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                <li class="active"><a href="co2.php"><i class="fas fa-leaf"></i> CO2 Calculator</a></li>
                <li><a href="challenges.php"><i class="fas fa-trophy"></i> Challenges</a></li>
                <li><a href="eco-chat.html"><i class="fas fa-robot"></i> EcoGuide</a></li>
            </ul>
            <div class="sidebar-footer">
                <span class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <?php echo htmlspecialchars($_SESSION['username']); ?>
                </span>
                <a href="../includes/logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i></a>
            </div>
        </nav>

        <button class="mobile-nav-toggle">
            <i class="fas fa-bars"></i>
        </button>

        <main class="content">
            <header class="content-header">
                <h1><i class="fas fa-leaf"></i> CO2 Footprint Calculator</h1>
                <div class="actions">
                    <button id="help-btn" class="icon-btn"><i class="fas fa-question-circle"></i></button>
                    <button id="theme-toggle" class="icon-btn"><i class="fas fa-moon"></i></button>
                </div>
            </header>
            
            <?php
            if (isset($_SESSION['success_message'])) {
                echo '<div class="alert alert-success"><i class="fas fa-check-circle"></i> ' . htmlspecialchars($_SESSION['success_message']) . '</div>';
                unset($_SESSION['success_message']);
            }
            if (isset($_SESSION['error_message'])) {
                echo '<div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> ' . htmlspecialchars($_SESSION['error_message']) . '</div>';
                unset($_SESSION['error_message']);
            }
            
            // Display last saved message if we have a calculation
            if ($latest_calculation) {
                $saved_date = date('F j, Y \a\t g:i a', strtotime($latest_calculation['created_at']));
                echo '<div class="alert alert-info"><i class="fas fa-history"></i> Loading your last saved calculation from ' . $saved_date . '</div>';
            }
            ?>
            
            <div class="calculator-wrapper">
                <form action="save_calculator.php" method="POST" id="co2-form">
                    <div class="calculator-grid">
                        <div class="calculator-section transport-section">
                            <div class="section-header">
                                <i class="fas fa-car"></i>
                                <h2>Transport</h2>
                            </div>
                            <div class="section-info">
                                <div class="info-badge transport-badge">
                                    <span id="transport-percentage">25%</span>
                                    <span class="badge-label">of typical footprint</span>
                                </div>
                                <p class="context-info">Transportation contributes significantly to your carbon footprint through fuel consumption and emissions.</p>
                            </div>
                            <div class="slider-container">
                                <div class="slider-label">
                                    <span><i class="fas fa-car-side"></i> Car travel</span>
                                    <span class="value-display" id="car-value"><?php echo $latest_calculation ? $latest_calculation['car_travel'] : 100; ?> km/week</span>
                                </div>
                                <input type="range" min="0" max="1000" value="<?php echo $latest_calculation ? $latest_calculation['car_travel'] : 100; ?>" class="slider" id="car-slider" name="car_travel">
                                <div class="slider-context">
                                    <span class="eco-friendly">Eco-friendly</span>
                                    <span class="high-impact">High impact</span>
                                </div>
                            </div>
                            <div class="slider-container">
                                <div class="slider-label">
                                    <span><i class="fas fa-bus"></i> Public transport</span>
                                    <span class="value-display" id="public-value"><?php echo $latest_calculation ? $latest_calculation['public_transport'] : 5; ?> trips/week</span>
                                </div>
                                <input type="range" min="0" max="30" value="<?php echo $latest_calculation ? $latest_calculation['public_transport'] : 5; ?>" class="slider" id="public-slider" name="public_transport">
                                <div class="slider-context">
                                    <span class="eco-friendly">Eco-friendly</span>
                                    <span class="high-impact">High impact</span>
                                </div>
                            </div>
                            <div class="slider-container">
                                <div class="slider-label">
                                    <span><i class="fas fa-plane"></i> Flights</span>
                                    <span class="value-display" id="flight-value"><?php echo $latest_calculation ? $latest_calculation['flights'] : 10; ?> hours/year</span>
                                </div>
                                <input type="range" min="0" max="100" value="<?php echo $latest_calculation ? $latest_calculation['flights'] : 10; ?>" class="slider" id="flight-slider" name="flights">
                                <div class="slider-context">
                                    <span class="eco-friendly">Eco-friendly</span>
                                    <span class="high-impact">High impact</span>
                                </div>
                            </div>
                            <div class="section-footer">
                                <div class="impact-indicator" id="transport-impact">
                                    <div class="impact-icon"><i class="fas fa-leaf"></i></div>
                                    <span class="impact-text">Moderate Impact</span>
                                </div>
                                <div class="eco-action">
                                    <a href="#" class="action-link" data-tooltip="Learn ways to reduce your transport emissions"><i class="fas fa-info-circle"></i> Reduce impact</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="calculator-section energy-section">
                            <div class="section-header">
                                <i class="fas fa-bolt"></i>
                                <h2>Home Energy</h2>
                            </div>
                            <div class="section-info">
                                <div class="info-badge energy-badge">
                                    <span id="energy-percentage">30%</span>
                                    <span class="badge-label">of typical footprint</span>
                                </div>
                                <p class="context-info">Home energy use is a major source of emissions, particularly in regions relying on fossil fuels for electricity.</p>
                            </div>
                            <div class="slider-container">
                                <div class="slider-label">
                                    <span><i class="fas fa-lightbulb"></i> Electricity</span>
                                    <span class="value-display" id="electricity-value"><?php echo $latest_calculation ? $latest_calculation['electricity'] : 250; ?> kWh/month</span>
                                </div>
                                <input type="range" min="0" max="1000" value="<?php echo $latest_calculation ? $latest_calculation['electricity'] : 250; ?>" class="slider" id="electricity-slider" name="electricity">
                                <div class="slider-context">
                                    <span class="eco-friendly">Eco-friendly</span>
                                    <span class="high-impact">High impact</span>
                                </div>
                            </div>
                            <div class="slider-container">
                                <div class="slider-label">
                                    <span><i class="fas fa-fire"></i> Natural Gas</span>
                                    <span class="value-display" id="gas-value"><?php echo $latest_calculation ? $latest_calculation['natural_gas'] : 50; ?> m³/month</span>
                                </div>
                                <input type="range" min="0" max="300" value="<?php echo $latest_calculation ? $latest_calculation['natural_gas'] : 50; ?>" class="slider" id="gas-slider" name="natural_gas">
                                <div class="slider-context">
                                    <span class="eco-friendly">Eco-friendly</span>
                                    <span class="high-impact">High impact</span>
                                </div>
                            </div>
                            <div class="energy-source-selector">
                                <h4>Energy Source</h4>
                                <div class="source-options">
                                    <label class="source-option">
                                        <input type="radio" name="energy_source" value="grid" <?php echo (!$latest_calculation || $latest_calculation['energy_source'] == 'grid') ? 'checked' : ''; ?>>
                                        <span class="option-text">Standard Grid</span>
                                    </label>
                                    <label class="source-option">
                                        <input type="radio" name="energy_source" value="green" <?php echo ($latest_calculation && $latest_calculation['energy_source'] == 'green') ? 'checked' : ''; ?>>
                                        <span class="option-text">Green Energy</span>
                                    </label>
                                    <label class="source-option">
                                        <input type="radio" name="energy_source" value="mixed" <?php echo ($latest_calculation && $latest_calculation['energy_source'] == 'mixed') ? 'checked' : ''; ?>>
                                        <span class="option-text">Mixed Sources</span>
                                    </label>
                                </div>
                            </div>
                            <div class="section-footer">
                                <div class="impact-indicator" id="energy-impact">
                                    <div class="impact-icon"><i class="fas fa-leaf"></i></div>
                                    <span class="impact-text">Moderate Impact</span>
                                </div>
                                <div class="eco-action">
                                    <a href="#" class="action-link" data-tooltip="Learn ways to reduce your energy emissions"><i class="fas fa-info-circle"></i> Reduce impact</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="calculator-section food-section">
                            <div class="section-header">
                                <i class="fas fa-utensils"></i>
                                <h2>Food & Consumption</h2>
                            </div>
                            <div class="section-info">
                                <div class="info-badge food-badge">
                                    <span id="food-percentage">20%</span>
                                    <span class="badge-label">of typical footprint</span>
                                </div>
                                <p class="context-info">Food choices significantly impact your carbon footprint, especially meat consumption and food transportation.</p>
                            </div>
                            <div class="slider-container">
                                <div class="slider-label">
                                    <span><i class="fas fa-drumstick-bite"></i> Meat consumption</span>
                                    <span class="value-display" id="meat-value"><?php echo $latest_calculation ? $latest_calculation['meat_consumption'] : 7; ?> meals/week</span>
                                </div>
                                <input type="range" min="0" max="21" value="<?php echo $latest_calculation ? $latest_calculation['meat_consumption'] : 7; ?>" class="slider" id="meat-slider" name="meat_consumption">
                                <div class="slider-context">
                                    <span class="eco-friendly">Eco-friendly</span>
                                    <span class="high-impact">High impact</span>
                                </div>
                            </div>
                            <div class="slider-container">
                                <div class="slider-label">
                                    <span><i class="fas fa-seedling"></i> Local food</span>
                                    <span class="value-display" id="local-value"><?php echo $latest_calculation ? $latest_calculation['local_food'] : 30; ?>%</span>
                                </div>
                                <input type="range" min="0" max="100" value="<?php echo $latest_calculation ? $latest_calculation['local_food'] : 30; ?>" class="slider" id="local-slider" name="local_food">
                                <div class="slider-context">
                                    <span class="eco-friendly">Eco-friendly</span>
                                    <span class="high-impact">High impact</span>
                                </div>
                            </div>
                            <div class="diet-type-selector">
                                <h4>Diet Type</h4>
                                <div class="diet-options">
                                    <label class="diet-option">
                                        <input type="radio" name="diet_type" value="omnivore" <?php echo (!$latest_calculation || $latest_calculation['diet_type'] == 'omnivore') ? 'checked' : ''; ?>>
                                        <span class="option-text">Omnivore</span>
                                    </label>
                                    <label class="diet-option">
                                        <input type="radio" name="diet_type" value="vegetarian" <?php echo ($latest_calculation && $latest_calculation['diet_type'] == 'vegetarian') ? 'checked' : ''; ?>>
                                        <span class="option-text">Vegetarian</span>
                                    </label>
                                    <label class="diet-option">
                                        <input type="radio" name="diet_type" value="vegan" <?php echo ($latest_calculation && $latest_calculation['diet_type'] == 'vegan') ? 'checked' : ''; ?>>
                                        <span class="option-text">Vegan</span>
                                    </label>
                                </div>
                            </div>
                            <div class="section-footer">
                                <div class="impact-indicator" id="food-impact">
                                    <div class="impact-icon"><i class="fas fa-leaf"></i></div>
                                    <span class="impact-text">Moderate Impact</span>
                                </div>
                                <div class="eco-action">
                                    <a href="#" class="action-link" data-tooltip="Learn ways to reduce your food emissions"><i class="fas fa-info-circle"></i> Reduce impact</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="results-panel">
                            <div class="results-header">
                                <h2>Your CO2 Footprint</h2>
                                <div class="carbon-meter-container">
                                    <div class="carbon-meter">
                                        <div class="carbon-meter-fill" id="carbon-meter-fill"></div>
                                    </div>
                                </div>
                            </div>

                            <div class="total-emissions-container">
                                <div class="emissions-icon">
                                    <i class="fas fa-cloud"></i>
                                </div>
                                <div class="emissions-data">
                                    <div class="emissions-tabs">
                                        <button type="button" class="emissions-tab active" data-timeframe="year">Year</button>
                                        <button type="button" class="emissions-tab" data-timeframe="month">Month</button>
                                        <button type="button" class="emissions-tab" data-timeframe="day">Day</button>
                                    </div>
                                    <div class="total-emissions" id="total-emissions">0</div>
                                    <div class="emissions-unit">kg CO2/<span id="timeframe-unit">year</span></div>
                                </div>
                            </div>
                            
                            <div class="impact-rating" id="impact-rating">Calculating your impact...</div>
                            
                            <div class="eco-tips">
                                <h3><i class="fas fa-lightbulb"></i> Smart Eco Tip</h3>
                                <p id="eco-suggestion">Adjust the sliders to see personalized tips on reducing your carbon footprint.</p>
                            </div>
                            
                            <div class="results-actions">
                                <button type="button" id="share-btn" class="action-btn"><i class="fas fa-share-alt"></i> Share</button>
                                <button type="submit" class="save-btn"><i class="fas fa-save"></i> Save Results</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            
            <div class="comparison-section">
                <h3><i class="fas fa-chart-bar"></i> How You Compare</h3>
                <div class="comparison-chart">
                    <div class="chart-bar">
                        <div class="bar-label">Global Average</div>
                        <div class="bar-container">
                            <div class="bar-fill" style="width: 80%;"></div>
                            <span class="bar-value">7,500 kg</span>
                        </div>
                    </div>
                    <div class="chart-bar">
                        <div class="bar-label">Your Footprint</div>
                        <div class="bar-container">
                            <div class="bar-fill your-footprint" id="comparison-bar"></div>
                            <span class="bar-value" id="comparison-value">Calculating...</span>
                        </div>
                    </div>
                    <div class="chart-bar">
                        <div class="bar-label">Sustainable Goal</div>
                        <div class="bar-container">
                            <div class="bar-fill sustainable" style="width: 30%;"></div>
                            <span class="bar-value">2,000 kg</span>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Help Modal -->
    <div class="modal" id="help-modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-question-circle"></i> How to use the CO2 Calculator</h2>
                <button class="close-btn">&times;</button>
            </div>
            <div class="modal-body">
                <p>Move the sliders to input your consumption habits. The calculator will automatically update your carbon footprint estimate in real-time.</p>
                
                <div class="help-section">
                    <h3><i class="fas fa-car"></i> Transport</h3>
                    <ul>
                        <li><strong>Car travel:</strong> <span class="unit-badge">km/week</span> Your average weekly car travel distance</li>
                        <li><strong>Public transport:</strong> <span class="unit-badge">trips/week</span> Number of public transport journeys you take weekly</li>
                        <li><strong>Flights:</strong> <span class="unit-badge">hours/year</span> Total flying time per year</li>
                    </ul>
                </div>
                
                <div class="help-section">
                    <h3><i class="fas fa-bolt"></i> Home Energy</h3>
                    <ul>
                        <li><strong>Electricity:</strong> <span class="unit-badge">kWh/month</span> Your monthly electricity consumption</li>
                        <li><strong>Natural Gas:</strong> <span class="unit-badge">m³/month</span> Your monthly natural gas usage</li>
                        <li><strong>Energy Source:</strong> Choose your primary source of household energy</li>
                    </ul>
                </div>
                
                <div class="help-section">
                    <h3><i class="fas fa-utensils"></i> Food & Consumption</h3>
                    <ul>
                        <li><strong>Meat consumption:</strong> <span class="unit-badge">meals/week</span> Number of meals containing meat per week</li>
                        <li><strong>Local food:</strong> <span class="unit-badge">percentage</span> Proportion of locally sourced food in your diet</li>
                        <li><strong>Diet Type:</strong> Select your typical dietary pattern</li>
                    </ul>
                </div>
                
                <div class="help-section">
                    <h3><i class="fas fa-info-circle"></i> Results</h3>
                    <p>Your carbon footprint is measured in <span class="unit-badge">kg CO₂/year</span> and can be viewed as daily, monthly, or yearly emissions.</p>
                    <p>Based on your inputs, you'll receive personalized eco-tips to help reduce your carbon footprint.</p>
                </div>
                
                <div class="help-tips">
                    <h3><i class="fas fa-lightbulb"></i> Tips</h3>
                    <ul>
                        <li>Small changes in your habits can make a significant difference</li>
                        <li>Click the "Save Results" button to track your progress over time</li>
                        <li>Use the comparison chart to see how you compare to global averages</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../assets/js/co2-calculator.js"></script>
    <script>
    // Basic modal functionality until we create the proper JS file
    document.addEventListener('DOMContentLoaded', function() {
        const helpBtn = document.getElementById('help-btn');
        const helpModal = document.getElementById('help-modal');
        const closeBtn = document.querySelector('.close-btn');
        
        helpBtn.addEventListener('click', function() {
            helpModal.style.display = 'flex';
        });
        
        closeBtn.addEventListener('click', function() {
            helpModal.style.display = 'none';
        });
        
        window.addEventListener('click', function(event) {
            if (event.target == helpModal) {
                helpModal.style.display = 'none';
            }
        });

        // Initialize calculation with the loaded values
        calculateEmissions();
    });
    </script>
    <script src="../script.js"></script>
</body>
</html>