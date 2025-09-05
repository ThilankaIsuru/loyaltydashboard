<?php
session_start();

// Check if the user is NOT logged in or is NOT an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    // Redirect to the login page
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LoyaltyHub - Admin Dashboard</title>
    <link rel="stylesheet" href="styles/main.css">
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
            <a href="admin.php" class="btn">Dashboard</a>
            <a href="logout.php" class="btn">Logout</a>
        </nav>
    </header>

    <div class="container">
        <h1>Admin Dashboard</h1>
        <p>Welcome to the admin panel. Use the links below to manage the loyalty program.</p>

        <div class="dashboard-grid">
            <a href="add_user.php" class="dashboard-link">
                <div class="dashboard-icon" style="background-color: #e0f2fe;">
                    <span class="icon-char" style="color: #0c4a6e;">&#x2795;</span>
                </div>
                <h2>Add New User</h2>
                <p>Create new user accounts and profiles for the loyalty program.</p>
            </a>

            <a href="manage_users.php" class="dashboard-link">
                <div class="dashboard-icon" style="background-color: #fef3e3;">
                    <span class="icon-char" style="color: #92400e;">&#9776;</span>
                </div>
                <h2>Manage Users</h2>
                <p>View, Edit, or delete existing user accounts and their details.</p>
            </a>

            <a href="view_reports.php" class="dashboard-link">
                <div class="dashboard-icon" style="background-color: #e6f9f5;">
                    <span class="icon-char" style="color: #065f46;">&#128196;</span>
                </div>
                <h2>View Reports</h2>
                <p>Access detailed reports on user activity and loyalty program performance.</p>
            </a>
        </div>
    </div>

    <footer class="site-footer">
        <p>&copy; 2025 Loyalty Rewards Program. All rights reserved.</p>
    </footer>

</body>

</html>