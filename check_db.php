<?php
require_once 'includes/db.php';

echo "<h2>Database Connection Check</h2>";

try {
    // Check connection
    echo "<p>Connection to database: <strong>SUCCESS</strong></p>";
    
    // Check if the users table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'users'");
    if ($stmt->rowCount() > 0) {
        echo "<p>Users table exists: <strong>YES</strong></p>";
        
        // Check table structure
        $stmt = $conn->query("DESCRIBE users");
        echo "<h3>Table Structure:</h3><ul>";
        while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            echo "<li>{$row['Field']} - {$row['Type']}</li>";
        }
        echo "</ul>";
        
        // Count users
        $stmt = $conn->query("SELECT COUNT(*) as count FROM users");
        $count = $stmt->fetch(PDO::FETCH_ASSOC)['count'];
        echo "<p>Number of users in table: <strong>{$count}</strong></p>";
        
        // Show first few users (without passwords)
        if ($count > 0) {
            $stmt = $conn->query("SELECT id, username, email, created_at FROM users LIMIT 5");
            echo "<h3>Sample Users:</h3><ul>";
            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                echo "<li>ID: {$row['id']}, Username: {$row['username']}, Email: {$row['email']}, Created: {$row['created_at']}</li>";
            }
            echo "</ul>";
        }
    } else {
        echo "<p style='color:red'>Users table does not exist!</p>";
        echo "<h3>Creating users table:</h3>";
        
        // Create the users table
        $sql = "CREATE TABLE users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(50) UNIQUE NOT NULL,
            email VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        
        $conn->exec($sql);
        echo "<p style='color:green'>Users table created successfully!</p>";
    }
} catch(PDOException $e) {
    echo "<p style='color:red'>Error: " . $e->getMessage() . "</p>";
}
?>