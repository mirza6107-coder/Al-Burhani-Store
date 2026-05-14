<?php
require_once '../db-connect.php';

$firstname = $_POST["firstname"];
$lastname = $_POST["lastname"];
$email = $_POST["email"];
$password = $_POST["password"];

// 1. Hash the password
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// 2. Use a Prepared Statement to prevent SQL Injection
$stmt = $conn->prepare("INSERT INTO admin (firstname, lastname, email, password) VALUES (?, ?, ?, ?)");
$stmt->bind_param("ssss", $firstname, $lastname, $email, $hashed_password);

if($stmt->execute()){
    header("Location: ../login/login.html");
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>