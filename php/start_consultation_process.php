<?php
session_start();
include "db_connect.php";
header('Content-Type: application/json');

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Not logged in"]);
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = $_SESSION["user_id"];
    $doctorId = (int) $_POST["doctor_id"];
    $patientName = trim($_POST["patient_name"]);
    $concern = trim($_POST["concern"]);
    $date = date("Y-m-d");
    $time = date("h:i A");

    if (empty($doctorId) || empty($patientName) || empty($concern)) {
        echo json_encode(["success" => false, "message" => "Missing fields"]);
        exit();
    }

    $stmt = $conn->prepare("INSERT INTO consultations (user_id, doctor_id, patient_name, concern, consult_date, consult_time) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("iissss", $userId, $doctorId, $patientName, $concern, $date, $time);
    $stmt->execute();

    echo json_encode(["success" => true]);
    exit();
}
?>