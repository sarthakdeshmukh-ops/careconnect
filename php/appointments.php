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

// Auto-mark past upcoming appointments as Completed
$stmt = $conn->prepare("UPDATE appointments SET status = 'Completed' WHERE user_id = ? AND status = 'Upcoming' AND appointment_date < CURDATE()");
$stmt->bind_param("i", $userId);
$stmt->execute();

// Get this user's appointments, joined with doctor info
$stmt = $conn->prepare("
    SELECT appointments.*, doctors.doctor_name, doctors.specialty
    FROM appointments
    JOIN doctors ON appointments.doctor_id = doctors.id
    WHERE appointments.user_id = ?
    ORDER BY appointment_date DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$appointments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get doctor list for the booking dropdown
$doctorsResult = $conn->query("SELECT id, doctor_name, specialty FROM doctors ORDER BY doctor_name");
$doctors = $doctorsResult->fetch_all(MYSQLI_ASSOC);

// Get this user's health records, joined with doctor info
$stmt = $conn->prepare("
    SELECT health_records.*, doctors.doctor_name, doctors.specialty
    FROM health_records
    JOIN doctors ON health_records.doctor_id = doctors.id
    WHERE health_records.user_id = ?
    ORDER BY record_date DESC
");
$stmt->bind_param("i", $userId);
$stmt->execute();
$records = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$activePage = "appointments";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Appointments - CareConnection+</title>
<link rel="stylesheet" href="../css/navbar.css">
<link rel="stylesheet" href="../css/appointments.css">
</head>
<body>

<?php include "includes/navbar.php"; ?>

<section class="page-header">
  <h1>📅 Appointments & Records</h1>
  <p>Manage your appointments and keep health records organized.</p>
  <button class="add-btn" id="openModalBtn">+ Book Appointment</button>
  <button class="add-btn" id="openRecordModalBtn" style="display:none;background:#4caf7d;">+ Add Record</button>
</section>

<div class="tabs">
  <button class="tab-btn active" id="tabAppointmentsBtn">Appointments</button>
  <button class="tab-btn" id="tabRecordsBtn">Health Records</button>
</div>

<div id="appointmentsTab">
<section class="appointments-list">
  <?php if (count($appointments) === 0): ?>
    <p class="empty-msg">You have no appointments yet. Click "Book Appointment" to schedule one.</p>
  <?php else: ?>
    <?php foreach ($appointments as $appt): ?>
      <?php
        $dateObj = new DateTime($appt["appointment_date"]);
        $day = $dateObj->format("d");
        $monthShort = strtoupper($dateObj->format("M"));
      ?>
      <div class="appt-row">
        <div class="appt-date">
          <span class="day"><?php echo $day; ?></span>
          <span class="month"><?php echo $monthShort; ?></span>
        </div>
        <div class="appt-info">
          <strong><?php echo htmlspecialchars($appt["doctor_name"]); ?> — <?php echo htmlspecialchars($appt["specialty"]); ?></strong>
          <span>🕐 <?php echo htmlspecialchars($appt["appointment_time"]); ?> &nbsp; 📝 <?php echo htmlspecialchars($appt["reason"]); ?></span>
        </div>

        <?php if ($appt["status"] === "Upcoming"): ?>
          <span class="badge badge-upcoming">Upcoming</span>
          <form action="cancel_appointment_process.php" method="POST" onsubmit="return confirm('Cancel this appointment?');">
            <input type="hidden" name="appointment_id" value="<?php echo $appt['id']; ?>">
            <button type="submit" class="cancel-appt-btn">Cancel</button>
          </form>
        <?php elseif ($appt["status"] === "Completed"): ?>
          <span class="badge badge-completed">Completed</span>
        <?php else: ?>
          <span class="badge badge-cancelled">Cancelled</span>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</section>
</div>

<div id="recordsTab" style="display:none;">
<section class="appointments-list">
  <?php if (count($records) === 0): ?>
    <p class="empty-msg">No health records yet. Click "Add Record" to create one.</p>
  <?php else: ?>
    <?php foreach ($records as $rec): ?>
      <?php
        $typeIcons = [
            "Blood Test" => "🩸",
            "Prescription" => "💊",
            "X-Ray" => "🦴",
            "Scan / MRI" => "🧠",
            "Vaccination" => "💉",
            "Other" => "📄"
        ];
        $icon = $typeIcons[$rec["record_type"]] ?? "📄";
      ?>
      <div class="record-row">
        <div class="record-icon"><?php echo $icon; ?></div>
        <div class="appt-info">
          <strong><?php echo htmlspecialchars($rec["title"]); ?></strong>
          <span><?php echo htmlspecialchars($rec["record_type"]); ?> &bull; <?php echo htmlspecialchars($rec["record_date"]); ?> &bull; <?php echo htmlspecialchars($rec["doctor_name"]); ?><?php if ($rec["notes"]): ?> &bull; <?php echo htmlspecialchars($rec["notes"]); ?><?php endif; ?></span>
        </div>
        <form action="remove_record_process.php" method="POST" onsubmit="return confirm('Remove this record?');">
          <input type="hidden" name="record_id" value="<?php echo $rec['id']; ?>">
          <button type="submit" class="cancel-appt-btn">Remove</button>
        </form>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</section>
</div>

<div class="modal-overlay" id="modalOverlay">
  <div class="modal-box">
    <h2>📅 Book New Appointment</h2>
    <form action="book_appointment_process.php" method="POST">
      <input type="hidden" name="consultation_mode" value="Offline">

      <label>Doctor / Specialist</label>
      <select name="doctor_id" required>
        <option value="">Select Doctor</option>
        <?php foreach ($doctors as $doc): ?>
          <option value="<?php echo $doc['id']; ?>"><?php echo htmlspecialchars($doc['doctor_name']); ?> — <?php echo htmlspecialchars($doc['specialty']); ?></option>
        <?php endforeach; ?>
      </select>

      <label>Date</label>
      <input type="date" name="appointment_date" required min="<?php echo date('Y-m-d'); ?>">

      <label>Time Slot</label>
      <select name="appointment_time" required>
        <option value="">Select Time</option>
        <option value="09:00 AM">09:00 AM</option>
        <option value="10:00 AM">10:00 AM</option>
        <option value="11:00 AM">11:00 AM</option>
        <option value="02:00 PM">02:00 PM</option>
        <option value="03:00 PM">03:00 PM</option>
        <option value="04:00 PM">04:00 PM</option>
      </select>

      <label>Reason for Visit</label>
      <textarea name="reason" placeholder="Brief description..." rows="3"></textarea>

      <div class="modal-buttons">
        <button type="button" class="cancel-btn" id="cancelModalBtn">Cancel</button>
        <button type="submit" class="save-btn">Confirm Booking</button>
      </div>
    </form>
  </div>
</div>

<div class="modal-overlay" id="recordModalOverlay">
  <div class="modal-box">
    <h2>📋 Add Health Record</h2>
    <form action="add_record_process.php" method="POST">
      <label>Record Type</label>
      <select name="record_type" required>
        <option value="">Select Type</option>
        <option value="Blood Test">Blood Test</option>
        <option value="Prescription">Prescription</option>
        <option value="X-Ray">X-Ray</option>
        <option value="Scan / MRI">Scan / MRI</option>
        <option value="Vaccination">Vaccination</option>
        <option value="Other">Other</option>
      </select>

      <label>Title / Description</label>
      <input type="text" name="title" placeholder="e.g., Complete Blood Count Report" required>

      <label>Date</label>
      <input type="date" name="record_date" required max="<?php echo date('Y-m-d'); ?>">

      <label>Doctor</label>
      <select name="doctor_id" required>
        <option value="">Select Doctor</option>
        <?php foreach ($doctors as $doc): ?>
          <option value="<?php echo $doc['id']; ?>"><?php echo htmlspecialchars($doc['doctor_name']); ?> — <?php echo htmlspecialchars($doc['specialty']); ?></option>
        <?php endforeach; ?>
      </select>

      <label>Notes (optional)</label>
      <textarea name="notes" placeholder="Additional details..." rows="3"></textarea>

      <div class="modal-buttons">
        <button type="button" class="cancel-btn" id="cancelRecordModalBtn">Cancel</button>
        <button type="submit" class="save-btn" style="background:#4caf7d;">Save Record</button>
      </div>
    </form>
  </div>
</div>

<script src="../js/home.js"></script>
<script src="../js/appointments.js"></script>
<script>
const tabAppointmentsBtn = document.getElementById("tabAppointmentsBtn");
const tabRecordsBtn = document.getElementById("tabRecordsBtn");
const appointmentsTab = document.getElementById("appointmentsTab");
const recordsTab = document.getElementById("recordsTab");
const openModalBtn = document.getElementById("openModalBtn");
const openRecordModalBtn = document.getElementById("openRecordModalBtn");
const recordModalOverlay = document.getElementById("recordModalOverlay");
const cancelRecordModalBtn = document.getElementById("cancelRecordModalBtn");

tabAppointmentsBtn.addEventListener("click", function () {
    tabAppointmentsBtn.classList.add("active");
    tabRecordsBtn.classList.remove("active");
    appointmentsTab.style.display = "block";
    recordsTab.style.display = "none";
    openModalBtn.style.display = "inline-block";
    openRecordModalBtn.style.display = "none";
});

tabRecordsBtn.addEventListener("click", function () {
    tabRecordsBtn.classList.add("active");
    tabAppointmentsBtn.classList.remove("active");
    recordsTab.style.display = "block";
    appointmentsTab.style.display = "none";
    openRecordModalBtn.style.display = "inline-block";
    openModalBtn.style.display = "none";
});

openRecordModalBtn.addEventListener("click", function () {
    recordModalOverlay.classList.add("show");
});
cancelRecordModalBtn.addEventListener("click", function () {
    recordModalOverlay.classList.remove("show");
});
recordModalOverlay.addEventListener("click", function (e) {
    if (e.target === recordModalOverlay) {
        recordModalOverlay.classList.remove("show");
    }
});
</script>
</body>
</html>