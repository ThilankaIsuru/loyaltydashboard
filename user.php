<?php
// Start session
session_start();

// Redirect to login if not authenticated as a regular user
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

// Database connection
require_once 'includes/db_connect.php'; // provides $conn (MySQLi)

// Get user's selected merchants
$stmt = $conn->prepare("
    SELECT m.id, m.name, m.description
    FROM user_merchants um
    JOIN merchants m ON um.merchant_id = m.id
    WHERE um.user_id = ?
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$result = $stmt->get_result();

$selected_merchants = [];
while ($row = $result->fetch_assoc()) {
    $selected_merchants[] = $row;
}
$stmt->close();

$is_logged_in = true;
$user_role = 'user';
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>LoyaltyHub - My Dashboard</title>
    <link rel="stylesheet" href="styles/main.css">
    <style>
        .dashboard-content {
            display: flex;
            flex-direction: column;
            gap: 20px;
            padding: 20px;
        }

        .merchants-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .merchant-card {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: center;
            padding: 20px;
            transition: transform 0.2s, box-shadow 0.2s;
            text-decoration: none;
            color: #333;
            display: block; /* Ensures the entire card is clickable */
        }

        .merchant-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .merchant-card img {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            margin-bottom: 10px;
        }

        .merchant-card p {
            font-weight: bold;
            margin: 0;
            font-size: 1.1em;
        }
    </style>
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
            <a href="user_dashboard.php" class="btn">My Profile</a>
            <a href="logout.php" class="btn">Logout</a>
        </nav>
    </header>

    <main class="container">
        <div class="dashboard-content">
            <h1>Welcome to Your Dashboard!</h1>
            <p>Here you can view the loyalty programs you have joined and manage your account.</p>

            <h3>Your Merchants</h3>
            <div class="merchants-grid">
                <?php if (count($selected_merchants) > 0): ?>
                    <?php foreach ($selected_merchants as $merchant): ?>
                        <a href="merchant.php?merchant_id=<?= htmlspecialchars($merchant['id']) ?>" class="merchant-card">
                            <img src="images/<?= htmlspecialchars($merchant['id']) ?>.png"
                                 alt="<?= htmlspecialchars($merchant['name']) ?>">
                            <p><?= htmlspecialchars($merchant['name']) ?></p>
                        </a>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p>You have not selected any merchants yet. Please go to the functionalities page to add some.</p>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <footer class="site-footer">
        <p>&copy; 2025 Loyalty Rewards Program. All rights reserved.</p>
    </footer>
</body>

</html>
