<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../html/login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = $_SESSION["user_id"];
    $vitalId = $_POST["vital_id"];

    $stmt = $conn->prepare("DELETE FROM health_vitals WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $vitalId, $userId);
    $stmt->execute();

    header("Location: fitness.php?tab=vitals");
    exit();
}
?>