<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../html/login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = $_SESSION["user_id"];
    $vitalType = trim($_POST["vital_type"]);
    $value = trim($_POST["value"]);
    $notes = trim($_POST["notes"]);
    $today = date("Y-m-d");

    if (empty($vitalType) || empty($value)) {
        die("Please fill all required fields. <a href='fitness.php?tab=vitals'>Go back</a>");
    }

    $stmt = $conn->prepare("INSERT INTO health_vitals (user_id, vital_type, value, notes, log_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $userId, $vitalType, $value, $notes, $today);
    $stmt->execute();

    header("Location: fitness.php?tab=vitals");
    exit();
}
?>