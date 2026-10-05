<nav class="navbar">
  <div class="logo">CareConnection<span class="plus">+</span></div>

  <ul class="nav-links">
    <li><a href="../html/home.html" class="<?php echo $activePage === 'home' ? 'active' : ''; ?>">Home</a></li>
    <li><a href="telemedicine.php" class="<?php echo $activePage === 'telemedicine' ? 'active' : ''; ?>">Telemedicine</a></li>
    <li><a href="appointments.php" class="<?php echo $activePage === 'appointments' ? 'active' : ''; ?>">Appointments</a></li>
    <li><a href="../html/mental-health.html" class="<?php echo $activePage === 'mental-health' ? 'active' : ''; ?>">Mental Health</a></li>
    <li><a href="medications.php" class="<?php echo $activePage === 'medications' ? 'active' : ''; ?>">Medications</a></li>
    <li><a href="../html/community.html" class="<?php echo $activePage === 'community' ? 'active' : ''; ?>">Community</a></li>
    <li><a href="../html/fitness.html" class="<?php echo $activePage === 'fitness' ? 'active' : ''; ?>">Fitness</a></li>
  </ul>

  <?php if ($isLoggedIn): ?>
    <div class="profile-wrap">
      <button class="profile-icon" id="profileBtn"><?php echo strtoupper(substr($userName, 0, 1)); ?></button>

      <div class="profile-dropdown" id="profileDropdown">
        <div class="dropdown-name"><?php echo htmlspecialchars($userName); ?></div>
        <div class="dropdown-email"><?php echo htmlspecialchars($userEmail); ?></div>

        <hr>

        <a href="profile.php">My Profile</a>
        <a href="logout.php" class="logout-link">Log Out</a>
      </div>
    </div>

  <?php else: ?>

    <a href="../html/login.html" class="login-btn">Login</a>

  <?php endif; ?>
</nav>