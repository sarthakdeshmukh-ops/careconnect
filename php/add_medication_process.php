<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../html/login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = $_SESSION["user_id"];
    $med_name = trim($_POST["med_name"]);
    $dosage = trim($_POST["dosage"]);
    $frequency = trim($_POST["frequency"]);
    $times = trim($_POST["times"]);
    $instruction = trim($_POST["instruction"]);
    $prescribed_by = trim($_POST["prescribed_by"]);

    if (empty($med_name) || empty($dosage) || empty($frequency) || empty($times) || empty($instruction)) {
        die("All required fields must be filled. <a href='medications.php'>Go back</a>");
    }

    $stmt = $conn->prepare("INSERT INTO medications (user_id, med_name, dosage, frequency, times, instruction, prescribed_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $userId, $med_name, $dosage, $frequency, $times, $instruction, $prescribed_by);
    $stmt->execute();

    header("Location: medications.php");
    exit();
}
?>oo