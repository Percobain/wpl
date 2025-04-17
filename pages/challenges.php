<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

require_once '../includes/db.php';

$stmt = $conn->prepare("
    WITH challenge_categories AS (
        SELECT 
            c.*,
            ROW_NUMBER() OVER (PARTITION BY c.difficulty ORDER BY RAND()) as row_num
        FROM challenges c
        WHERE c.id NOT IN (
            SELECT challenge_id 
            FROM user_challenges 
            WHERE username = :username 
            AND status = 'completed'
        )
    )
    SELECT cc.*, uc.status, uc.completed_at,
        (SELECT COUNT(*) 
         FROM user_challenges uc2 
         WHERE uc2.username = :username 
         AND uc2.status = 'completed') as completed_count
    FROM challenge_categories cc
    LEFT JOIN user_challenges uc ON cc.id = uc.challenge_id AND uc.username = :username
    WHERE cc.row_num <= 2  -- Get 2 challenges of each difficulty
    ORDER BY RAND()
    LIMIT 6
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

// Get completed challenges count
$stmt = $conn->prepare("
    SELECT COUNT(*) as completed_count
    FROM user_challenges
    WHERE username = :username AND status = 'completed'
");
$stmt->execute(['username' => $_SESSION['username']]);
$completed = $stmt->fetch(PDO::FETCH_ASSOC);
$completedCount = $completed['completed_count'];

// Get user level
$level = 1;
$nextLevelPoints = 100;
if ($total_points >= 100) {
    $level = floor($total_points / 100) + 1;
    $nextLevelPoints = $level * 100;
}
$progress = ($total_points % 100) / 100 * 100;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoQuests | <?php echo htmlspecialchars($_SESSION['username']); ?></title>
    <link rel="stylesheet" href="../assets/css/components/challenges.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/aos@next/dist/aos.css" />
</head>
<body>
    <div class="eco-bg"></div>
    <div class="gradient-overlay"></div>
    
    <nav class="main-nav">
        <div class="nav-container">
            <div class="logo">
                <div class="logo-img">
                    <i class="fas fa-seedling"></i>
                </div>
                <span>EcoQuests</span>
            </div>
            
            <div class="profile-section">
                <div class="level-badge" title="Your current level">
                    <span>LVL <?php echo $level; ?></span>
                </div>
                <div class="profile-menu">
                    <span class="username"><?php echo htmlspecialchars($_SESSION['username']); ?></span>
                    <img src="https://api.dicebear.com/7.x/avataaars/svg?seed=<?php echo $_SESSION['username']; ?>&backgroundColor=b6e3f4" alt="Profile" class="profile-img">
                    <div class="dropdown-menu">
                        <a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a>
                        <a href="co2.php"><i class="fas fa-calculator"></i> CO2 Calculator</a>
                        <a href="eco-chat.html"><i class="fas fa-robot"></i> Chatbot</a>
                        <a href="../includes/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="progress-overview">
        <div class="container">
            <div class="stat-cards">
                <div class="stat-card" data-aos="fade-up" data-aos-delay="100">
                    <div class="stat-icon ecology">
                        <i class="fas fa-leaf"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $total_points; ?></h3>
                        <p>Total EcoPoints</p>
                        <div class="progress-bar">
                            <div class="progress" style="width: <?php echo $progress; ?>%"></div>
                        </div>
                        <div class="level-info">
                            <span>Level <?php echo $level; ?></span>
                            <span><?php echo $total_points % 100; ?>/100 to Level <?php echo $level+1; ?></span>
                        </div>
                    </div>
                </div>
                
                <div class="stat-card" data-aos="fade-up" data-aos-delay="200">
                    <div class="stat-icon achievements">
                        <i class="fas fa-trophy"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $completedCount; ?></h3>
                        <p>Challenges Completed</p>
                    </div>
                </div>
                
                <div class="stat-card impact-card" data-aos="fade-up" data-aos-delay="300">
                    <div class="stat-icon impact">
                        <i class="fas fa-globe-americas"></i>
                    </div>
                    <div class="stat-info">
                        <h3>Eco Impact</h3>
                        <p>Your positive impact so far:</p>
                        <div class="impact-metrics">
                            <div class="impact-item">
                                <i class="fas fa-tint"></i>
                                <span><?php echo $completedCount * 25; ?> gal water saved</span>
                            </div>
                            <div class="impact-item">
                                <i class="fas fa-bolt"></i>
                                <span><?php echo $completedCount * 2; ?> kWh energy saved</span>
                            </div>
                            <div class="impact-item">
                                <i class="fas fa-tree"></i>
                                <span><?php echo floor($completedCount / 5); ?> trees equivalent</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <main class="challenges-container">
        <div class="container">
            <div class="section-header" data-aos="fade-up">
                <h1><i class="fas fa-mountain"></i> Your Active Challenges</h1>
                <p>Complete these eco-friendly challenges to earn points and make a real impact</p>
            </div>

            <?php if(empty($challenges)): ?>
                <div class="empty-state" data-aos="zoom-in">
                    <div class="trophy-animation">
                        <i class="fas fa-award"></i>
                        <div class="sparkles"></div>
                    </div>
                    <h2>Congratulations, Eco Champion!</h2>
                    <p>You've completed all available challenges. Your commitment to sustainability is making a real difference!</p>
                    <div class="action-buttons">
                        <a href="dashboard.php" class="primary-btn"><i class="fas fa-home"></i> Back to Dashboard</a>
                        <a href="leaderboard.php" class="secondary-btn"><i class="fas fa-trophy"></i> View Leaderboard</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="challenge-filter">
                    <div class="filter-options">
                        <button class="filter-btn active" data-filter="all">All Challenges</button>
                        <button class="filter-btn" data-filter="easy">Easy</button>
                        <button class="filter-btn" data-filter="medium">Medium</button>
                        <button class="filter-btn" data-filter="tough">Tough</button>
                    </div>
                    <div class="challenge-view-toggle">
                        <button class="view-btn active" data-view="grid"><i class="fas fa-th-large"></i></button>
                        <button class="view-btn" data-view="list"><i class="fas fa-list"></i></button>
                    </div>
                </div>
                
                <div class="challenges-grid view-grid">
                    <?php foreach ($challenges as $index => $challenge): 
                        // Determine the icon based on category
                        $icon = 'fa-leaf';
                        $categoryClass = '';
                        if(isset($challenge['category'])) {
                            $categoryClass = strtolower($challenge['category']);
                            switch(strtolower($challenge['category'])) {
                                case 'energy': 
                                    $icon = 'fa-bolt'; 
                                    break;
                                case 'water': 
                                    $icon = 'fa-water'; 
                                    break;
                                case 'waste': 
                                    $icon = 'fa-recycle'; 
                                    break;
                                case 'food': 
                                    $icon = 'fa-apple-alt'; 
                                    break;
                                case 'transport': 
                                    $icon = 'fa-bicycle'; 
                                    break;
                            }
                        }
                    ?>
                        <div class="challenge-card <?php echo $challenge['difficulty']; ?>" data-aos="fade-up" data-aos-delay="<?php echo $index * 100; ?>" data-category="<?php echo $challenge['difficulty']; ?>">
                            <div class="card-header <?php echo $categoryClass; ?>">
                                <div class="challenge-icon">
                                    <i class="fas <?php echo $icon; ?>"></i>
                                </div>
                                <div class="difficulty-badge">
                                    <?php 
                                        $difficultyIcon = 'fa-seedling';
                                        if($challenge['difficulty'] == 'medium') $difficultyIcon = 'fa-tree';
                                        if($challenge['difficulty'] == 'tough') $difficultyIcon = 'fa-mountain';
                                    ?>
                                    <i class="fas <?php echo $difficultyIcon; ?>"></i>
                                    <span><?php echo ucfirst($challenge['difficulty']); ?></span>
                                </div>
                            </div>
                            
                            <div class="card-body">
                                <h3><?php echo htmlspecialchars($challenge['title']); ?></h3>
                                
                                <div class="challenge-description">
                                    <p><?php echo htmlspecialchars($challenge['description']); ?></p>
                                </div>
                                
                                <div class="challenge-meta">
                                    <div class="points">
                                        <i class="fas fa-star"></i>
                                        <span><?php echo $challenge['points']; ?> Points</span>
                                    </div>
                                    
                                    <?php if(isset($challenge['category'])): ?>
                                    <div class="category">
                                        <i class="fas <?php echo $icon; ?>"></i>
                                        <span><?php echo ucfirst($challenge['category']); ?></span>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="card-footer">
                                <div class="impact-preview">
                                    <span>Impact: </span>
                                    <?php
                                        $impactText = "Helps reduce carbon footprint";
                                        if(isset($challenge['category'])) {
                                            switch(strtolower($challenge['category'])) {
                                                case 'energy': 
                                                    $impactText = "Saves " . ($challenge['points'] * 0.5) . " kWh of energy";
                                                    break;
                                                case 'water': 
                                                    $impactText = "Conserves " . ($challenge['points'] * 5) . " gallons of water";
                                                    break;
                                                case 'waste': 
                                                    $impactText = "Prevents " . ($challenge['points'] * 0.2) . " lbs of waste";
                                                    break;
                                                case 'food': 
                                                    $impactText = "Reduces " . ($challenge['points'] * 0.3) . " lbs CO2 emissions";
                                                    break;
                                                case 'transport': 
                                                    $impactText = "Saves " . ($challenge['points'] * 0.4) . " lbs CO2 emissions";
                                                    break;
                                            }
                                        }
                                    ?>
                                    <span class="impact-text"><?php echo $impactText; ?></span>
                                </div>
                                
                                <form action="complete_challenge.php" method="POST" class="complete-form">
                                    <input type="hidden" name="challenge_id" value="<?php echo $challenge['id']; ?>">
                                    <input type="hidden" name="category" value="<?php echo $challenge['category']; ?>">
                                    <button type="submit" class="complete-btn">
                                        <span class="btn-text">Complete Challenge</span>
                                        <span class="btn-icon"><i class="fas fa-check-circle"></i></span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="challenges-actions">
                    <button class="refresh-btn" data-aos="fade-up">
                        <i class="fas fa-sync-alt"></i>
                        <span>Show Different Challenges</span>
                    </button>
                </div>
            <?php endif; ?>
            
            <div class="additional-content" data-aos="fade-up">
                <div class="eco-tip">
                    <div class="tip-icon">
                        <i class="fas fa-lightbulb"></i>
                    </div>
                    <div class="tip-content">
                        <h3>Eco Tip of the Day</h3>
                        <p>Using a reusable water bottle instead of plastic bottles can save up to 1,460 plastic bottles per person each year.</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="eco-footer">
        <div class="container">
            <div class="footer-content">
                <div class="footer-logo">
                    <i class="fas fa-seedling"></i>
                    <span>EcoQuests</span>
                </div>
                <div class="footer-motto">
                    <p>"Every small action creates a ripple of positive change for our planet."</p>
                </div>
                <!-- <div class="footer-links">
                    <a href="dashboard.php">Dashboard</a>
                    <a href="profile.php">Profile</a>
                    <a href="about.php">About Us</a>
                    <a href="contact.php">Contact</a>
                </div> -->
            </div>
            <div class="footer-bottom">
                <p>&copy; 2025 EcoLife. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script src="https://unpkg.com/aos@next/dist/aos.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize AOS animation library
            AOS.init({
                duration: 800,
                easing: 'ease-out',
                once: true
            });
            
            // Profile dropdown toggle
            const profileMenu = document.querySelector('.profile-menu');
            if (profileMenu) {
                profileMenu.addEventListener('click', function() {
                    this.classList.toggle('active');
                });
                
                // Close dropdown when clicking outside
                document.addEventListener('click', function(event) {
                    if (!profileMenu.contains(event.target)) {
                        profileMenu.classList.remove('active');
                    }
                });
            }
            
            // Challenge filtering
            const filterButtons = document.querySelectorAll('.filter-btn');
            const challengeCards = document.querySelectorAll('.challenge-card');
            
            filterButtons.forEach(button => {
                button.addEventListener('click', () => {
                    // Remove active class from all buttons
                    filterButtons.forEach(btn => btn.classList.remove('active'));
                    
                    // Add active class to clicked button
                    button.classList.add('active');
                    
                    const filter = button.getAttribute('data-filter');
                    
                    // Show/hide challenges based on filter
                    challengeCards.forEach(card => {
                        if (filter === 'all' || card.getAttribute('data-category') === filter) {
                            card.style.display = '';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            });
            
            // View toggle (grid/list)
            const viewButtons = document.querySelectorAll('.view-btn');
            const challengesGrid = document.querySelector('.challenges-grid');
            
            viewButtons.forEach(button => {
                button.addEventListener('click', () => {
                    // Remove active class from all buttons
                    viewButtons.forEach(btn => btn.classList.remove('active'));
                    
                    // Add active class to clicked button
                    button.classList.add('active');
                    
                    const view = button.getAttribute('data-view');
                    
                    // Toggle view class
                    if (view === 'grid') {
                        challengesGrid.classList.remove('view-list');
                        challengesGrid.classList.add('view-grid');
                    } else {
                        challengesGrid.classList.remove('view-grid');
                        challengesGrid.classList.add('view-list');
                    }
                });
            });
            
            // Refresh button
            const refreshBtn = document.querySelector('.refresh-btn');
            if (refreshBtn) {
                refreshBtn.addEventListener('click', () => {
                    window.location.href = "?refresh=true";
                });
            }
            
            // Add hover effects to challenge cards
            challengeCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.classList.add('card-hover');
                });
                
                card.addEventListener('mouseleave', function() {
                    this.classList.remove('card-hover');
                });
            });
        });
    </script>
</body>
</html>