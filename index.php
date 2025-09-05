<?php
session_start();

// Check if a user is logged in
$is_logged_in = isset($_SESSION['user_id']);
$user_role = $is_logged_in ? $_SESSION['role'] : '';

// If a user is logged in, redirect them to their specific dashboard
if ($is_logged_in) {
    if ($user_role === 'admin') {
        header("Location: admin.php");
    } else {
        // Redirect ordinary users to their dashboard
        header("Location: user.php");
    }
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LoyaltyHub - Home</title>
    <link rel="stylesheet" href="styles/main.css">
    <style>
        .hero-section {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            gap: 40px;
            padding: 40px 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
        }
        .hero-content {
            max-width: 600px;
        }
        .hero-image {
            width: 100%;
            max-width: 500px;
        }
        .hero-image img {
            width: 100%;
            height: auto;
            border-radius: 8px;
        }
        /* Responsive adjustments */
        @media (min-width: 768px) {
            .hero-section {
                flex-direction: row;
                text-align: left;
                padding: 60px;
            }
            .hero-content {
                flex: 1;
                padding-right: 40px;
            }
            .hero-image {
                flex: 1;
                max-width: none;
            }
        }
        .content-section {
            padding: 40px 20px;
            background-color: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
            text-align: center;
        }
    </style>
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
        <section class="hero-section">
            <div class="hero-content">
                <h1>Welcome to LoyaltyHub!</h1>
                <p>Your one-stop solution for managing and tracking customer loyalty rewards. Easily view your points, manage companies, and more.</p>
                <p>Ready to get started? Log in to your account to view your rewards and access our features.</p>
                <?php if (!$is_logged_in): ?>
                    <a href="login.php" class="btn">Get Started Now</a>
                <?php endif; ?>
            </div>
            <div class="hero-image">
                <img src="images/home.png" alt="A person managing loyalty points on a digital device">
            </div>
        </section>
        
        <section class="content-section">
            <h2>About Our Platform</h2>
            <p>LoyaltyHub is designed to simplify your life by bringing all your loyalty programs under a single, easy-to-use application. No more fumbling with multiple cards or apps—just one convenient hub for all your rewards.</p>
        </section>
    </main>
    <footer class="site-footer">
        <p>&copy; 2025 Loyalty Rewards Program. All rights reserved.</p>
    </footer>
</body>
</html>
