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

    // Check a file was actually uploaded
    if (!isset($_FILES["document"]) || $_FILES["document"]["error"] !== UPLOAD_ERR_OK) {
        die("Please upload a document. <a href='appointments.php'>Go back</a>");
    }

    $allowedTypes = ["application/pdf", "image/jpeg", "image/png"];
    $fileType = $_FILES["document"]["type"];
    if (!in_array($fileType, $allowedTypes)) {
        die("Only PDF, JPG, or PNG files are allowed. <a href='appointments.php'>Go back</a>");
    }

    // Build a unique filename so two uploads never overwrite each other
    $originalName = basename($_FILES["document"]["name"]);
    $extension = pathinfo($originalName, PATHINFO_EXTENSION);
    $uniqueName = "user" . $userId . "_" . time() . "_" . uniqid() . "." . $extension;

    $uploadDir = "../uploads/health_records/";
    $uploadPath = $uploadDir . $uniqueName;

    if (!move_uploaded_file($_FILES["document"]["tmp_name"], $uploadPath)) {
        die("File upload failed. Please try again. <a href='appointments.php'>Go back</a>");
    }

    // Store the web-accessible relative path (not the file system path)
    $documentPath = "../uploads/health_records/" . $uniqueName;

    $stmt = $conn->prepare("INSERT INTO health_records (user_id, record_type, doctor_id, document_path, record_date, title, notes) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isissss", $userId, $recordType, $doctorId, $documentPath, $date, $title, $notes);
    $stmt->execute();

    header("Location: appointments.php");
    exit();
}
?>