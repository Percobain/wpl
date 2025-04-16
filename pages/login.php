<?php
session_start();
require_once '../includes/db.php';
$error = null;

// If user is already logged in, redirect to home page
if (isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = trim($_POST['password']);

    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            
            header("Location: dashboard.php");  // Redirect to co2.php instead of co2.html
            exit();
        } else {
            $error = "Invalid username or password";
        }
    } catch(PDOException $e) {
        $error = "Login failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - EcoLife</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/components/auth.css">
</head>
<body>
    <div class="auth-container">
        <h2>Login</h2>
        <?php if($error) echo "<p class='error'>$error</p>"; ?>
        <?php if(isset($_GET['registration']) && $_GET['registration'] == 'success') echo "<p class='success'>Registration successful! Please login.</p>"; ?>
        <form method="POST" action="">
            <div class="form-group">
                <input type="text" name="username" required placeholder="Username">
            </div>
            <div class="form-group">
                <input type="password" name="password" required placeholder="Password">
            </div>
            <button type="submit">Login</button>
        </form>
        <p>Don't have an account? <a href="signup.php">Sign up here</a></p>
    </div>
</body>
</html>