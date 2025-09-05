<?php
session_start();

// Check if the user is NOT logged in or is NOT an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

/* ---------- DB ---------- */
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

/* ---------- Fetch Report Data ---------- */
$reports = [];
$error = '';

try {
    // Total Users
    $stmt = $pdo->query("SELECT COUNT(*) AS total_users FROM users");
    $reports['total_users'] = $stmt->fetchColumn();

    // New Users in last 30 days
    $stmt = $pdo->query("SELECT COUNT(*) AS new_users FROM users WHERE created_at >= NOW() - INTERVAL 30 DAY");
    $reports['new_users'] = $stmt->fetchColumn();

    // Total Merchants
    $stmt = $pdo->query("SELECT COUNT(*) AS total_merchants FROM merchants");
    $reports['total_merchants'] = $stmt->fetchColumn();

    // Total Transactions
    $stmt = $pdo->query("SELECT COUNT(*) AS total_transactions FROM transactions");
    $reports['total_transactions'] = $stmt->fetchColumn();

    // Recent Transactions (Last 10)
    $stmt = $pdo->query("
        SELECT 
            t.action_type, 
            t.description, 
            t.points_earned, 
            t.points_used,
            t.created_at,
            u.first_name,
            u.last_name
        FROM transactions t
        LEFT JOIN users u ON t.user_id = u.id
        ORDER BY t.created_at DESC
        LIMIT 10
    ");
    $reports['recent_transactions'] = $stmt->fetchAll();

} catch (Exception $e) {
    $error = "Could not retrieve report data: " . $e->getMessage();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LoyaltyHub - View Reports</title>
    <link rel="stylesheet" href="styles/main.css">
    <style>
        .report-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        .card {
            background-color: #fff;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            text-align: center;
        }
        .card h3 {
            margin-top: 0;
            color: #4a4a4a;
        }
        .card .value {
            font-size: 2.5em;
            font-weight: bold;
            color: #007BFF;
            margin: 10px 0;
        }
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .report-table th, .report-table td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        .report-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .report-table tbody tr:nth-child(odd) {
            background-color: #f9f9f9;
        }
    </style>
</head>
<body>

    <header class="header">
        <div class="logo-container">
            <img src="images/logo.png" alt="LoyaltyHub Logo">
        </div>
        <nav class="nav">
            <a href="admin.php">Dashboard</a>
            <a href="logout.php" class="btn">Logout</a>
        </nav>
    </header>

    <main class="container">
        <h1>System Reports</h1>
        <p>A high-level overview of the platform's key metrics.</p>
        
        <?php if (!empty($error)): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php else: ?>

            <div class="report-cards">
                <div class="card">
                    <h3>Total Users</h3>
                    <div class="value"><?= htmlspecialchars($reports['total_users']) ?></div>
                </div>
                <div class="card">
                    <h3>New Users (Last 30 Days)</h3>
                    <div class="value"><?= htmlspecialchars($reports['new_users']) ?></div>
                </div>
                <div class="card">
                    <h3>Total Merchants</h3>
                    <div class="value"><?= htmlspecialchars($reports['total_merchants']) ?></div>
                </div>
                <div class="card">
                    <h3>Total Transactions</h3>
                    <div class="value"><?= htmlspecialchars($reports['total_transactions']) ?></div>
                </div>
            </div>

            <h2>Recent Transactions</h2>
            <?php if (empty($reports['recent_transactions'])): ?>
                <div class="alert info">No recent transactions found.</div>
            <?php else: ?>
                <table class="report-table">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Action Type</th>
                            <th>Description</th>
                            <th>Points Gained/Used</th>
                            <th>Date</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reports['recent_transactions'] as $transaction): ?>
                            <tr>
                                <td><?= htmlspecialchars($transaction['first_name'] . ' ' . $transaction['last_name']) ?></td>
                                <td><?= htmlspecialchars(ucfirst($transaction['action_type'])) ?></td>
                                <td><?= htmlspecialchars($transaction['description']) ?></td>
                                <td>
                                    <?php
                                        if ($transaction['action_type'] === 'earned') {
                                            echo '+' . htmlspecialchars($transaction['points_earned']);
                                        } else {
                                            echo '-' . htmlspecialchars($transaction['points_used']);
                                        }
                                    ?>
                                </td>
                                <td><?= htmlspecialchars($transaction['created_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endif; ?>
    </main>
    <footer class="site-footer">
        <p>&copy; 2025 Loyalty Rewards Program. All rights reserved.</p>
    </footer>
</body>
</html>
