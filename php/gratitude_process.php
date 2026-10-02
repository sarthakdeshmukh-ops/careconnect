<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../html/login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = $_SESSION["user_id"];
    $entryText = trim($_POST["entry_text"]);

    if (empty($entryText)) {
        die("Please write something before saving. <a href='mental-health.php'>Go back</a>");
    }

    $stmt = $conn->prepare("INSERT INTO gratitude_entries (user_id, entry_text) VALUES (?, ?)");
    $stmt->bind_param("is", $userId, $entryText);
    $stmt->execute();

    header("Location: mental-health.php");
    exit();
}
?>