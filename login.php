<?php
session_start();

// Check if the user is already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_type'] == 'admin') {
        header("Location: admin.php");
        exit();
    } else {
        header("Location: user_dashboard.php");
        exit();
    }
}

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    include 'includes/db_connect.php';

    $username = $_POST['username'];
    $password = $_POST['password'];

    // Check for the default 'uoc' user first
    if ($username === 'uoc' && $password === 'uoc') {
        // You need to find the user_id for 'uoc' to use the session.
        // For simplicity now, you can hardcode a user_id or get it from the database.
        // Let's assume uoc has user_id = 1 and user_type = 'ordinary'
        $_SESSION['user_id'] = 1; 
        $_SESSION['user_type'] = 'ordinary';
        header("Location: user_dashboard.php");
        exit();
    }

    // Prepare and execute a SQL query for other users
    $sql = "SELECT user_id, password, user_type FROM users WHERE username = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        
        // This is where you would ideally use password_verify()
        if ($row['password'] == $password) { // Using a simple comparison for now as discussed
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['user_type'] = $row['user_type'];
            
            if ($row['user_type'] == 'admin') {
                header("Location: admin.php");
                exit();
            } else {
                header("Location: user_dashboard.php");
                exit();
            }
        } else {
            $error = "Invalid username or password.";
        }
    } else {
        $error = "Invalid username or password.";
    }

    $stmt->close();
    $conn->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LoyaltyHub - Login</title>
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
        </nav>
    </header>

    <main class="login-container">
        <h2>Login to your Account</h2>
        <?php if ($error): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>
        <form action="login.php" method="POST" class="login-form">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <button type="submit" class="btn">Login</button>
        </form>
    </main>
</body>
</html>