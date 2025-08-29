<?php
session_start();

// Security check: ensure only logged-in users can access this page
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Check if the form was submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Include the database connection file
    include 'includes/db_connect.php';

    // Get the user ID from the session and the company ID from the form
    $user_id = $_SESSION['user_id'];
    $company_id = $_POST['company_id'];

    // Prepare and execute the SQL query to update the user's company
    $sql = "UPDATE users SET company_id = ? WHERE user_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $company_id, $user_id);

    if ($stmt->execute()) {
        // Update successful
        // You can add a success message to a session variable if you want
    } else {
        // Update failed
        // You can add an error message to a session variable
    }

    $stmt->close();
    $conn->close();
}

// Redirect the user back to the user dashboard
header("Location: user_dashboard.php");
exit();
?>