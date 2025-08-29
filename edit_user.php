<?php
session_start();

// Security check: only allow admins to access this page
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

include 'includes/db_connect.php';

$message = '';
$user_to_edit = null;

// PART 1: Fetch user data for pre-filling the form
if (isset($_GET['id'])) {
    $user_id = $_GET['id'];
    $sql = "SELECT user_id, username, user_type, company_id FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $user_to_edit = $result->fetch_assoc();
    } else {
        $message = "User not found.";
    }
}

// PART 2: Handle form submission to update the user
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['user_id'])) {
    $user_id = $_POST['user_id'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $user_type = $_POST['user_type'];
    
    // Handle the empty company_id for the database
    $company_id = (!empty($_POST['company_id'])) ? $_POST['company_id'] : NULL;

    // Check if the password field is not empty
    if (!empty($password)) {
        $sql = "UPDATE users SET username=?, password=?, user_type=?, company_id=? WHERE user_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssii", $username, $password, $user_type, $company_id, $user_id);
    } else {
        // Update without changing the password
        $sql = "UPDATE users SET username=?, user_type=?, company_id=? WHERE user_id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssii", $username, $user_type, $company_id, $user_id);
    }

    if ($stmt->execute()) {
        $message = "User updated successfully!";
    } else {
        $message = "Error updating user: " . $stmt->error;
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
    <title>LoyaltyHub - Edit User</title>
    <link rel="stylesheet" href="styles/main.css">
</head>
<body>

    <header class="header">
        <div class="logo-container">
            <img src="images/logo.png" alt="LoyaltyHub Logo">
        </div>
        <nav class="nav">
            <a href="admin.php">Dashboard</a>
            <a href="manage_users.php">Manage Users</a>
            <a href="logout.php" class="btn">Logout</a>
        </nav>
    </header>

    <main class="container">
        <h1>Edit User</h1>
        
        <?php if ($message): ?>
            <p style="color: green;"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <?php if ($user_to_edit): ?>
            <form action="edit_user.php" method="POST">
                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_to_edit['user_id']); ?>">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user_to_edit['username']); ?>" required>
                </div>
                <div class="form-group">
                    <label for="password">Password (leave blank to keep current):</label>
                    <input type="password" id="password" name="password">
                </div>
                <div class="form-group">
                    <label for="user_type">User Type:</label>
                    <select id="user_type" name="user_type">
                        <option value="ordinary" <?php if ($user_to_edit['user_type'] == 'ordinary') echo 'selected'; ?>>Ordinary User</option>
                        <option value="admin" <?php if ($user_to_edit['user_type'] == 'admin') echo 'selected'; ?>>Administrator</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="company_id">Company:</label>
                    <select id="company_id" name="company_id">
                        <option value="">(None)</option>
                        <?php while ($row = $result_companies->fetch_assoc()): ?>
                            <option value="<?php echo htmlspecialchars($row['company_id']); ?>"
                                <?php if ($row['company_id'] == $user_to_edit['company_id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($row['company_name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                <button type="submit" class="btn">Update User</button>
            </form>
        <?php else: ?>
            <p>No user selected for editing. Please go back to the <a href="manage_users.php">Manage Users</a> page.</p>
        <?php endif; ?>
    </main>

</body>
</html>