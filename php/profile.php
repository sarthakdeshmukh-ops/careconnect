<?php
session_start();
if (!isset($_SESSION["user_id"])) {
    header("Location: ../html/login.html");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>My Profile - CareConnection+</title>
<link rel="stylesheet" href="../css/login.css">
</head>
<body>
  <div class="care-card">
    <h1>Welcome, <?php echo htmlspecialchars($_SESSION["full_name"]); ?> 💙</h1>
    <p>This is your personal CareConnection+ profile.</p>
    <a class="care-btn" href="logout.php">Log Out</a>
  </div>
</body>
</html>