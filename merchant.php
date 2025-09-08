<?php
session_start();

/* ---------- DB Connection ---------- */
require_once 'includes/db_connect.php'; // gives $conn (MySQLi)

// Redirect if not a logged-in user
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];

/* ---------- Get user data for welcome message ---------- */
$stmt_user = $conn->prepare("SELECT first_name FROM users WHERE id = ?");
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user = $result_user->fetch_assoc();
$stmt_user->close();

/* ---------- Get merchant ID from URL ---------- */
$merchant_id = $_GET['merchant_id'] ?? null;
if (!$merchant_id) {
    die("Merchant not specified.");
}

/* ---------- Get merchant name ---------- */
$stmt_merchant = $conn->prepare("SELECT name FROM merchants WHERE id = ?");
$stmt_merchant->bind_param("i", $merchant_id);
$stmt_merchant->execute();
$result_merchant = $stmt_merchant->get_result();
$merchant = $result_merchant->fetch_assoc();
$stmt_merchant->close();

if (!$merchant) {
    die("Merchant not found.");
}

/* ---------- Get current loyalty points ---------- */
$points_stmt = $conn->prepare("
    SELECT IFNULL(SUM(points_earned), 0) - IFNULL(SUM(points_used), 0) AS total_points
    FROM transactions
    WHERE user_id = ? AND merchant_id = ?
");
$points_stmt->bind_param("ii", $user_id, $merchant_id);
$points_stmt->execute();
$result_points = $points_stmt->get_result()->fetch_assoc();
$current_points = (int)$result_points['total_points'];
$points_stmt->close();

/* ---------- Get all rewards for this merchant ---------- */
$rewards_stmt = $conn->prepare("
    SELECT id, name, description, points_required 
    FROM rewards 
    WHERE merchant_id = ?
    ORDER BY points_required ASC
");
$rewards_stmt->bind_param("i", $merchant_id);
$rewards_stmt->execute();
$result_rewards = $rewards_stmt->get_result();

$rewards = [];
while ($row = $result_rewards->fetch_assoc()) {
    $rewards[] = $row;
}
$rewards_stmt->close();
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
