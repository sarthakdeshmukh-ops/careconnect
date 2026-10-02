<?php
date_default_timezone_set("Asia/Kolkata");   // PHP clock

$host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "careconnect_db";

$conn = new mysqli($host, $db_user, $db_pass, $db_name);

if ($conn->connect_error) {
    die("Database connection failed: " . $conn->connect_error);
}

$conn->query("SET time_zone = '+05:30'");    // MySQL clock
?>