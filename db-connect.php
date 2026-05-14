<?php
// Database credentials
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "alburhanstore";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    // In production, you'd log this and show a generic message
    die("Connection failed: " . $conn->connect_error);
}

// Set charset to utf8mb4 for better compatibility (emojis, special characters)
$conn->set_charset("utf8mb4");
?>