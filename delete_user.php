<?php
session_start();

// Security check: only allow admins to perform this action
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Check if a user ID was provided in the URL
if (isset($_GET['id'])) {
    include 'includes/db_connect.php';

    // Get the user ID from the URL and sanitize it
    $user_id = $_GET['id'];

    // Prepare and execute the SQL DELETE query
    $sql = "DELETE FROM users WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $user_id);
    
    if ($stmt->execute()) {
        // Deletion successful
        // You could add a session message here if you want
    } else {
        // Deletion failed
        // You could add a session message here
    }

    $stmt->close();
    $conn->close();
}

// Redirect back to the manage users page
header("Location: manage_users.php");
exit();
?>