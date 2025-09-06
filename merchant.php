<?php
session_start();

// Database connection
$host = 'localhost';
$dbname = 'loyalty_rewards';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Redirect if not a logged-in user
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

// Get user data for welcome message
$stmt_user = $pdo->prepare("SELECT first_name FROM users WHERE id = ?");
$stmt_user->execute([$user_id]);
$user = $stmt_user->fetch();

// Get merchant ID from URL
$merchant_id = $_GET['merchant_id'] ?? null;

if (!$merchant_id) {
    die("Merchant not specified.");
}

// Get merchant name and logo (assuming logo is based on ID)
$stmt_merchant = $pdo->prepare("SELECT name FROM merchants WHERE id = ?");
$stmt_merchant->execute([$merchant_id]);
$merchant = $stmt_merchant->fetch();

if (!$merchant) {
    die("Merchant not found.");
}

// Get current loyalty points for the user at this merchant
$points_stmt = $pdo->prepare("
    SELECT IFNULL(SUM(points_earned), 0) - IFNULL(SUM(points_used), 0) AS total_points
    FROM transactions
    WHERE user_id = ? AND merchant_id = ?
");
$points_stmt->execute([$user_id, $merchant_id]);
$result = $points_stmt->fetch();
$current_points = (int)$result['total_points'];

// Get all rewards for this merchant
$rewards_stmt = $pdo->prepare("
    SELECT id, name, description, points_required 
    FROM rewards 
    WHERE merchant_id = ?
    ORDER BY points_required ASC
");
$rewards_stmt->execute([$merchant_id]);
$rewards = $rewards_stmt->fetchAll();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title><?= htmlspecialchars($merchant['name']) ?> | LoyaltyHub</title>
    <link rel="stylesheet" href="styles/main.css" />
</head>
<body>

    <header class="header">
        <div class="logo-container">
            <img src="images/logo.png" alt="LoyaltyHub Logo">
        </div>
        <nav class="nav">
            <a href="index.php">Home</a>
            <a href="functionalities.php">Functionalities</a>
            <a href="help.php">Help</a>
            <a href="user_dashboard.php">My Profile</a>
            <a href="logout.php" class="btn">Logout</a>
        </nav>
    </header>


    <main class="container">
        <div class="card">
            <div class="welcome">
                <h1 style="text-align: center;">Welcome, <?= htmlspecialchars($user['first_name']) ?>!</h1>
                <p style="text-align: center;">This is your loyalty dashboard for <?= htmlspecialchars($merchant['name']) ?>.</p>
            </div>
            <div class="card">
                <h2>Your Current Points</h2>
                <p style="text-align: center; font-size: 2.5em; font-weight: bold; color: #3f90ff;">
                    <?= $current_points ?>
                </p>
                <p style="text-align: center;">You can get below Rewards from your points at the merchant</p>
            </div>
        </div>
        
        <div class="card">
            <h2>Available Rewards</h2>
            <div class="rewards-grid">
                <?php if (empty($rewards)): ?>
                    <p style="text-align: center; color: #7f8c8d; font-style: italic;">
                        There are no rewards available for this merchant yet.</p>
                <?php else: ?>
                    <?php foreach ($rewards as $r): ?>
                        <div class="reward-card <?= $current_points >= $r['points_required'] ? 'can-redeem' : 'not-enough-points' ?>">
                            <h3><?= htmlspecialchars($r['name']) ?></h3>
                            <p><?= htmlspecialchars($r['description']) ?></p>
                            <p><strong><?= $r['points_required'] ?> pts</strong></p>
                            <?php if ($current_points >= $r['points_required']): ?>
    <p class="reward-available">✅ Reward available</p>
<?php else: ?>
    <p class="need-more-points">Need <?= $r['points_required'] - $current_points ?> more points</p>
<?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <a href="user.php" class="btn">← Back to Dashboard</a>
    </main>
    
    <footer class="site-footer">
        <p>&copy; 2025 Loyalty Rewards Program. All rights reserved.</p> 
    </footer>
    <style>
        .welcome {
            padding: 20px;
            text-align: center;
        }
        .rewards-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .reward-card {
            background-color: #f0f4f8;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
        }
        .reward-card h3 {
            color: #102a43;
            margin-bottom: 10px;
        }
        .reward-card p {
            font-size: 0.9em;
            color: #555;
            margin-bottom: 10px;
        }
        .reward-card strong {
            font-size: 1.2em;
            color: #3f90ff;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            border-radius: 4px;
            background-color: #3f90ff;
            color: white;
            text-decoration: none;
            text-align: center;
            transition: background-color 0.3s;
        }
        .btn:hover {
            background-color: #2e7af3;
        }
        .btn[disabled] {
            background-color: #ccc;
            cursor: not-allowed;
        }

        .reward-available {
         color: #27ae60;
         font-weight: bold;
        margin-top: 10px;
        }

    .need-more-points {
    color: #e74c3c;
    font-weight: bold;
    margin-top: 10px;
}
    </style>
</body>
</html>
