<?php
session_start();
include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $uniqueId = trim($_POST["unique_user_id"]);
    $email = trim($_POST["email"]);

    $stmt = $conn->prepare("SELECT id, full_name FROM users WHERE unique_user_id = ? AND email = ?");
    $stmt->bind_param("ss", $uniqueId, $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        die("No account found with that User ID and Email. <a href='../html/forgot-password.php'>Try again</a>");
    }

    $user = $result->fetch_assoc();
    $userId = $user["id"];

    // Generate a random, hard-to-guess token
    $token = bin2hex(random_bytes(32));
    $expiresAt = date("Y-m-d H:i:s", strtotime("+15 minutes"));

    $stmt = $conn->prepare("INSERT INTO password_resets (user_id, token, expires_at) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $userId, $token, $expiresAt);
    $stmt->execute();

    $resetLink = "../html/reset-password.php?token=" . $token;
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
    <meta charset="UTF-8">
    <title>Reset Link - CareConnection+</title>
    <link rel="stylesheet" href="../css/login.css">
    </head>
    <body>
      <div class="care-card">
        <div class="care-badge">Your Health, Simplified.</div>
        <h1>Reset Link Ready</h1>
        <p class="subtitle">In a real deployed app, this link would be emailed to you. For this local demo, here it is directly:</p>
        <p style="word-break: break-all; background:#f5f8fb; padding:14px; border-radius:10px; font-size:13px; margin-bottom:18px;">
          <a href="<?php echo htmlspecialchars($resetLink); ?>"><?php echo htmlspecialchars($resetLink); ?></a>
        </p>
        <p class="subtitle">This link expires in 15 minutes and can only be used once.</p>
        <a href="<?php echo htmlspecialchars($resetLink); ?>" class="care-btn" style="display:block; text-align:center; margin-top:16px;">Reset Password Now</a>
      </div>
    </body>
    </html>
    <?php
    exit();
}
?>