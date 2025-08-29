<?php
session_start();

// Security check: only allow admins to view this page
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'includes/db_connect.php';

// SQL query to fetch a comprehensive report
// We use LEFT JOIN to include all users, even if they don't have a company or loyalty points record.
$sql = "SELECT users.username, users.user_type, companies.company_name, loyalty_points.points_balance
        FROM users
        LEFT JOIN companies ON users.company_id = companies.company_id
        LEFT JOIN loyalty_points ON users.user_id = loyalty_points.user_id AND users.company_id = loyalty_points.company_id
        ORDER BY users.user_id DESC";
        
$result = $conn->query($sql);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LoyaltyHub - Reports</title>
    <link rel="stylesheet" href="styles/main.css">
    <style>
        .report-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .report-table th, .report-table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .report-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .no-data {
            text-align: center;
            font-style: italic;
            color: #777;
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
        <h1>User Reports</h1>
        <p>This report provides a comprehensive overview of all users in the system.</p>
        
        <?php if ($result->num_rows > 0): ?>
            <table class="report-table">
                <thead>
                    <tr>
                        <th>Username</th>
                        <th>User Type</th>
                        <th>Company</th>
                        <th>Points Balance</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['user_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['company_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($row['points_balance'] ?? 0); ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p class="no-data">No user data found in the system.</p>
        <?php endif; ?>
    </main>
</body>
</html>