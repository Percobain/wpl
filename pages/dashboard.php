<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Get user stats if available
require_once '../includes/db.php';
$userStats = [];
try {
    $stmt = $conn->prepare("SELECT * FROM co2_calculations WHERE user_id = :user_id ORDER BY created_at DESC LIMIT 1");
    $stmt->execute(['user_id' => $_SESSION['user_id']]);
    $co2Data = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($co2Data) {
        // Calculate carbon footprint values
        $carEmissions = $co2Data['car_travel'] * 0.12 * 52;
        $publicEmissions = $co2Data['public_transport'] * 1.5 * 52;
        $flightEmissions = $co2Data['flights'] * 90;
        $electricityEmissions = $co2Data['electricity'] * 0.5 * 12;
        $gasEmissions = $co2Data['natural_gas'] * 2.0 * 12;
        $meatEmissions = $co2Data['meat_consumption'] * 6.0 * 52;
        $localFoodReduction = $co2Data['local_food'] * -0.1 * $meatEmissions / 100;
        
        $totalEmissions = $carEmissions + $publicEmissions + $flightEmissions + 
                      $electricityEmissions + $gasEmissions + $meatEmissions + $localFoodReduction;
                      
        $userStats = [
            'total' => round($totalEmissions),
            'last_updated' => date('M j, Y', strtotime($co2Data['created_at'])),
            'categories' => [
                'Transportation' => round($carEmissions + $publicEmissions + $flightEmissions),
                'Home Energy' => round($electricityEmissions + $gasEmissions),
                'Diet' => round($meatEmissions + $localFoodReduction)
            ]
        ];
    }
} catch(PDOException $e) {
    // Silently handle errors
}

