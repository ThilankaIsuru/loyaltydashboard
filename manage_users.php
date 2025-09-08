<?php
session_start();

// Check if the user is NOT logged in or is NOT an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

/* ---------- DB Connection ---------- */
require_once 'includes/db_connect.php'; // gives $conn (MySQLi)

/* ---------- Messages & State ---------- */
$success = '';
$error = '';
$errors = [];
$users = [];
$merchants = [];

/* ---------- Fetch All Merchants (for checkboxes) ---------- */
try {
    $stmt = $conn->query("SELECT id, name FROM merchants ORDER BY name");
    while ($row = $stmt->fetch_assoc()) {
        $merchants[$row['id']] = $row['name'];
    }
} catch (Exception $e) {
    $error = "Failed to load merchants: " . $e->getMessage();
}

/* ---------- Edit & Delete Logic ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Delete User
    if (isset($_POST['delete_user_id'])) {
        $user_id = (int)$_POST['delete_user_id'];
        try {
            $conn->begin_transaction();
            $stmt = $conn->prepare("DELETE FROM user_merchants WHERE user_id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();

            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->bind_param("i", $user_id);
            $stmt->execute();
            $stmt->close();

            $conn->commit();
            $success = "User deleted successfully!";
        } catch (Exception $e) {
            if ($conn->errno) $conn->rollback();
            $error = "Failed to delete user: " . $e->getMessage();
        }
    }

    // Update User
    if (isset($_POST['update_user_id'])) {
        $user_id = (int)$_POST['update_user_id'];
        $first_name = trim($_POST['first_name'] ?? '');
        $last_name = trim($_POST['last_name'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $role = trim($_POST['role'] ?? 'user');
        $selected_companies = $_POST['companies'] ?? [];

        // Validate inputs
        if ($first_name === '') $errors[] = "First name is required.";
        if ($last_name === '') $errors[] = "Last name is required.";
        if (!preg_match('/^\d{10}$/', $phone)) $errors[] = "Phone number must be exactly 10 digits.";
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
        
        if (!empty($password)) {
            if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters long.";
            if (!preg_match('/[0-9]/', $password)) $errors[] = "Password must contain at least one number.";
            if ($password !== $confirm_password) $errors[] = "Passwords do not match.";
        }
        
        if ($role !== 'user' && $role !== 'admin') $errors[] = "Invalid user role.";

        // Duplicate check on email/phone, excluding the current user
        $stmt = $conn->prepare("SELECT COUNT(*) FROM users WHERE (email = ? OR phone = ?) AND id != ?");
        $stmt->bind_param("ssi", $email, $phone, $user_id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        if ($count > 0) {
            $errors[] = "Email or phone number already registered to another user.";
        }

        if (empty($errors)) {
            try {
                $conn->begin_transaction();

                $sql = "UPDATE users SET first_name = ?, last_name = ?, phone = ?, email = ?, role = ?";
                $params = [$first_name, $last_name, $phone, $email, $role];
                $types = "sssss";

                if (!empty($password)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $sql .= ", password = ?";
                    $params[] = $hashed_password;
                    $types .= "s";
                }
                $sql .= " WHERE id = ?";
                $params[] = $user_id;
                $types .= "i";

                $stmt = $conn->prepare($sql);
                $stmt->bind_param($types, ...$params);
                $stmt->execute();
                $stmt->close();

                // Update loyalty programs
                $stmt = $conn->prepare("DELETE FROM user_merchants WHERE user_id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $stmt->close();

                if (!empty($selected_companies)) {
                    $linkStmt = $conn->prepare("INSERT INTO user_merchants (user_id, merchant_id) VALUES (?, ?)");
                    foreach ($selected_companies as $cid) {
                        $cid = (int)$cid;
                        $linkStmt->bind_param("ii", $user_id, $cid);
                        $linkStmt->execute();
                    }
                    $linkStmt->close();
                }

                $conn->commit();
                $success = "User updated successfully!";
            } catch (Exception $e) {
                if ($conn->errno) $conn->rollback();
                $error = "Failed to update user: " . $e->getMessage();
            }
        }
    }
}

/* ---------- Fetch All Users & Enrolled Programs ---------- */
try {
    $sql = "
        SELECT
            u.id, u.first_name, u.last_name, u.phone, u.email, u.role,
            GROUP_CONCAT(m.name ORDER BY m.name SEPARATOR ', ') AS enrolled_programs,
            GROUP_CONCAT(m.id ORDER BY m.name SEPARATOR ',') AS enrolled_ids
        FROM
            users u
        LEFT JOIN
            user_merchants um ON u.id = um.user_id
        LEFT JOIN
            merchants m ON um.merchant_id = m.id
        GROUP BY
            u.id
        ORDER BY
            u.id DESC
    ";
    $stmt = $conn->query($sql);
    while ($row = $stmt->fetch_assoc()) {
        $users[] = $row;
    }
    $stmt->close();
} catch (Exception $e) {
    $error = "Failed to fetch users: " . $e->getMessage();
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LoyaltyHub - Manage Users</title>
    <link rel="stylesheet" href="styles/main.css">
    <style>
        .edit-form-container {
            display: none;
            margin-top: 20px;
            border: 1px solid #ccc;
            padding: 20px;
            border-radius: 8px;
            background-color: #f9f9f9;
        }

        /* Added Styles for a more structured table */
        .table-container {
            overflow-x: auto;
            margin-top: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            min-width: 600px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
            color: #4a4a4a;
            font-weight: bold;
        }
        tbody tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .btn-edit, .btn-delete {
            padding: 8px 12px;
            font-size: 0.9em;
            margin-right: 5px;
            cursor: pointer;
            border-radius: 5px;
            border: none;
        }
        .btn-edit { background-color: #4CAF50; color: white; }
        .btn-delete { background-color: #f44336; color: white; }
        .btn-edit:hover { background-color: #45a049; }
        .btn-delete:hover { background-color: #da190b; }
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
        <h1>Manage Users</h1>
        <p>View, edit, or delete user accounts.</p>

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

        <!-- Users Table -->
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Role</th>
                        <th>Enrolled Programs</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($users)): ?>
                        <tr><td colspan="6" style="text-align: center;">No users found.</td></tr>
                    <?php else: ?>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?= htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) ?></td>
                                <td><?= htmlspecialchars($user['email']) ?></td>
                                <td><?= htmlspecialchars($user['phone']) ?></td>
                                <td><?= htmlspecialchars($user['role']) ?></td>
                                <td><?= htmlspecialchars($user['enrolled_programs'] ?? 'None') ?></td>
                                <td>
                                    <button class="btn btn-edit" onclick="showEditForm(<?= htmlspecialchars(json_encode($user)) ?>)">Edit</button>
                                    <form method="POST" style="display:inline-block;" onsubmit="return confirm('Are you sure you want to delete this user?');">
                                        <input type="hidden" name="delete_user_id" value="<?= htmlspecialchars($user['id']) ?>">
                                        <button type="submit" class="btn btn-delete">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Edit User Form (initially hidden) -->
        <div id="editFormContainer" class="edit-form-container">
            <h2>Edit User</h2>
            <form id="editForm" class="form" method="POST">
                <input type="hidden" name="update_user_id" id="editUserId" />

                <div class="row">
                    <div class="col">
                        <label for="editFirstName">First Name:</label>
                        <input type="text" id="editFirstName" name="first_name" required />
                    </div>
                    <div class="col">
                        <label for="editLastName">Last Name:</label>
                        <input type="text" id="editLastName" name="last_name" required />
                    </div>
                </div>

                <label for="editPhone">Phone (10 digits):</label>
                <input type="tel" id="editPhone" name="phone" maxlength="10" required />

                <label for="editEmail">Email:</label>
                <input type="email" id="editEmail" name="email" required />

                <label for="editPassword">New Password (optional):</label>
                <input type="password" id="editPassword" name="password" />
                <span class="form-help">Leave blank to keep current password.</span>

                <label for="editConfirmPassword">Confirm New Password:</label>
                <input type="password" id="editConfirmPassword" name="confirm_password" />

                <label for="editRole">User Role:</label>
                <select id="editRole" name="role" required>
                    <option value="user">User</option>
                    <option value="admin">Admin</option>
                </select>

                <label style="margin-top: 15px;">Choose Loyalty Programs:</label>
                <div class="checkbox-group">
                    <?php foreach ($merchants as $id => $name): ?>
                        <div class="checkbox-item">
                            <input type="checkbox" id="editCompany_<?= $id ?>" name="companies[]" value="<?= htmlspecialchars($id) ?>">
                            <label for="editCompany_<?= $id ?>"><?= htmlspecialchars($name) ?></label>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="form-actions">
                    <button type="submit">Update User</button>
                    <button type="button" class="btn-cancel" onclick="hideEditForm()">Cancel</button>
                </div>
            </form>
        </div>

    </main>
    <footer class="site-footer">
        <p>&copy; 2025 Loyalty Rewards Program. All rights reserved.</p>
    </footer>

    <script>
        function showEditForm(user) {
            const form = document.getElementById('editFormContainer');
            document.getElementById('editUserId').value = user.id;
            document.getElementById('editFirstName').value = user.first_name;
            document.getElementById('editLastName').value = user.last_name;
            document.getElementById('editPhone').value = user.phone;
            document.getElementById('editEmail').value = user.email;
            document.getElementById('editRole').value = user.role;

            // Uncheck all checkboxes first
            document.querySelectorAll('#editForm .checkbox-group input[type="checkbox"]').forEach(checkbox => {
                checkbox.checked = false;
            });

            // Check the ones the user is enrolled in
            if (user.enrolled_ids) {
                const enrolledIds = user.enrolled_ids.split(',');
                enrolledIds.forEach(id => {
                    const checkbox = document.getElementById('editCompany_' + id);
                    if (checkbox) {
                        checkbox.checked = true;
                    }
                });
            }

            form.style.display = 'block';
            window.scrollTo({ top: form.offsetTop, behavior: 'smooth' });
        }

        function hideEditForm() {
            document.getElementById('editFormContainer').style.display = 'none';
        }
    </script>
</body>
</html>
