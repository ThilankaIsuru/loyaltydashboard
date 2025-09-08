<?php
session_start();

// Redirect to login if not authenticated as a regular user
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

// Database connection
require_once 'includes/db_connect.php'; // provides $conn (MySQLi)

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Profile update
    if (isset($_POST['update_profile'])) {
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name  = trim($_POST['last_name'] ?? '');
        $phone      = trim($_POST['phone'] ?? '');
        $new_password = $_POST['new_password'] ?? '';
        $current_password = $_POST['current_password'] ?? '';

        // Validate phone
        if (!empty($phone) && !preg_match('/^\d{10}$/', $phone)) {
            $error = "Phone number must be exactly 10 digits.";
        }

        if (empty($error)) {
            $update_fields = [];
            $update_params = [];

            // Check for password update
            if (!empty($new_password)) {
                $stmt = $conn->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $stmt->bind_result($current_hash);
                $stmt->fetch();
                $stmt->close();

                if (password_verify($current_password, $current_hash)) {
                    $update_fields[] = "password = ?";
                    $update_params[] = password_hash($new_password, PASSWORD_DEFAULT);
                } else {
                    $error = "Incorrect current password.";
                }
            }

            if (empty($error)) {
                $update_fields[] = "first_name = ?";
                $update_fields[] = "last_name = ?";
                $update_fields[] = "phone = ?";
                $update_params[] = $first_name;
                $update_params[] = $last_name;
                $update_params[] = $phone;

                $update_params[] = $user_id;

                $sql = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = ?";
                $stmt = $conn->prepare($sql);

                // Dynamically bind parameters
                $types = str_repeat('s', count($update_fields)); // 's' for string
                if (!empty($new_password)) $types = 's' . $types; // first param is password
                $types .= 'i'; // for user_id at the end

                $stmt->bind_param(str_repeat('s', count($update_params) - 1) . 'i', ...$update_params);
                $stmt->execute();
                $stmt->close();

                $success = "Your information has been updated successfully.";
            }
        }
    }

    // Merchant update
    if (isset($_POST['update_merchants'])) {
        $merchants_to_add = $_POST['merchants'] ?? [];

        $conn->begin_transaction();

        try {
            // Delete old associations
            $stmt_delete = $conn->prepare("DELETE FROM user_merchants WHERE user_id = ?");
            $stmt_delete->bind_param("i", $user_id);
            $stmt_delete->execute();
            $stmt_delete->close();

            // Insert new associations
            $stmt_insert = $conn->prepare("INSERT INTO user_merchants (user_id, merchant_id) VALUES (?, ?)");
            foreach ($merchants_to_add as $merchant_id) {
                $merchant_id = (int)$merchant_id;
                $stmt_insert->bind_param("ii", $user_id, $merchant_id);
                $stmt_insert->execute();
            }
            $stmt_insert->close();

            $conn->commit();
            $success = "Your merchant list has been updated successfully.";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Failed to update merchants: " . $e->getMessage();
        }
    }
}

// Fetch user data
$stmt = $conn->prepare("SELECT first_name, last_name, phone, email FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

// Fetch all merchants
$all_merchants = [];
$result = $conn->query("SELECT id, name FROM merchants ORDER BY name ASC");
while ($row = $result->fetch_assoc()) {
    $all_merchants[] = $row;
}

// Fetch user's enrolled merchants
$user_merchants = [];
$stmt = $conn->prepare("SELECT merchant_id FROM user_merchants WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $user_merchants[] = $row['merchant_id'];
}
$stmt->close();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LoyaltyHub - User Dashboard</title>
    <link rel="stylesheet" href="styles/main.css">
    <style>
        .profile-container, .manage-container {
            max-width: 600px;
            margin: 40px auto;
            background: #fff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }
        .profile-container h1, .manage-container h1 {
            text-align: center;
            margin-bottom: 20px;
        }
        .profile-form label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .profile-form input[type="text"],
        .profile-form input[type="email"],
        .profile-form input[type="password"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 20px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .profile-form input[readonly] {
            background-color: #f0f0f0;
            cursor: not-allowed;
        }
        .profile-form button, .manage-container button {
            width: 100%;
            padding: 12px;
            border: none;
            border-radius: 4px;
            background-color: #3f90ff;
            color: white;
            font-size: 1.1em;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .profile-form button:hover, .manage-container button:hover {
            background-color: #2e7af3;
        }
        .message {
            text-align: center;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        .success {
            background-color: #d4edda;
            color: #155724;
            border-color: #c3e6cb;
        }
        .error {
            background-color: #f8d7da;
            color: #721c24;
            border-color: #f5c6cb;
        }
        .merchant-list {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        .merchant-item {
            display: flex;
            align-items: center;
            gap: 10px;
            background-color: #f7f7f7;
            padding: 10px;
            border-radius: 5px;
        }
        .merchant-item img {
            width: 40px;
            height: 40px;
            border-radius: 50%;
        }
        .merchant-item label {
            font-weight: bold;
        }
    </style>
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
            <a href="user_dashboard.php">My Profile</a>
            <a href="logout.php" class="btn">Logout</a>
        </nav>
    </header>

    <main class="container">
        <div class="profile-container">
            <h1>My Profile</h1>
            <?php if (!empty($success)): ?>
                <div class="message success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="message error"><?php echo $error; ?></div>
            <?php endif; ?>
            <form action="user_dashboard.php" method="POST" class="profile-form">
                <label for="first_name">First Name</label>
                <input type="text" id="first_name" name="first_name" value="<?php echo htmlspecialchars($user['first_name']); ?>" required>

                <label for="last_name">Last Name</label>
                <input type="text" id="last_name" name="last_name" value="<?php echo htmlspecialchars($user['last_name']); ?>" required>

                <label for="phone">Phone Number</label>
                <input type="text" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>

                <label for="email">Email</label>
                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>

                <hr style="margin: 20px 0;">

                <p>Change Password (optional)</p>
                <label for="current_password">Current Password</label>
                <input type="password" id="current_password" name="current_password">

                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password">

                <button type="submit" name="update_profile">Update Profile</button>
            </form>
        </div>

        <div class="manage-container">
            <h1>Manage My Merchants</h1>
            <p>Select the merchants you want to join. Unchecking a merchant will remove it from your list.</p>
            <form action="user_dashboard.php" method="POST">
                <div class="merchant-list">
                    <?php foreach ($all_merchants as $merchant): ?>
                        <div class="merchant-item">
                            <input type="checkbox" 
                                   id="merchant_<?= htmlspecialchars($merchant['id']) ?>" 
                                   name="merchants[]" 
                                   value="<?= htmlspecialchars($merchant['id']) ?>"
                                   <?= in_array($merchant['id'], $user_merchants) ? 'checked' : '' ?>>
                            <img src="images/<?= htmlspecialchars($merchant['id']) ?>.png" 
                                 alt="<?= htmlspecialchars($merchant['name']) ?>">
                            <label for="merchant_<?= htmlspecialchars($merchant['id']) ?>">
                                <?= htmlspecialchars($merchant['name']) ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
                <button type="submit" name="update_merchants" class="btn-update">Update Merchants</button>
            </form>
        </div>
    </main>

    <footer class="site-footer">
        <p>&copy; 2025 Loyalty Rewards Program. All rights reserved.</p>
    </footer>
</body>
</html>
