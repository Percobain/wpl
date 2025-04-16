<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: pages/login.php");
    exit();
} else {
    // Redirect logged in users to the CO2 calculator page
    header("Location: pages/dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoLife</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <nav>
        <h2>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</h2>
        <a href="includes/logout.php">Logout</a>
    </nav>
    <h1>Home</h1>
</body>
</html>