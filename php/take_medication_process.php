<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../html/login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = $_SESSION["user_id"];
    $medicationId = $_POST["medication_id"];
    $logTime = $_POST["log_time"];
    $today = date("Y-m-d");

    // Confirm this medication actually belongs to the logged-in user
    $check = $conn->prepare("SELECT id FROM medications WHERE id = ? AND user_id = ?");
    $check->bind_param("ii", $medicationId, $userId);
    $check->execute();
    if ($check->get_result()->num_rows === 0) {
        die("Invalid medication.");
    }

    // Don't log the same dose twice
    $existing = $conn->prepare("SELECT id FROM medication_logs WHERE medication_id = ? AND user_id = ? AND log_date = ? AND log_time = ?");
    $existing->bind_param("iiss", $medicationId, $userId, $today, $logTime);
    $existing->execute();

    if ($existing->get_result()->num_rows === 0) {
        $stmt = $conn->prepare("INSERT INTO medication_logs (medication_id, user_id, log_date, log_time) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $medicationId, $userId, $today, $logTime);
        $stmt->execute();
    }

    header("Location: medications.php");
    exit();
}
?>