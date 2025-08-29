<?php
session_start();

// Security check: only allow admins to view this page
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'includes/db_connect.php';

// SQL query to fetch all users and their associated company names
$sql = "SELECT users.user_id, users.username, users.user_type, companies.company_name 
        FROM users 
        LEFT JOIN companies ON users.company_id = companies.company_id 
        ORDER BY users.user_id DESC";
        
$result = $conn->query($sql);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LoyaltyHub - Manage Users</title>
    <link rel="stylesheet" href="styles/main.css">
    <style>
        /* Add some specific styles for the table */
        .user-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        .user-table th, .user-table td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .user-table th {
            background-color: #f2f2f2;
            font-weight: bold;
        }
        .action-links a {
            margin-right: 10px;
            text-decoration: none;
            color: #3498db;
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
        <h1>Manage Users</h1>
        
        <?php if ($result->num_rows > 0): ?>
            <table class="user-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>User Type</th>
                        <th>Company</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['user_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['username']); ?></td>
                        <td><?php echo htmlspecialchars($row['user_type']); ?></td>
                        <td><?php echo htmlspecialchars($row['company_name'] ?? 'N/A'); ?></td>
                        <td class="action-links">
                            <a href="edit_user.php?id=<?php echo htmlspecialchars($row['user_id']); ?>">Edit</a>
                            <a href="delete_user.php?id=<?php echo htmlspecialchars($row['user_id']); ?>" onclick="return confirm('Are you sure you want to delete this user?');">Delete</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p>No users found.</p>
        <?php endif; ?>
    </main>

</body>
</html>
