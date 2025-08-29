<?php
session_start();
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
            <a href="index.php">Home</a>
            <a href="login.php" class="btn">Login</a>
        </nav>
    </header>

    <main class="container">
        <h1>Help & FAQ</h1>
        <p>If you have questions about how to use LoyaltyHub, you might find your answer below.</p>
        
        <div class="faq">
            <h3>How do I log in?</h3>
            <p>Go to the login page and enter your assigned username and password. If you are a student, use the default username `uoc` and password `uoc`.</p>
        </div>

        <div class="faq">
            <h3>How do I see my loyalty points?</h3>
            <p>After logging in, you will be taken to your dashboard. From there, you can select your company from the dropdown menu to view your points.</p>
        </div>
        
        <div class="faq">
            <h3>What if I forgot my password?</h3>
            <p>For this system, please contact an administrator to reset your password.</p>
        </div>

        <div class="faq">
            <h3>How can I contact support?</h3>
            <p>For any issues, please reach out to our administration team. (Replace with a specific contact method if needed.)</p>
        </div>
    </main>
</body>
</html>