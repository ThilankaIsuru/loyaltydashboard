<?php
session_start();

/* ---------- DB ---------- */
$host = 'localhost';
$dbname = 'loyalty_rewards';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

/* ---------- Messages & State ---------- */
$success = '';
$error = '';
$errors = [];
$defaultTab = 'login'; // page opens on Login by default

// If we redirected after registration, show success and stay on Login tab
if (isset($_GET['registered']) && $_GET['registered'] === '1') {
    $success = "Registration successful! You can now log in.";
    $defaultTab = 'login';
}

/* ---------- Registration ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register'])) {
    $first_name = trim($_POST['first_name'] ?? '');
    $last_name  = trim($_POST['last_name'] ?? '');
    $phone      = trim($_POST['phone'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $password   = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    $selected_companies = $_POST['companies'] ?? [];

    // Validate
    if ($first_name === '') $errors[] = "First name is required.";
    if ($last_name === '')  $errors[] = "Last name is required.";
    if (!preg_match('/^\d{10}$/', $phone)) $errors[] = "Phone number must be exactly 10 digits.";
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
    if (strlen($password) < 6) $errors[] = "Password must be at least 6 characters long.";
    if (!preg_match('/[0-9]/', $password)) $errors[] = "Password must contain at least one number.";
    if ($password !== $confirm_password) $errors[] = "Passwords do not match.";

    // Duplicate check
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ? OR phone = ?");
    $stmt->execute([$email, $phone]);
    if ($stmt->fetchColumn() > 0) {
        $errors[] = "Email or phone number already registered.";
    }

    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Insert user
            $stmt = $pdo->prepare("
                INSERT INTO users (first_name, last_name, phone, email, password, role)
                VALUES (?, ?, ?, ?, ?, 'user')
            ");
            $stmt->execute([$first_name, $last_name, $phone, $email, $hashed_password]);

            $user_id = (int)$pdo->lastInsertId();

            // Insert selected merchants
            if (!empty($selected_companies)) {
                $checkMerchant = $pdo->prepare("SELECT COUNT(*) FROM merchants WHERE id = ?");
                $link = $pdo->prepare("INSERT INTO user_merchants (user_id, merchant_id) VALUES (?, ?)");

                foreach ($selected_companies as $cid) {
                    $cid = (int)$cid;
                    if ($cid <= 0) continue;

                    $checkMerchant->execute([$cid]);
                    if ($checkMerchant->fetchColumn() > 0) {
                        $link->execute([$user_id, $cid]);
                    }
                }
            }

            $pdo->commit();

            // Redirect with success flag
            header("Location: " . basename(__FILE__) . "?registered=1");
            exit;
        } catch (Exception $e) {
            if ($pdo->inTransaction()) $pdo->rollBack();
            $error = "Registration failed: " . $e->getMessage();
            $defaultTab = 'register';
        }
    } else {
        $defaultTab = 'register';
    }
}

/* ---------- Login ---------- */
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT id, first_name, last_name, password, role FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id']    = (int)$user['id'];
        $_SESSION['role']       = trim($user['role']); // Trim the role to prevent whitespace issues
        $_SESSION['full_name'] = $user['first_name'] . ' ' . $user['last_name'];

        if ($_SESSION['role'] === 'admin') {
            header("Location: admin.php");
        } else {
            header("Location: user.php");
        }
        exit;
    } else {
        $error = "Invalid email or password.";
        $defaultTab = 'login';
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Loyalty Rewards Program | Login & Register</title>
    <link rel="stylesheet" href="styles/main.css" />
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

    <div class="container">
        <h1>Loyalty Rewards Program</h1>
        <p>Welcome! Please log in or register.</p>

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

        <!-- Tabs -->
        <div class="tabs">
            <button class="tab-btn" id="tabLogin" onclick="showTab('login')">Login</button>
            <button class="tab-btn" id="tabRegister" onclick="showTab('register')">Register</button>
        </div>

        <!-- Login Form -->
        <form id="loginForm" class="form" method="POST">
            <input type="hidden" name="login" value="1" />
            <label for="login_email">Email:</label>
            <input type="email" id="login_email" name="email" required />

            <label for="login_password">Password:</label>
            <input type="password" id="login_password" name="password" required />

            <button type="submit">Log In</button>
        </form>

        <!-- Registration Form -->
        <form id="registerForm" class="form" method="POST">
            <input type="hidden" name="register" value="1" />

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

            <label>Choose Loyalty Programs:</label>
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

            <button type="submit">Register</button>
        </form>
    </div>

    <script>
    // Tab logic
    function showTab(tabName) {
        const loginForm = document.getElementById('loginForm');
        const registerForm = document.getElementById('registerForm');
        const loginBtn = document.getElementById('tabLogin');
        const registerBtn = document.getElementById('tabRegister');
    
        loginForm.style.display = 'none';
        registerForm.style.display = 'none';
    
        loginBtn.classList.remove('active');
        registerBtn.classList.remove('active');
    
        if (tabName === 'login') {
            loginForm.style.display = 'block';
            loginBtn.classList.add('active');
        } else {
            registerForm.style.display = 'block';
            registerBtn.classList.add('active');
        }
    }
    
    // Set initial tab from PHP
    (function() {
        const initial = "<?php echo $defaultTab; ?>";
        showTab(initial);
    })();
    </script>

    <footer class="site-footer">
        <p>&copy; 2025 Loyalty Rewards Program. All rights reserved.</p>
    </footer>
</body>
</html>
