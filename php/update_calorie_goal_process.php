<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../html/login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = $_SESSION["user_id"];
    $goal = (int) $_POST["daily_calorie_goal"];
    if ($goal < 500) { $goal = 500; }
    $ndate = isset($_POST["ndate"]) ? $_POST["ndate"] : date("Y-m-d");

    $stmt = $conn->prepare("UPDATE users SET daily_calorie_goal = ? WHERE id = ?");
    $stmt->bind_param("ii", $goal, $userId);
    $stmt->execute();

    header("Location: fitness.php?tab=nutrition&ndate=" . urlencode($ndate));
    exit();
}
?>