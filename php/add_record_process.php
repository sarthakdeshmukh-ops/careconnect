<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../html/login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = $_SESSION["user_id"];
    $recordType = trim($_POST["record_type"]);
    $doctorId = (int) $_POST["doctor_id"];
    $date = $_POST["record_date"];
    $title = trim($_POST["title"]);
    $notes = trim($_POST["notes"]);

    if (empty($recordType) || empty($doctorId) || empty($date) || empty($title)) {
        die("Please fill all required fields. <a href='appointments.php'>Go back</a>");
    }

    $stmt = $conn->prepare("INSERT INTO health_records (user_id, record_type, doctor_id, record_date, title, notes) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isisss", $userId, $recordType, $doctorId, $date, $title, $notes);
    $stmt->execute();

    header("Location: appointments.php");
    exit();
}
?>