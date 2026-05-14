<?php
session_start();
require_once '../db-connect.php';

$email = $_POST['email'];
$password = $_POST['password'];

// 1. Fetch the user by email (Add 'name' to your SELECT)
$stmt = $conn->prepare("SELECT id, firstname, password FROM admin WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($user = $result->fetch_assoc()) {
    if (password_verify($password, $user['password'])) {
        // Success! Set session variables
        $_SESSION['admin_id'] = $user['id'];
        $_SESSION['admin_firstname'] = $user['firstname']; // Store name here
        header("Location: ../Home/burhani.php"); // Ensure you redirect to the PHP version
        exit();
    } else {
        header("Location: login.php?error=invalid_password");
    }
} else {
    header("Location: login.php?error=user_not_found");
}

$stmt->close();
$conn->close();
?>