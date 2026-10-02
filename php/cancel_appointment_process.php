<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../html/login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = $_SESSION["user_id"];
    $appointmentId = $_POST["appointment_id"];

    // "AND user_id = ?" stops User A from cancelling User B's appointment
    $stmt = $conn->prepare("UPDATE appointments SET status = 'Cancelled' WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $appointmentId, $userId);
    $stmt->execute();

    header("Location: appointments.php");
    exit();
}
?>