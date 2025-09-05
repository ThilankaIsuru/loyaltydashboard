<?php
session_start();

// Database connection
$host = 'localhost';
$dbname = 'loyalty_rewards';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Redirect if not a logged-in user
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_profile'])) {
        // Handle profile update
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $new_password = $_POST['new_password'] ?? '';
        $current_password = $_POST['current_password'] ?? '';

        // Validate phone number
        if (!empty($phone) && !preg_match('/^[0-9]{10}$/', $phone)) {
            $error = "Phone number must be exactly 10 digits.";
        }

        if (empty($error)) {
            $update_fields = [];
            $update_params = [];

            // Check if a password is being updated
            if (!empty($new_password)) {
                // Check current password before allowing an update
                $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
                $stmt->execute([$_SESSION['user_id']]);
                $current_hash = $stmt->fetchColumn();

                if (password_verify($current_password, $current_hash)) {
                    $update_fields[] = 'password = ?';
                    $update_params[] = password_hash($new_password, PASSWORD_DEFAULT);
                } else {
                    $error = "Incorrect current password.";
                }
            }

            // Add other fields if valid
            if (empty($error)) {
                $update_fields[] = 'first_name = ?';
                $update_params[] = $first_name;
                $update_fields[] = 'last_name = ?';
                $update_params[] = $last_name;
                $update_fields[] = 'phone = ?';
                $update_params[] = $phone;

                $update_params[] = $_SESSION['user_id'];

                $update_query = "UPDATE users SET " . implode(', ', $update_fields) . " WHERE id = ?";
                $stmt = $pdo->prepare($update_query);
                $stmt->execute($update_params);

                $success = "Your information has been updated successfully.";
            }
        }
    } elseif (isset($_POST['update_merchants'])) {
        // Handle merchant update
        try {
            // Begin a transaction
            $pdo->beginTransaction();

            // 1. Delete all existing merchant associations for the user
            $stmt_delete = $pdo->prepare("DELETE FROM user_merchants WHERE user_id = ?");
            $stmt_delete->execute([$user_id]);

            // 2. Insert the newly selected merchants
            $merchants_to_add = $_POST['merchants'] ?? [];
            $stmt_insert = $pdo->prepare("INSERT INTO user_merchants (user_id, merchant_id) VALUES (?, ?)");
            
            foreach ($merchants_to_add as $merchant_id) {
                $stmt_insert->execute([$user_id, $merchant_id]);
            }

            // Commit the transaction
            $pdo->commit();
            $success = "Your merchant list has been updated successfully.";

        } catch (Exception $e) {
            $pdo->rollBack();
            $error = "Failed to update merchants: " . $e->getMessage();
        }
    }
}

// Get user data from the database
$stmt = $pdo->prepare("SELECT first_name, last_name, phone, email FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

// Fetch all available merchants
$stmt_all_merchants = $pdo->query("SELECT id, name FROM merchants ORDER BY name ASC");
$all_merchants = $stmt_all_merchants->fetchAll();

// Fetch merchants the user has already joined
$stmt_user_merchants = $pdo->prepare("SELECT merchant_id FROM user_merchants WHERE user_id = ?");
$stmt_user_merchants->execute([$user_id]);
$user_merchants = $stmt_user_merchants->fetchAll(PDO::FETCH_COLUMN, 0); // Fetch merchant IDs into a flat array

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
