<?php
session_start();

// Check if the user is NOT logged in or is NOT an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'includes/db_connect.php';

$message = '';
$message_type = '';

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $user_type = $_POST['user_type'];
    
    // --- THIS IS THE FIX ---
    // Check if the company_id is an empty string from the "None" option
    // If so, set it to NULL for the database insertion
    $company_id = (!empty($_POST['company_id'])) ? $_POST['company_id'] : NULL;

    $sql = "INSERT INTO users (username, password, user_type, company_id) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    
    // The bind_param for company_id must be 'i' (integer) if it's not null, or we can use 's' for string
    // Let's use 'i' and ensure our variable is NULL or an integer.
    // The previous code had a small bug here. This new code is more robust.
    $stmt->bind_param("sssi", $username, $password, $user_type, $company_id);
    
    if ($stmt->execute()) {
        $message = "User '{$username}' added successfully!";
        $message_type = 'success';
    } else {
        $message = "Error: " . $stmt->error;
        $message_type = 'error';
    }

    $stmt->close();
}

// Fetch all companies to populate the dropdown
$sql_companies = "SELECT company_id, company_name FROM companies";
$result_companies = $conn->query($sql_companies);

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LoyaltyHub - Add User</title>
    <link rel="stylesheet" href="styles/main.css">
    <style>
        .message.success { color: green; }
        .message.error { color: red; }
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
        <h1>Add New User</h1>
        
        <?php if ($message): ?>
            <p class="message <?php echo $message_type; ?>"><?php echo $message; ?></p>
        <?php endif; ?>

        <form action="add_user.php" method="POST">
            <div class="form-group">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required>
            </div>
            <div class="form-group">
                <label for="password">Password:</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="user_type">User Type:</label>
                <select id="user_type" name="user_type">
                    <option value="ordinary">Ordinary User</option>
                    <option value="admin">Administrator</option>
                </select>
            </div>
            <div class="form-group">
                <label for="company_id">Company:</label>
                <select id="company_id" name="company_id">
                    <option value="">(None)</option>
                    <?php while ($row = $result_companies->fetch_assoc()): ?>
                        <option value="<?php echo htmlspecialchars($row['company_id']); ?>">
                            <?php echo htmlspecialchars($row['company_name']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <button type="submit" class="btn">Add User</button>
        </form>
    </main>
</body>
</html>