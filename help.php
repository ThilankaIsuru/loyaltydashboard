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
    <title>LoyaltyHub - Help</title>
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
        <h1>Help & FAQ</h1>
        <p>If you have questions about how to use LoyaltyHub, you might find your answer below.</p>
        
        <div class="faq">
            <h3>How do I log in?</h3>
            <p>Go to the login page and enter your username and password. If you are a new user please register.</p>
        </div>

        <div class="faq">
            <h3>How do I see my loyalty points?</h3>
            <p>After logging in, you will be taken to your dashboard. From there, you can see your selected companies and your points.</p>
        </div>
        
        <div class="faq">
            <h3>What if I forgot my password?</h3>
            <p>Please contact an administrator to reset your password.</p>
        </div>

        <div class="faq">
            <h3>How can I contact support?</h3>
            <p>For any issues, please reach out to our administration team.</p>
        </div>
    </main>
    <footer class="site-footer">
        <p>&copy; 2025 Loyalty Rewards Program. All rights reserved.</p>
    </footer>
</body>
</html>
