<?php
session_start();
include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = trim($_POST["full_name"]);
    $unique_id = trim($_POST["unique_user_id"]);
    $email     = trim($_POST["email"]);
    $password  = $_POST["password"];

    if (empty($full_name) || empty($unique_id) || empty($email) || empty($password)) {
        die("All fields are required. <a href='../html/register.html'>Go back</a>");
    }

    // Check if user ID or email already exists
    $check = $conn->prepare("SELECT id FROM users WHERE unique_user_id = ? OR email = ?");
    $check->bind_param("ss", $unique_id, $email);
    $check->execute();
    $check->store_result();

    if ($check->num_rows > 0) {
        die("That User ID or Email is already registered. <a href='../html/login.html'>Login instead</a>");
    }

    // Hash the password before saving (never store plain text)
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (full_name, unique_user_id, email, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $full_name, $unique_id, $email, $hashed_password);

    if ($stmt->execute()) {
        header("Location: ../html/login.html?registered=1");
        exit();
    } else {
        die("Something went wrong: " . $conn->error);
    }
}
?>