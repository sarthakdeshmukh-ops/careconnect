<?php
session_start();
include "../php/db_connect.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Forgot Password - CareConnection+</title>
<link rel="stylesheet" href="../css/login.css">
</head>
<body>
  <div class="care-card">
    <div class="care-badge">Your Health, Simplified.</div>
    <h1>Forgot Password</h1>
    <p class="subtitle">Enter your details to reset your password</p>

    <form action="../php/forgot_password_process.php" method="POST">
      <label>Unique User ID</label>
      <input type="text" name="unique_user_id" required>

      <label>Email</label>
      <input type="email" name="email" required>

      <button type="submit" class="care-btn">Send Reset Link</button>
    </form>

    <p class="switch-link">Remembered your password? <a href="login.html">Log in here</a></p>
  </div>
</body>
</html>