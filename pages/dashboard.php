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
    <title>Dashboard - EcoLife</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/components/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header>
        <h3>EcoLife Dashboard</h3>
        <div>
            <span class="welcome-message">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
            <a href="../includes/logout.php" class="logout-btn">Logout</a>
        </div>
    </header>

    <div class="dashboard-container">
        <h1>Your Eco Tools</h1>
        <div class="tools-grid">
            <a href="co2.php" class="tool-card">
                <div class="tool-icon">
                    <i class="fas fa-leaf"></i>
                </div>
                <h2>CO2 Calculator</h2>
                <p>Calculate and track your carbon footprint</p>
            </a>
            <a href="eco-chat.html" class="tool-card">
                <div class="tool-icon">
                    <i class="fas fa-robot"></i>
                </div>
                <h2>EcoGuide Chat</h2>
                <p>Get personalized sustainability advice</p>
            </a>
            <a href="challenges.php" class="tool-card">
                <div class="tool-icon">
                    <i class="fas fa-trophy"></i>
                </div>
                <h2>Eco Challenges</h2>
                <p>Complete challenges to earn green points</p>
            </a>
        </div>
    </div>
</body>
</html>