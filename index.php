<?php
session_start();

// Check if a user is logged in
if (isset($_SESSION['user_id'])) {
    // If logged in, redirect to the appropriate dashboard
    if ($_SESSION['user_type'] == 'admin') {
        header("Location: admin.php");
        exit();
    } else {
        header("Location: user_dashboard.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LoyaltyHub - Welcome</title>
    <link rel="stylesheet" href="styles/main.css">
</head>
<body>

    <header class="header">
        <div class="logo-container">
            <img src="images/logo.png" alt="LoyaltyHub Logo">
        </div>
        <nav class="nav">
            <a href="login.php" class="btn">Login</a>
        </nav>
    </header>

    <main class="container">
        <h1>Welcome to LoyaltyHub!</h1>
        <p>Your one-stop solution for managing and tracking customer loyalty rewards. Easily view your points, manage companies, and more.</p>
        <p>Ready to get started? Log in to your account to view your rewards and access our features.</p>
        <a href="login.php" class="btn">Get Started Now</a>
    </main>

</body>
</html>