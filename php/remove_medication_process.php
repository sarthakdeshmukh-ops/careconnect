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

    // "AND user_id = ?" is what stops User A from deleting User B's medicine
    $stmt = $conn->prepare("DELETE FROM medications WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $medicationId, $userId);
    $stmt->execute();

    header("Location: medications.php");
    exit();
}
?>