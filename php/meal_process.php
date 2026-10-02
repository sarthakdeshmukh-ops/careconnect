<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../html/login.html");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $userId = $_SESSION["user_id"];
    $mealType = trim($_POST["meal_type"]);
    $foodItems = trim($_POST["food_items"]);
    $calories = (int) $_POST["calories"];
    $protein = (int) $_POST["protein"];
    $carbs = (int) $_POST["carbs"];
    $fat = (int) $_POST["fat"];
    $today = date("Y-m-d");

    if (empty($mealType) || empty($foodItems)) {
        die("Please fill all required fields. <a href='fitness.php?tab=nutrition'>Go back</a>");
    }

    $stmt = $conn->prepare("INSERT INTO meals (user_id, meal_type, food_items, calories, protein, carbs, fat, meal_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issiiiis", $userId, $mealType, $foodItems, $calories, $protein, $carbs, $fat, $today);
    $stmt->execute();

    header("Location: fitness.php?tab=nutrition");
    exit();
}
?>