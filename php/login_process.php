<?php
session_start();
include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $unique_id = trim($_POST["unique_user_id"]);
    $email     = trim($_POST["email"]);
    $password  = $_POST["password"];

    $stmt = $conn->prepare("SELECT id, full_name, password FROM users WHERE unique_user_id = ? AND email = ?");
    $stmt->bind_param("ss", $unique_id, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("No account found with that User ID and Email. <a href='../html/register.html'>Create one</a>");
    }

    $user = $result->fetch_assoc();

    if (password_verify($password, $user["password"])) {
        $_SESSION["user_id"]   = $user["id"];
        $_SESSION["full_name"] = $user["full_name"];
        header("Location: home.php");
        exit();
    } else {
        die("Incorrect password. <a href='../html/login.html'>Try again</a>");
    }
}
?>