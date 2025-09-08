<?php
session_start();

// Check if the user is NOT logged in or is NOT an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// DB Connection
require_once 'includes/db_connect.php';

// Messages
$success = '';
$error = '';
$errors = [];

// get User details
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_user'])) {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name = trim($_POST['last_name'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $role = trim($_POST['role'] ?? 'user');
    $selected_companies = $_POST['companies'] ?? [];

    // Validate inputs
    if ($first_name === '')
        $errors[] = "First name is required.";
    if ($last_name === '')
        $errors[] = "Last name is required.";
    if (!preg_match('/^\d{10}$/', $phone))
        $errors[] = "Phone number must be exactly 10 digits.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL))
        $errors[] = "Valid email is required.";
    if (strlen($password) < 6)
        $errors[] = "Password must be at least 6 characters long.";
    if (!preg_match('/[0-9]/', $password))
        $errors[] = "Password must contain at least one number.";
    if ($password !== $confirm_password)
        $errors[] = "Passwords do not match.";
    if ($role !== 'user' && $role !== 'admin')
        $errors[] = "Invalid user role.";

    // Duplicate check for email or phone
    $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR phone = ?");
    $stmt->bind_param("ss", $email, $phone);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        $errors[] = "Email or phone number already registered.";
    }

    if (empty($errors)) {
        try {
            // add user details to database
            $conn->begin_transaction();

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            $stmt = $conn->prepare("
                INSERT INTO users (first_name, last_name, phone, email, password, role)
                VALUES (?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param("ssssss", $first_name, $last_name, $phone, $email, $hashed_password, $role);
            $stmt->execute();
            $user_id = $stmt->insert_id;
            $stmt->close();

            // Insert selected merchants
            if (!empty($selected_companies)) {
                $linkStmt = $conn->prepare("INSERT INTO user_merchants (user_id, merchant_id) VALUES (?, ?)");
                foreach ($selected_companies as $cid) {
                    $cid = (int) $cid;
                    $linkStmt->bind_param("ii", $user_id, $cid);
                    $linkStmt->execute();
                }
                $linkStmt->close();
            }

            $conn->commit();
            $success = "User added successfully!";
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Failed to add user: " . $e->getMessage();
        }
    }
}
?>



<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LoyaltyHub - Add User</title>
    <link rel="stylesheet" href="styles/main.css">
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
        <p>Use the form below to create a new user account and enroll them in loyalty programs.</p>

        <!-- Alerts -->
        <?php if (!empty($success)): ?>
            <div class="alert success"><?= htmlspecialchars($success) ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="alert error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert error">
                <ul style="margin-left:18px; text-align:left;">
                    <?php foreach ($errors as $e): ?>
                        <li><?= htmlspecialchars($e) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Add User Form -->
        <form class="form" method="POST">
            <input type="hidden" name="add_user" value="1" />

            <div class="row">
                <div class="col">
                    <label for="first_name">First Name:</label>
                    <input type="text" id="first_name" name="first_name" required />
                </div>
                <div class="col">
                    <label for="last_name">Last Name:</label>
                    <input type="text" id="last_name" name="last_name" required />
                </div>
            </div>

            <label for="phone">Phone (10 digits):</label>
            <input type="tel" id="phone" name="phone" maxlength="10" required />

            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required />

            <label for="password">Password (min 6 chars, 1 number):</label>
            <input type="password" id="password" name="password" required />

            <label for="confirm_password">Confirm Password:</label>
            <input type="password" id="confirm_password" name="confirm_password" required />

            <label for="role">User Role:</label>
            <select id="role" name="role" required>
                <option value="user">User</option>
                <option value="admin">Admin</option>
            </select>

            <!-- New: Loyalty Programs Checkbox Group -->
            <label style="margin-top: 15px;">Choose Loyalty Programs:</label>
            <div class="checkbox-group">
                <div class="checkbox-item">
                    <input type="checkbox" id="company_1" name="companies[]" value="1">
                    <label for="company_1">Cargills</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" id="company_2" name="companies[]" value="2">
                    <label for="company_2">Keellssuper</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" id="company_3" name="companies[]" value="3">
                    <label for="company_3">Spar</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" id="company_4" name="companies[]" value="4">
                    <label for="company_4">Arpico</label>
                </div>
                <div class="checkbox-item">
                    <input type="checkbox" id="company_5" name="companies[]" value="5">
                    <label for="company_5">Lanka Super</label>
                </div>
            </div>

            <button type="submit">Add User</button>
        </form>
    </main>

    <footer class="site-footer">
        <p>&copy; 2025 Loyalty Rewards Program. All rights reserved.</p>
    </footer>

</body>

</html>