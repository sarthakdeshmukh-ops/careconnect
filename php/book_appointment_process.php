<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../html/login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = $_SESSION["user_id"];
    $doctorId = $_POST["doctor_id"];
    $mode = isset($_POST["consultation_mode"]) ? $_POST["consultation_mode"] : "Offline";
    $date = $_POST["appointment_date"];
    $time = $_POST["appointment_time"];
    $reason = trim($_POST["reason"]);

    if (empty($doctorId) || empty($date) || empty($time)) {
        die("Please fill all required fields. <a href='appointments.php'>Go back</a>");
    }

    $stmt = $conn->prepare("INSERT INTO appointments (user_id, doctor_id, consultation_mode, appointment_date, appointment_time, reason) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissss", $userId, $doctorId, $mode, $date, $time, $reason);
    $stmt->execute();

    header("Location: appointments.php?booked=1");
    exit();
}
?>