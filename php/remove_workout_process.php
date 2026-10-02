<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../html/login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = $_SESSION["user_id"];
    $workoutId = $_POST["workout_id"];

    $stmt = $conn->prepare("DELETE FROM workouts WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $workoutId, $userId);
    $stmt->execute();

    header("Location: fitness.php?tab=workouts");
    exit();
}
?>