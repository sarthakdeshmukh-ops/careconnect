<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../html/login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = $_SESSION["user_id"];
    $exerciseType = trim($_POST["exercise_type"]);
    $duration = (int) $_POST["duration_minutes"];
    $calories = (int) $_POST["calories_burned"];
    $today = date("Y-m-d");

    if (empty($exerciseType) || $duration <= 0) {
        die("Please fill all required fields. <a href='fitness.php?tab=workouts'>Go back</a>");
    }

    $stmt = $conn->prepare("INSERT INTO workouts (user_id, exercise_type, duration_minutes, calories_burned, workout_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("isiis", $userId, $exerciseType, $duration, $calories, $today);
    $stmt->execute();

    header("Location: fitness.php?tab=workouts");
    exit();
}
?>