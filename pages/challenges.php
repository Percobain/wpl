<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

require_once '../includes/db.php';

// Get user's challenges and points
$stmt = $conn->prepare("
    SELECT DISTINCT c.*,
        uc.status,
        uc.completed_at,
        (SELECT COUNT(*) 
         FROM user_challenges uc2 
         WHERE uc2.username = :username 
         AND uc2.status = 'completed') as completed_count
    FROM challenges c
    LEFT JOIN user_challenges uc ON c.id = uc.challenge_id AND uc.username = :username
    WHERE c.id NOT IN (
        SELECT challenge_id 
        FROM user_challenges 
        WHERE username = :username 
        AND status = 'completed'
    )
    GROUP BY c.difficulty
    ORDER BY RAND()
    LIMIT 3
");

$stmt->execute(['username' => $_SESSION['username']]);
$challenges = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Calculate total points
$stmt = $conn->prepare("
    SELECT COALESCE(SUM(c.points), 0) as total_points
    FROM user_challenges uc
    JOIN challenges c ON uc.challenge_id = c.id
    WHERE uc.username = :username AND uc.status = 'completed'
");
$stmt->execute(['username' => $_SESSION['username']]);
$points = $stmt->fetch(PDO::FETCH_ASSOC);
$total_points = $points['total_points'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eco Challenges - <?php echo htmlspecialchars($_SESSION['username']); ?></title>
    <link rel="stylesheet" href="../assets/css/components/challenges.css">
</head>
<body>
    <header>
        <h3><?php echo htmlspecialchars($_SESSION['username']); ?>'s Eco Challenges</h3>
        <div class="user-stats">
            <span class="points">🌟 <?php echo $total_points; ?> Points</span>
            <a href="dashboard.php" class="nav-btn">Dashboard</a>
            <a href="../includes/logout.php" class="logout-btn">Logout</a>
        </div>
    </header>

    <main class="challenges-container">
        <div class="challenges-grid">
            <?php foreach ($challenges as $challenge): ?>
                <div class="challenge-card <?php echo $challenge['difficulty']; ?>">
                    <div class="difficulty-badge"><?php echo ucfirst($challenge['difficulty']); ?></div>
                    <h3><?php echo htmlspecialchars($challenge['title']); ?></h3>
                    <p><?php echo htmlspecialchars($challenge['description']); ?></p>
                    <div class="challenge-meta">
                        <span class="points-display">🌟 <?php echo $challenge['points']; ?> Points</span>
                    </div>
                    <form action="complete_challenge.php" method="POST">
                        <input type="hidden" name="challenge_id" value="<?php echo $challenge['id']; ?>">
                        <input type="hidden" name="category" value="<?php echo $challenge['category']; ?>">
                        <button type="submit" class="complete-btn">Complete Challenge</button>
                    </form>
                </div>
            <?php endforeach; ?>
        </div>
    </main>
</body>
</html>