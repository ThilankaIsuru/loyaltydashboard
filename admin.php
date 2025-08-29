<?php
session_start();

// Check if the user is NOT logged in or is NOT an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
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
            <a href="admin.php">Dashboard</a>
            <a href="logout.php" class="btn">Logout</a>
        </nav>
    </header>

    <main class="container">
        <h1>Welcome, Admin!</h1>
        <p>This is your central hub for all administrative tasks. Please use the links below to manage the system.</p>
        
        <div class="admin-links">
            <a href="add_user.php" class="btn">Add New User</a>
            <a href="manage_users.php" class="btn">Manage Users (View, Edit, Delete)</a>
            <a href="view_reports.php" class="btn">View Reports</a>
        </div>
    </main>

</body>
</html>