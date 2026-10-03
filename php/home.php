<?php
session_start();
include "db_connect.php";

$isLoggedIn = isset($_SESSION["user_id"]);
$userName = $isLoggedIn ? $_SESSION["full_name"] : "";
$userEmail = "";

if ($isLoggedIn) {
    $stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
    $stmt->bind_param("i", $_SESSION["user_id"]);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();
    $userEmail = $row["email"];
}

$activePage = "home";
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>CareConnection+ | Your Health, Simplified.</title>
<link rel="stylesheet" href="../css/home.css">
<link rel="stylesheet" href="../css/navbar.css">
</head>
<body>

<?php include "includes/navbar.php"; ?>

<section class="hero">
  <div class="hero-text">
    <h1>Your Health, <span class="highlight">Simplified.</span></h1>
    <p>A complete digital health companion — manage appointments, track medications, consult doctors, and nurture your mental wellness, all in one place.</p>
    <div class="hero-buttons">
      <a href="appointments.php" class="btn-primary">Book Appointment</a>
      <a href="telemedicine.php" class="btn-secondary">Find a Doctor</a>
    </div>
  </div>
  <div class="hero-icon">🩺</div>
</section>

<section class="stats">
  <div class="stat"><h2>24/7</h2><p>Virtual Consultations</p></div>
  <div class="stat"><h2>50+</h2><p>Expert Doctors</p></div>
  <div class="stat"><h2>10K+</h2><p>Happy Patients</p></div>
  <div class="stat"><h2>6</h2><p>Health Modules</p></div>
</section>

<section class="modules">
  <h2>Explore Health Modules</h2>
  <div class="module-grid">
    <div class="module-card">
      <div class="module-icon">🖥️</div>
      <h3>Telemedicine</h3>
      <p>Connect with doctors virtually. Browse specialists and schedule video consultations from home.</p>
    </div>
    <div class="module-card">
      <div class="module-icon">📅</div>
      <h3>Appointments & Records</h3>
      <p>Book appointments, manage your health records, and keep your medical history organized.</p>
    </div>
    <div class="module-card">
      <div class="module-icon">🧘</div>
      <h3>Mental Wellness</h3>
      <p>Track your mood, practice mindfulness, journal your thoughts, and access breathing exercises.</p>
    </div>
    <div class="module-card">
      <div class="module-icon">💊</div>
      <h3>Medication Tracker</h3>
      <p>Never miss a dose. Set reminders, track dosage schedules, and monitor adherence.</p>
    </div>
    <div class="module-card">
      <div class="module-icon">🏥</div>
      <h3>Community Health</h3>
      <p>Stay informed with health articles, vaccination drives, and prevention tips.</p>
    </div>
    <div class="module-card">
      <div class="module-icon">🏃</div>
      <h3>Fitness & Nutrition</h3>
      <p>Track workouts, log meals, monitor calories, and stay on top of your fitness goals.</p>
    </div>
  </div>
</section>

<footer>
  CareConnection+ &copy; 2026 — Your Health, is our Responsibility
</footer>

<script src="../js/home.js"></script>
</body>
</html>