<?php
session_start();

// Check if the user is NOT logged in or is NOT an ordinary user
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'ordinary') {
    header("Location: login.php");
    exit();
}

// Include the database connection file to fetch user data
include 'includes/db_connect.php';

// Fetch the current user's details from the database
$user_id = $_SESSION['user_id'];
$sql = "SELECT username, company_id FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

$username = $user['username'];
$user_company_id = $user['company_id'];

// Fetch all companies to populate the dropdown
$sql_companies = "SELECT company_id, company_name FROM companies";
$result_companies = $conn->query($sql_companies);

// Close the database connection
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LoyaltyHub - User Dashboard</title>
    <link rel="stylesheet" href="styles/main.css">
</head>
<body>

    <header class="header">
        <div class="logo-container">
            <img src="images/logo.png" alt="LoyaltyHub Logo">
        </div>
        <nav class="nav">
            <a href="user_dashboard.php">Dashboard</a>
            <a href="logout.php" class="btn">Logout</a>
        </nav>
    </header>

    <main class="container">
        <h1>Welcome, <?php echo htmlspecialchars($username); ?>!</h1>
        <p>This is your personal dashboard. Here you can view your loyalty points and select your company.</p>

        <form action="update_company.php" method="POST" class="form-group">
            <label for="company">Select Your Company:</label>
            <select name="company_id" id="company">
                <?php while ($row = $result_companies->fetch_assoc()): ?>
                    <option value="<?php echo htmlspecialchars($row['company_id']); ?>"
                        <?php if ($row['company_id'] == $user_company_id) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($row['company_name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
            <button type="submit" class="btn">Update Company</button>
        </form>

        <div class="loyalty-points">
            <h2>Your Loyalty Points:</h2>
            <?php
            // This is a placeholder. We will fetch and display the points in a later step.
            echo "<p>Please select a company to view your points.</p>";
            ?>
        </div>
    </main>
</body>
</html>