$challengeCount = 0;
try {
    $stmt = $conn->prepare("SELECT COUNT(*) as count FROM user_challenges WHERE username = :username AND status = 'completed'");
    $stmt->execute(['username' => $_SESSION['username']]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($result) {
        $challengeCount = $result['count'];
    }
} catch(PDOException $e) {
    // Silently handle errors
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - EcoLife</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/components/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Chart.js for visualizations -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="eco-leaves">
        <div class="leaf leaf1"></div>
        <div class="leaf leaf2"></div>
        <div class="leaf leaf3"></div>
        <div class="leaf leaf4"></div>
    </div>
    
    <div class="dashboard-wrapper">
        <aside class="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <i class="fas fa-leaf pulse"></i>
                    <span>EcoLife</span>
                </div>
                <div class="user-info">
                    <div class="avatar">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="user-details">
                        <h4><?php echo htmlspecialchars($_SESSION['username']); ?></h4>
                        <span class="user-level">Eco Enthusiast</span>
                    </div>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <ul>
                    <li class="active">
                        <a href="dashboard.php">
                            <i class="fas fa-tachometer-alt"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="co2.php">
                            <i class="fas fa-calculator"></i>
                            <span>Carbon Footprint</span>
                        </a>
                    </li>
                    <li>
                        <a href="eco-chat.html">
                            <i class="fas fa-robot"></i>
                            <span>EcoGuide Chat</span>
                        </a>
                    </li>
                    <li>
                        <a href="challenges.php">
                            <i class="fas fa-trophy"></i>
                            <span>Eco Challenges</span>
                        </a>
                    </li>
                    <!-- <li>
                        <a href="profile.php">
                            <i class="fas fa-user-cog"></i>
                            <span>Profile</span>
                        </a>
                    </li> -->
                </ul>
            </nav>
            
            <div class="sidebar-footer">
                <a href="../includes/logout.php" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>
        
        <main class="main-content">
            <header class="page-header">
                <div class="header-content">
                    <h1>Welcome back, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h1>
                    <p class="subtitle">Your journey to sustainable living continues today</p>
                </div>
                <div class="header-actions">
                    <div class="date-indicator">
                        <i class="far fa-calendar-alt"></i>
                        <span><?php echo date('F j, Y'); ?></span>
                    </div>
                </div>
            </header>
            
            <!-- Dashboard Overview Cards -->
            <section class="overview-cards">
                <div class="card footprint-card animate-in">
                    <div class="card-icon">
                        <i class="fas fa-globe-americas"></i>
                    </div>
                    <div class="card-content">
                        <h3>Carbon Footprint</h3>
                        <?php if (!empty($userStats)): ?>
                            <div class="card-value"><?php echo number_format($userStats['total']); ?> <span>kg CO2/year</span></div>
                            <div class="card-comparison <?php echo ($userStats['total'] < 7500) ? 'positive' : 'negative'; ?>">
                                <i class="fas <?php echo ($userStats['total'] < 7500) ? 'fa-arrow-down' : 'fa-arrow-up'; ?>"></i>
                                <span><?php echo abs(round(($userStats['total'] - 7500)/7500 * 100)); ?>% <?php echo ($userStats['total'] < 7500) ? 'below' : 'above'; ?> average</span>
                            </div>
                        <?php else: ?>
                            <div class="card-empty">
                                <p>Not calculated yet</p>
                                <a href="co2.php" class="action-link">Calculate Now</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card challenges-card animate-in" style="animation-delay: 0.1s">
                    <div class="card-icon">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="card-content">
                        <h3>Eco Challenges</h3>
                        <div class="card-value"><?php echo $challengeCount; ?> <span>completed</span></div>
                        <div class="progress-container">
                            <div class="progress-bar" style="width: <?php echo min(100, $challengeCount * 10); ?>%"></div>
                        </div>
                        <a href="challenges.php" class="action-link">View All Challenges</a>
                    </div>
                </div>
                
                <div class="card tips-card animate-in" style="animation-delay: 0.2s">
                    <div class="card-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <div class="card-content">
                        <h3>Eco Tip of the Day</h3>
                        <p class="eco-tip">"Replace single-use plastics with reusable alternatives like cloth bags, metal straws, and glass containers."</p>
                        <a href="eco-chat.html" class="action-link">Get More Tips</a>
                    </div>
                </div>
            </section>
            
            <!-- Detailed Dashboard Content -->
            <section class="dashboard-content">
                <?php if (!empty($userStats)): ?>
                <div class="content-row">
                    <div class="chart-container animate-in" style="animation-delay: 0.3s">
                        <h2>Carbon Footprint Breakdown</h2>
                        <div class="chart-wrapper">
                            <canvas id="footprintChart"></canvas>
                        </div>
                        <div class="chart-legend">
                            <div class="legend-item">
                                <span class="color-dot transport-dot"></span>
                                <span>Transportation</span>
                            </div>
                            <div class="legend-item">
                                <span class="color-dot energy-dot"></span>
                                <span>Home Energy</span>
                            </div>
                            <div class="legend-item">
                                <span class="color-dot diet-dot"></span>
                                <span>Diet</span>
                            </div>
                        </div>
                        <p class="last-updated">Last updated: <?php echo $userStats['last_updated']; ?></p>
                    </div>
                    
                    <div class="eco-tools-container animate-in" style="animation-delay: 0.4s">
                        <h2>Quick Actions</h2>
                        <div class="eco-tools">
                            <a href="co2.php" class="eco-tool">
                                <div class="tool-icon">
                                    <i class="fas fa-leaf"></i>
                                </div>
                                <div class="tool-details">
                                    <h3>Update CO2 Data</h3>
                                    <p>Recalculate your carbon footprint</p>
                                </div>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                            
                            <a href="eco-chat.html" class="eco-tool">
                                <div class="tool-icon">
                                    <i class="fas fa-robot"></i>
                                </div>
                                <div class="tool-details">
                                    <h3>EcoGuide Chat</h3>
                                    <p>Get personalized advice</p>
                                </div>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                            
                            <a href="challenges.php" class="eco-tool">
                                <div class="tool-icon">
                                    <i class="fas fa-tasks"></i>
                                </div>
                                <div class="tool-details">
                                    <h3>Start New Challenge</h3>
                                    <p>Choose your next eco challenge</p>
                                </div>
                                <i class="fas fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                </div>
                <?php else: ?>
                <div class="welcome-banner animate-in" style="animation-delay: 0.3s">
                    <div class="banner-content">
                        <h2>Start Your Eco Journey Today!</h2>
                        <p>Calculate your carbon footprint to get personalized recommendations and track your progress towards a more sustainable lifestyle.</p>
                        <a href="co2.php" class="btn-primary">Calculate My Footprint</a>
                    </div>
                    <div class="banner-image">
                        <img src="../assets/images/earth-illustration.svg" alt="Earth illustration" onerror="this.style.display='none'">
                    </div>
                </div>
                
                <div class="tools-grid animate-in" style="animation-delay: 0.4s">
                    <h2>Explore Your Eco Tools</h2>
                    <div class="grid-container">
                        <a href="co2.php" class="tool-card">
                            <div class="card-bg"></div>
                            <div class="tool-icon">
                                <i class="fas fa-leaf"></i>
                            </div>
                            <h3>CO2 Calculator</h3>
                            <p>Calculate and track your carbon footprint</p>
                        </a>
                        
                        <a href="eco-chat.html" class="tool-card">
                            <div class="card-bg"></div>
                            <div class="tool-icon">
                                <i class="fas fa-robot"></i>
                            </div>
                            <h3>EcoGuide Chat</h3>
                            <p>Get personalized sustainability advice</p>
                        </a>
                        
                        <a href="challenges.php" class="tool-card">
                            <div class="card-bg"></div>
                            <div class="tool-icon">
                                <i class="fas fa-trophy"></i>
                            </div>
                            <h3>Eco Challenges</h3>
                            <p>Complete challenges to earn green points</p>
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </section>
        </main>
    </div>
    
    <?php if (!empty($userStats)): ?>
    <script>
    // Initialize chart.js
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('footprintChart').getContext('2d');
        
        // Data from PHP
        const transportEmissions = <?php echo $userStats['categories']['Transportation']; ?>;
        const energyEmissions = <?php echo $userStats['categories']['Home Energy']; ?>;
        const dietEmissions = <?php echo $userStats['categories']['Diet']; ?>;
        
        const chart = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Transportation', 'Home Energy', 'Diet'],
                datasets: [{
                    data: [transportEmissions, energyEmissions, dietEmissions],
                    backgroundColor: [
                        '#3ec9a7', // Transportation
                        '#2d5f5d', // Home Energy
                        '#64b6ac'  // Diet
                    ],
                    borderWidth: 0,
                    borderRadius: 5,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                const value = context.raw;
                                const total = context.dataset.data.reduce((acc, val) => acc + val, 0);
                                const percentage = Math.round((value / total) * 100);
                                return `${context.label}: ${value} kg (${percentage}%)`;
                            }
                        },
                        backgroundColor: 'rgba(0, 25, 19, 0.8)',
                        padding: 12,
                        bodyFont: {
                            family: "'Poppins', sans-serif",
                            size: 14
                        },
                        titleFont: {
                            family: "'Poppins', sans-serif",
                            size: 16,
                            weight: 'bold'
                        }
                    }
                },
                animation: {
                    animateScale: true,
                    animateRotate: true,
                    duration: 2000,
                    easing: 'easeOutQuart'
                }
            }
        });
        
        // Add interactive animation on hover
        document.querySelectorAll('.animate-in').forEach(item => {
            item.classList.add('visible');
        });
    });
    </script>
    <?php endif; ?>
</body>
</html>