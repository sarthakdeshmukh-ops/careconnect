<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../html/login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = $_SESSION["user_id"];
    $mood = trim($_POST["mood"]);
    $today = date("Y-m-d");

    $moodValues = ["Great" => 90, "Good" => 70, "Okay" => 50, "Low" => 30, "Awful" => 10];

    if (!isset($moodValues[$mood])) {
        die("Invalid mood. <a href='mental-health.php'>Go back</a>");
    }
    $moodValue = $moodValues[$mood];

    // Insert today's mood, or update it if already logged today
    $stmt = $conn->prepare("
        INSERT INTO mood_logs (user_id, mood, mood_value, log_date)
        VALUES (?, ?, ?, ?)
        ON DUPLICATE KEY UPDATE mood = VALUES(mood), mood_value = VALUES(mood_value)
    ");
    $stmt->bind_param("isis", $userId, $mood, $moodValue, $today);
    $stmt->execute();

    header("Location: mental-health.php");
    exit();
}
?>