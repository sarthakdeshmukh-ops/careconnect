<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../html/login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = $_SESSION["user_id"];
    $recordId = $_POST["record_id"];

    // Fetch the file path first so we can delete the actual file too
    $stmt = $conn->prepare("SELECT document_path FROM health_records WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $recordId, $userId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();

    if ($row && $row["document_path"] && file_exists($row["document_path"])) {
        unlink($row["document_path"]);
    }

    $stmt = $conn->prepare("DELETE FROM health_records WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $recordId, $userId);
    $stmt->execute();

    header("Location: appointments.php");
    exit();
}
?>