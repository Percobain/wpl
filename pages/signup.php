<?php
require_once '../includes/db.php';
$error = null;
$debug_info = ""; // For debugging purposes

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = password_hash(trim($_POST['password']), PASSWORD_DEFAULT);

    try {
        // Check if username already exists
        $checkStmt = $conn->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $checkStmt->execute([$username, $email]);
        
        if ($checkStmt->rowCount() > 0) {
            $error = "Username or email already exists";
        } else {
            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $success = $stmt->execute([$username, $email, $password]);
            
            if ($success) {
                header("Location: login.php?registration=success");
                exit();
            } else {
                $error = "Insert operation failed";
                $debug_info = print_r($stmt->errorInfo(), true);
            }
        }
    } catch(PDOException $e) {
        $error = "Registration failed: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sign Up - EcoLearn</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/components/auth.css">
</head>
<body>
    <div class="auth-container">
        <h2>Sign Up</h2>
        <?php if($error) echo "<p class='error'>$error</p>"; ?>
        <?php if($debug_info) echo "<pre class='error'>$debug_info</pre>"; ?>
        <form method="POST" action="">
            <div class="form-group">
                <input type="text" name="username" required placeholder="Username">
            </div>
            <div class="form-group">
                <input type="email" name="email" required placeholder="Email">
            </div>
            <div class="form-group">
                <input type="password" name="password" required placeholder="Password">
            </div>
            <button type="submit">Sign Up</button>
        </form>
        <p>Already have an account? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>