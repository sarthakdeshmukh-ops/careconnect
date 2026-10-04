<?php
session_start();
include "../php/db_connect.php";

$token = isset($_GET["token"]) ? $_GET["token"] : "";
$validToken = false;

if ($token) {
    $stmt = $conn->prepare("SELECT * FROM password_resets WHERE token = ? AND used = 0 AND expires_at > NOW()");
    $stmt->bind_param("s", $token);
    $stmt->execute();
    $result = $stmt->get_result();
    $validToken = $result->num_rows > 0;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reset Password - CareConnection+</title>
<link rel="stylesheet" href="../css/login.css">
</head>
<body>
  <div class="care-card">
    <div class="care-badge">Your Health, Simplified.</div>

    <?php if (!$validToken): ?>
      <h1>Link Expired</h1>
      <p class="subtitle">This reset link is invalid, already used, or has expired.</p>
      <a href="forgot-password.php" class="care-btn" style="display:block; text-align:center;">Request a New Link</a>
    <?php else: ?>
      <h1>Set New Password</h1>
      <p class="subtitle">Choose a new password for your account</p>

      <form action="../php/reset_password_process.php" method="POST" id="resetForm">
        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">

        <label>New Password</label>
        <input type="password" name="password" id="newPassword" required>

        <label>Confirm New Password</label>
        <input type="password" name="confirm_password" id="confirmPassword" required>

        <button type="submit" class="care-btn">Reset Password</button>
      </form>
    <?php endif; ?>

    <p class="switch-link"><a href="login.html">Back to Login</a></p>
  </div>
  <script src="../js/password-toggle.js"></script>
  <script src="../js/reset-password.js"></script>
</body>
</html>