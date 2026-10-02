<?php
session_start();
include "db_connect.php";

if (!isset($_SESSION["user_id"])) {
    header("Location: ../html/login.html");
    exit();
}

$userId = $_SESSION["user_id"];
$isLoggedIn = true;
$userName = $_SESSION["full_name"];

$stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$userEmail = $stmt->get_result()->fetch_assoc()["email"];

// Get this user's medications only
$stmt = $conn->prepare("SELECT * FROM medications WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$medications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Build today's dose schedule from each medication's "times" field
$today = date("Y-m-d");
$schedule = [];

foreach ($medications as $med) {
    foreach (explode(",", $med["times"]) as $t) {
        $schedule[] = [
            "medication_id" => $med["id"],
            "med_name" => $med["med_name"],
            "dosage" => $med["dosage"],
            "instruction" => $med["instruction"],
            "time" => trim($t)
        ];
    }
}

// Find which of today's doses are already logged as taken
$stmt = $conn->prepare("SELECT medication_id, log_time FROM medication_logs WHERE user_id = ? AND log_date = ?");
$stmt->bind_param("is", $userId, $today);
$stmt->execute();
$takenSet = [];
foreach ($stmt->get_result()->fetch_all(MYSQLI_ASSOC) as $row) {
    $takenSet[$row["medication_id"] . "|" . $row["log_time"]] = true;
}

$now = new DateTime();
$takenCount = 0; $pendingCount = 0; $missedCount = 0;

foreach ($schedule as &$item) {
    $key = $item["medication_id"] . "|" . $item["time"];
    if (isset($takenSet[$key])) {
        $item["status"] = "taken";
        $takenCount++;
    } else {
        $scheduledTime = DateTime::createFromFormat("h:i A", $item["time"]);
        if ($scheduledTime && $scheduledTime < $now) {
            $item["status"] = "missed";
            $missedCount++;
        } else {
            $item["status"] = "pending";
            $pendingCount++;
        }
    }
}
unset($item);

usort($schedule, function ($a, $b) {
    return DateTime::createFromFormat("h:i A", $a["time"]) <=> DateTime::createFromFormat("h:i A", $b["time"]);
});

$activePage = "medications";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Medications - CareConnection+</title>
<link rel="stylesheet" href="../css/navbar.css">
<link rel="stylesheet" href="../css/medications.css">
</head>
<body>

<?php include "includes/navbar.php"; ?>

<section class="page-header">
  <h1>💊 Medication Tracker</h1>
  <p>Never miss a dose — manage your medications and schedules easily.</p>
  <button class="add-btn" id="openModalBtn">+ Add Medication</button>
</section>

<section class="schedule-card">
  <div class="schedule-header">
    <h2>Today's Schedule</h2>
    <span class="today-date"><?php echo date("d M Y"); ?></span>
  </div>

  <?php if (count($schedule) === 0): ?>
    <p class="empty-msg">No medications added yet. Click "Add Medication" to get started.</p>
  <?php else: ?>
    <?php foreach ($schedule as $item): ?>
      <div class="schedule-row">
        <div class="schedule-time"><?php echo htmlspecialchars($item["time"]); ?></div>
        <div class="schedule-info">
          <strong><?php echo htmlspecialchars($item["med_name"]); ?> — <?php echo htmlspecialchars($item["dosage"]); ?></strong>
          <span><?php echo htmlspecialchars($item["instruction"]); ?></span>
        </div>
        <?php if ($item["status"] === "taken"): ?>
          <span class="badge badge-taken">Taken</span>
        <?php elseif ($item["status"] === "missed"): ?>
          <span class="badge badge-missed">Missed</span>
        <?php else: ?>
          <form action="take_medication_process.php" method="POST">
            <input type="hidden" name="medication_id" value="<?php echo $item['medication_id']; ?>">
            <input type="hidden" name="log_time" value="<?php echo htmlspecialchars($item['time']); ?>">
            <button type="submit" class="take-btn">Take</button>
          </form>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</section>

<section class="stats-row">
  <div class="stat-card">
    <div class="stat-icon stat-green">✔</div>
    <div><h3><?php echo $takenCount; ?></h3><p>Taken Today</p></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon stat-yellow">⏳</div>
    <div><h3><?php echo $pendingCount; ?></h3><p>Pending</p></div>
  </div>
  <div class="stat-card">
    <div class="stat-icon stat-red">✖</div>
    <div><h3><?php echo $missedCount; ?></h3><p>Missed</p></div>
  </div>
</section>

<section class="all-meds">
  <h2>All Medications</h2>
  <?php if (count($medications) === 0): ?>
    <p class="empty-msg">You haven't added any medications yet.</p>
  <?php else: ?>
    <?php foreach ($medications as $med): ?>
      <div class="med-row">
        <div class="med-info">
          <strong><?php echo htmlspecialchars($med["med_name"]); ?> — <?php echo htmlspecialchars($med["dosage"]); ?></strong>
          <span>
            <?php echo htmlspecialchars($med["frequency"]); ?> &bull;
            <?php echo htmlspecialchars($med["times"]); ?> &bull;
            <?php echo htmlspecialchars($med["instruction"]); ?>
            <?php if ($med["prescribed_by"]): ?> &bull; <?php echo htmlspecialchars($med["prescribed_by"]); ?><?php endif; ?>
          </span>
        </div>
        <form action="remove_medication_process.php" method="POST" onsubmit="return confirm('Remove this medication?');">
          <input type="hidden" name="medication_id" value="<?php echo $med['id']; ?>">
          <button type="submit" class="remove-btn">Remove</button>
        </form>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</section>

<div class="modal-overlay" id="modalOverlay">
  <div class="modal-box">
    <h2>💊 Add Medication</h2>
    <form action="add_medication_process.php" method="POST">
      <label>Medication Name</label>
      <input type="text" name="med_name" placeholder="e.g., Paracetamol" required>

      <label>Dosage</label>
      <input type="text" name="dosage" placeholder="e.g., 500mg" required>

      <label>Frequency</label>
      <select name="frequency" required>
        <option value="">Select Frequency</option>
        <option value="Once daily">Once daily</option>
        <option value="Twice daily">Twice daily</option>
        <option value="Thrice daily">Thrice daily</option>
        <option value="Once weekly">Once weekly</option>
      </select>

      <label>Times (comma separated, format 08:00 AM)</label>
      <input type="text" name="times" placeholder="e.g., 08:00 AM, 08:00 PM" required>

      <label>Instruction</label>
      <select name="instruction" required>
        <option value="">Select</option>
        <option value="Before food">Before food</option>
        <option value="After food">After food</option>
        <option value="With food">With food</option>
      </select>

      <label>Prescribed By (optional)</label>
      <input type="text" name="prescribed_by" placeholder="Doctor name">

      <div class="modal-buttons">
        <button type="button" class="cancel-btn" id="cancelModalBtn">Cancel</button>
        <button type="submit" class="save-btn">Add Medication</button>
      </div>
    </form>
  </div>
</div>

<script src="../js/home.js"></script>
<script src="../js/medications.js"></script>
</body>
</html>