<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

require_once '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['challenge_id'])) {
    try {
        $conn->beginTransaction();
        
        // Mark current challenge as completed
        $stmt = $conn->prepare("
            INSERT INTO user_challenges (username, challenge_id, status, completed_at)
            VALUES (:username, :challenge_id, 'completed', NOW())
        ");
        
        $stmt->execute([
            'username' => $_SESSION['username'],
            'challenge_id' => $_POST['challenge_id']
        ]);

        // Get a new challenge from the same category
        $stmt = $conn->prepare("
            SELECT c.* FROM challenges c
            WHERE c.category = :category
            AND c.id NOT IN (
                SELECT challenge_id 
                FROM user_challenges 
                WHERE username = :username
            )
            AND c.difficulty = (
                SELECT difficulty 
                FROM challenges 
                WHERE id = :current_challenge
            )
            ORDER BY RAND()
            LIMIT 1
        ");

        $stmt->execute([
            'category' => $_POST['category'],
            'username' => $_SESSION['username'],
            'current_challenge' => $_POST['challenge_id']
        ]);

        $conn->commit();
        // $_SESSION['success_message'] = "Challenge completed! New challenge unlocked!";
        
    } catch (Exception $e) {
        $conn->rollBack();
        $_SESSION['error_message'] = "Error completing challenge: " . $e->getMessage();
    }
}

header("Location: challenges.php");
exit();