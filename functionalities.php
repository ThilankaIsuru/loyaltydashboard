<?php
session_start();

// Check if a user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$user_role = $is_logged_in ? $_SESSION['role'] : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LoyaltyHub - Functionalities</title>
    <link rel="stylesheet" href="styles/main.css">
</head>
<body>

    <header class="header">
        <div class="logo-container">
            <img src="images/logo.png" alt="LoyaltyHub Logo">
        </div>
        <nav class="nav">
            <?php if ($is_logged_in): ?>
                <?php if ($user_role === 'admin'): ?>
                    <a href="admin.php">Home</a>
                    <a href="functionalities.php">Functionalities</a>
                    <a href="help.php">Help</a>
                    <a href="admin.php" class="btn">Dashboard</a>
                <?php else: ?>
                    <a href="user.php">Home</a>
                    <a href="functionalities.php">Functionalities</a>
                    <a href="help.php">Help</a>
                    <a href="user.php" class="btn">My Profile</a>
                <?php endif; ?>
                <a href="logout.php" class="btn">Logout</a>
            <?php else: ?>
                <a href="index.php">Home</a>
                <a href="functionalities.php">Functionalities</a>
                <a href="help.php">Help</a>
                <a href="login.php" class="btn">Login</a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="container">
        <h1>Our System's Core Functionalities</h1>
        <p>LoyaltyHub offers a comprehensive set of features for both administrators and ordinary users.</p>
        
        <div class="list-group">
            <h3>For Users:</h3>
            <ul>
                <li><strong>User Dashboard:</strong> A personalized home page after logging in.</li>
                <li><strong>Company Selection:</strong> Easily select and view loyalty points for a specific company.</li>
                <li><strong>Loyalty Point Tracking:</strong> View and track your current loyalty point balance.</li>
                <li><strong>Secure Access:</strong> Login and logout functionality to protect your data.</li>
            </ul>
        </div>

        <div class="list-group">
            <h3>For Administrators:</h3>
            <ul>
                <li><strong>Admin Dashboard:</strong> A central hub for all administrative tasks.</li>
                <li><strong>User Management:</strong> Add, view, edit, and delete user accounts.</li>
                <li><strong>Reports:</strong> A simple overview of all transactions in the system.</li>
                <li><strong>Role-Based Access:</strong> Only administrators can access sensitive management pages.</li>
            </ul>
        </div>
    </main>
    <footer class="site-footer">
        <p>&copy; 2025 Loyalty Rewards Program. All rights reserved.</p>
    </footer>
</body>
</html>
