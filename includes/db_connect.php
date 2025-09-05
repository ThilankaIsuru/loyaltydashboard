<?php
$servername = "localhost";
$username = "root"; // Use your actual MySQL username
$password = ""; // Use your actual MySQL password
$dbname = "loyalty_rewards"; // The name of your database

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>