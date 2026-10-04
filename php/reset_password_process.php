<?php
session_start();
include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $token = $_POST["token"];
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirm_password"];

    if ($password !== $confirmPassword) {
        die("Passwords do not match. <a href='../html/reset-password.php?token=" . urlencode($token) . "'>Go back</a>");
    }
    if (strlen($password) < 6) {
        die("Password must be at least 6 characters. <a href='../html/reset-password.php?token=" . urlencode($token) . "'>Go back</a>");
    }

    $stmt = $conn->prepare("SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("This reset link is invalid or has expired. <a href='../html/forgot-password.php'>Request a new one</a>");
    }

    $resetRow = $result->fetch_assoc();
    $userId = $resetRow["user_id"];

    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
    $stmt->bind_param("si", $hashedPassword, $userId);
    $stmt->execute();

    // Mark this token used so it can't be reused
    $stmt = $conn->prepare("UPDATE password_resets SET used = 1 WHERE token = ?");
    $stmt->bind_param("s", $token);
    $stmt->execute();

    header("Location: ../html/login.html?reset=1");
    exit();
}
?>