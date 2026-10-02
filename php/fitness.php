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

$stmt = $conn->prepare("SELECT email, daily_calorie_goal FROM users WHERE id = ?");
$stmt->bind_param("i", $userId);
$stmt->execute();
$userRow = $stmt->get_result()->fetch_assoc();
$userEmail = $userRow["email"];
$calorieGoal = $userRow["daily_calorie_goal"];

$activeTab = isset($_GET["tab"]) ? $_GET["tab"] : "workouts";
$today = date("Y-m-d");

/* ============ WORKOUTS — build the selected week ============ */
$weekParam = isset($_GET["week"]) ? $_GET["week"] : $today;
$weekDate = new DateTime($weekParam);
$dayOfWeek = (int) $weekDate->format("N"); // 1=Mon ... 7=Sun
$weekDate->modify("-" . ($dayOfWeek - 1) . " days");
$weekStart = $weekDate->format("Y-m-d");
$weekEndObj = clone $weekDate;
$weekEndObj->modify("+6 days");
$weekEnd = $weekEndObj->format("Y-m-d");

$todayObj = new DateTime($today);
$todayDow = (int) $todayObj->format("N");
$currentWeekStartObj = clone $todayObj;
$currentWeekStartObj->modify("-" . ($todayDow - 1) . " days");
$currentWeekStart = $currentWeekStartObj->format("Y-m-d");

$isCurrentWorkoutWeek = ($weekStart === $currentWeekStart);

$prevWeekObj = clone $weekDate;
$prevWeekObj->modify("-7 days");
$prevWeek = $prevWeekObj->format("Y-m-d");

$nextWeekObj = clone $weekDate;
$nextWeekObj->modify("+7 days");
$canGoNextWeek = ($nextWeekObj <= new DateTime($currentWeekStart));
$nextWeek = $nextWeekObj->format("Y-m-d");

$stmt = $conn->prepare("SELECT * FROM workouts WHERE user_id = ? AND workout_date BETWEEN ? AND ? ORDER BY workout_date, id");
$stmt->bind_param("iss", $userId, $weekStart, $weekEnd);
$stmt->execute();
$weekWorkouts = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$days = [];
for ($i = 0; $i < 7; $i++) {
    $d = clone $weekDate;
    $d->modify("+$i days");
    $dateStr = $d->format("Y-m-d");
    $days[$dateStr] = [
        "label" => $d->format("D"),
        "date" => $dateStr,
        "total_minutes" => 0,
        "total_cal" => 0,
        "exercises" => []
    ];
}
foreach ($weekWorkouts as $w) {
    $days[$w["workout_date"]]["total_minutes"] += (int) $w["duration_minutes"];
    $days[$w["workout_date"]]["total_cal"] += (int) $w["calories_burned"];
    $days[$w["workout_date"]]["exercises"][] = $w;
}
$weekTotalWorkouts = count($weekWorkouts);
$weekTotalMinutes = array_sum(array_column($weekWorkouts, "duration_minutes"));
$weekTotalCal = array_sum(array_column($weekWorkouts, "calories_burned"));
$maxMinutes = max(array_column($days, "total_minutes"));
if ($maxMinutes < 1) { $maxMinutes = 1; }

// Only TODAY'S workouts go in the list below the graph — not the whole week
$todayWorkouts = array_values(array_filter($weekWorkouts, function ($w) use ($today) {
    return $w["workout_date"] === $today;
}));

/* ============ NUTRITION — build the selected day ============ */
$nDate = isset($_GET["ndate"]) ? $_GET["ndate"] : $today;
$isCurrentNutritionDay = ($nDate === $today);

$prevDayObj = new DateTime($nDate);
$prevDayObj->modify("-1 day");
$prevDay = $prevDayObj->format("Y-m-d");

$nextDayObj = new DateTime($nDate);
$nextDayObj->modify("+1 day");
$canGoNextDay = ($nextDayObj <= new DateTime($today));
$nextDay = $nextDayObj->format("Y-m-d");

$stmt = $conn->prepare("SELECT * FROM meals WHERE user_id = ? AND meal_date = ? ORDER BY id");
$stmt->bind_param("is", $userId, $nDate);
$stmt->execute();
$dayMeals = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$dayCalories = array_sum(array_column($dayMeals, "calories"));
$dayProtein = array_sum(array_column($dayMeals, "protein"));
$dayCarbs = array_sum(array_column($dayMeals, "carbs"));
$dayFat = array_sum(array_column($dayMeals, "fat"));
$caloriePct = $calorieGoal > 0 ? min(100, round(($dayCalories / $calorieGoal) * 100)) : 0;

$mealIcons = ["Breakfast" => "🍳", "Lunch" => "🍲", "Snack" => "🍎", "Dinner" => "🍽️"];

/* ============ HEALTH VITALS ============ */
$vitalTypes = [
    "Blood Pressure" => ["icon" => "🩺", "unit" => "mmHg"],
    "Heart Rate"      => ["icon" => "❤️", "unit" => "bpm"],
    "Blood Sugar"     => ["icon" => "🩸", "unit" => "mg/dL"],
    "Weight"          => ["icon" => "⚖️", "unit" => "kg"],
    "Temperature"     => ["icon" => "🌡️", "unit" => "°C"],
    "SpO2"            => ["icon" => "🫁", "unit" => "%"]
];

$stmt = $conn->prepare("SELECT * FROM health_vitals WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$allVitals = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$latestVitals = [];
foreach ($vitalTypes as $type => $info) {
    foreach ($allVitals as $v) {
        if ($v["vital_type"] === $type) {
            $latestVitals[$type] = $v;
            break;
        }
    }
}

$activePage = "fitness";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Fitness - CareConnection+</title>
<link rel="stylesheet" href="../css/navbar.css">
<link rel="stylesheet" href="../css/fitness.css">
</head>
<body>

<?php include "includes/navbar.php"; ?>

<section class="page-header">
  <h1>🏃 Fitness, Nutrition & Health Tracking</h1>
  <p>Track workouts, log meals, monitor your vitals and stay on top of your health goals.</p>
</section>

<div class="tabs">
  <button class="tab-btn <?php echo $activeTab === 'workouts' ? 'active' : ''; ?>" id="tabWorkoutsBtn">Workouts</button>
  <button class="tab-btn <?php echo $activeTab === 'nutrition' ? 'active' : ''; ?>" id="tabNutritionBtn">Nutrition</button>
  <button class="tab-btn <?php echo $activeTab === 'vitals' ? 'active' : ''; ?>" id="tabVitalsBtn">Health Vitals</button>
  <button class="tab-btn <?php echo $activeTab === 'bmi' ? 'active' : ''; ?>" id="tabBmiBtn">BMI Calculator</button>
</div>

<!-- ================= WORKOUTS TAB ================= -->
<div id="workoutsTab" class="tab-panel" style="display:<?php echo $activeTab === 'workouts' ? 'block' : 'none'; ?>;">

  <div class="week-nav">
    <a href="fitness.php?tab=workouts&week=<?php echo $prevWeek; ?>" class="week-nav-btn">‹ Prev Week</a>
    <div class="week-nav-center">
      <span class="week-range"><?php echo (new DateTime($weekStart))->format("d M"); ?> — <?php echo (new DateTime($weekEnd))->format("d M Y"); ?></span>
      <input type="date" id="workoutDatePicker" value="<?php echo $weekParam; ?>" max="<?php echo $today; ?>">
    </div>
    <?php if ($canGoNextWeek): ?>
      <a href="fitness.php?tab=workouts&week=<?php echo $nextWeek; ?>" class="week-nav-btn">Next Week ›</a>
    <?php else: ?>
      <span class="week-nav-btn disabled">Next Week ›</span>
    <?php endif; ?>
  </div>

  <?php if ($isCurrentWorkoutWeek): ?>
    <button class="add-btn" id="openWorkoutModalBtn">+ Log Workout</button>
  <?php endif; ?>

  <section class="summary-card">
    <h2>This Week's Summary</h2>
    <div class="summary-numbers">
      <div><strong><?php echo $weekTotalWorkouts; ?></strong><span>Workouts</span></div>
      <div><strong><?php echo $weekTotalMinutes; ?></strong><span>Minutes</span></div>
      <div><strong><?php echo $weekTotalCal; ?></strong><span>Cal Burned</span></div>
    </div>

    <div class="bar-chart">
      <?php foreach ($days as $dateStr => $day): ?>
        <?php
          $barPct = round(($day["total_minutes"] / $maxMinutes) * 100);
          if ($barPct < 4 && $day["total_minutes"] > 0) { $barPct = 4; }
          $isFuture = $dateStr > $today;
        ?>
        <div class="bar-col">
          <div class="bar <?php echo $isFuture ? 'bar-future' : ''; ?>"
               style="height: <?php echo $barPct; ?>%;"
               data-day='<?php echo htmlspecialchars(json_encode($day), ENT_QUOTES); ?>'></div>
          <span class="bar-label <?php echo $dateStr === $today ? 'bar-label-today' : ''; ?>"><?php echo $day["label"]; ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <?php if ($isCurrentWorkoutWeek): ?>
  <section class="entries-list">
    <h3 class="list-heading">Today's Workouts</h3>
    <?php if (count($todayWorkouts) === 0): ?>
      <p class="empty-msg">No workouts logged today.</p>
    <?php else: ?>
      <?php $exerciseIcons = ["Running"=>"🏃","Weight Training"=>"🏋️","Yoga"=>"🧘","Cycling"=>"🚴","Walking"=>"🚶","Swimming"=>"🏊","Other"=>"💪"]; ?>
      <?php foreach ($todayWorkouts as $w): ?>
        <?php $icon = $exerciseIcons[$w["exercise_type"]] ?? "💪"; ?>
        <div class="entry-row">
          <div class="entry-icon"><?php echo $icon; ?></div>
          <div class="entry-info">
            <strong><?php echo htmlspecialchars($w["exercise_type"]); ?></strong>
            <span><?php echo $w["duration_minutes"]; ?> min &bull; <?php echo $w["calories_burned"]; ?> cal burned</span>
          </div>
          <form action="remove_workout_process.php" method="POST" onsubmit="return confirm('Remove this workout?');">
            <input type="hidden" name="workout_id" value="<?php echo $w['id']; ?>">
            <button type="submit" class="remove-btn">Remove</button>
          </form>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>
  <?php else: ?>
  <p class="empty-msg" style="margin:20px 48px;">Click a bar above to view that day's workouts. Only the current week's "today" list is editable.</p>
  <?php endif; ?>
</div>

<!-- ================= NUTRITION TAB ================= -->
<div id="nutritionTab" class="tab-panel" style="display:<?php echo $activeTab === 'nutrition' ? 'block' : 'none'; ?>;">

  <div class="week-nav">
    <a href="fitness.php?tab=nutrition&ndate=<?php echo $prevDay; ?>" class="week-nav-btn">‹ Prev Day</a>
    <div class="week-nav-center">
      <span class="week-range"><?php echo (new DateTime($nDate))->format("d M Y"); ?></span>
      <input type="date" id="nutritionDatePicker" value="<?php echo $nDate; ?>" max="<?php echo $today; ?>">
    </div>
    <?php if ($canGoNextDay): ?>
      <a href="fitness.php?tab=nutrition&ndate=<?php echo $nextDay; ?>" class="week-nav-btn">Next Day ›</a>
    <?php else: ?>
      <span class="week-nav-btn disabled">Next Day ›</span>
    <?php endif; ?>
  </div>

  <?php if ($isCurrentNutritionDay): ?>
    <button class="add-btn" id="openMealModalBtn">+ Log Meal</button>
  <?php endif; ?>

  <section class="summary-card">
    <div class="calorie-row">
      <div class="calorie-ring" style="--pct: <?php echo $caloriePct; ?>;">
        <div class="calorie-ring-inner">
          <div class="calorie-value"><?php echo $dayCalories; ?></div>
          <div class="calorie-sep">/ <?php echo $calorieGoal; ?></div>
          <div class="calorie-unit">kcal</div>
        </div>
      </div>
      <div class="macro-list">
        <div><span class="dot dot-blue"></span> Protein: <strong><?php echo $dayProtein; ?>g</strong></div>
        <div><span class="dot dot-orange"></span> Carbs: <strong><?php echo $dayCarbs; ?>g</strong></div>
        <div><span class="dot dot-red"></span> Fat: <strong><?php echo $dayFat; ?>g</strong></div>

        <?php if ($isCurrentNutritionDay): ?>
        <form action="update_calorie_goal_process.php" method="POST" class="goal-form">
          <input type="hidden" name="ndate" value="<?php echo $nDate; ?>">
          <label>Daily Goal (kcal)</label>
          <input type="number" name="daily_calorie_goal" value="<?php echo $calorieGoal; ?>" min="500" step="50">
          <button type="submit" class="save-goal-btn">Save</button>
        </form>
        <?php endif; ?>
      </div>
    </div>
  </section>

  <section class="entries-list">
    <?php if (count($dayMeals) === 0): ?>
      <p class="empty-msg">No meals logged for this day.</p>
    <?php else: ?>
      <?php foreach ($dayMeals as $m): ?>
        <?php $icon = $mealIcons[$m["meal_type"]] ?? "🍽️"; ?>
        <div class="entry-row">
          <div class="entry-icon"><?php echo $icon; ?></div>
          <div class="entry-info">
            <strong><?php echo htmlspecialchars($m["meal_type"]); ?>: <?php echo htmlspecialchars($m["food_items"]); ?></strong>
            <span><?php echo $m["calories"]; ?> kcal &bull; P: <?php echo $m["protein"]; ?>g &bull; C: <?php echo $m["carbs"]; ?>g &bull; F: <?php echo $m["fat"]; ?>g</span>
          </div>
          <?php if ($isCurrentNutritionDay): ?>
            <form action="remove_meal_process.php" method="POST" onsubmit="return confirm('Remove this meal?');">
              <input type="hidden" name="meal_id" value="<?php echo $m['id']; ?>">
              <button type="submit" class="remove-btn">Remove</button>
            </form>
          <?php endif; ?>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>
</div>

<!-- ================= HEALTH VITALS TAB ================= -->
<div id="vitalsTab" class="tab-panel" style="display:<?php echo $activeTab === 'vitals' ? 'block' : 'none'; ?>;">

  <div class="vitals-header">
    <h2>Health Vitals Log</h2>
    <button class="add-btn-inline" id="openVitalModalBtn">+ Log Vital</button>
  </div>

  <div class="vitals-grid">
    <?php foreach ($vitalTypes as $type => $info): ?>
      <?php $latest = $latestVitals[$type] ?? null; ?>
      <div class="vital-card">
        <div class="vital-icon"><?php echo $info["icon"]; ?></div>
        <div class="vital-title"><?php echo $type; ?></div>
        <div class="vital-value"><?php echo $latest ? htmlspecialchars($latest["value"]) : "--"; ?></div>
        <div class="vital-unit"><?php echo $info["unit"]; ?></div>
      </div>
    <?php endforeach; ?>
  </div>

  <h3 class="list-heading">Recent Vital Readings</h3>
  <section class="entries-list">
    <?php if (count($allVitals) === 0): ?>
      <p class="empty-msg">No vitals logged yet.</p>
    <?php else: ?>
      <?php foreach ($allVitals as $v): ?>
        <?php $info = $vitalTypes[$v["vital_type"]] ?? ["icon" => "📊", "unit" => ""]; ?>
        <div class="entry-row">
          <div class="entry-icon"><?php echo $info["icon"]; ?></div>
          <div class="entry-info">
            <strong><?php echo htmlspecialchars($v["vital_type"]); ?></strong>
            <span class="vital-reading-value"><?php echo htmlspecialchars($v["value"]); ?> <?php echo $info["unit"]; ?></span>
            <?php if ($v["notes"]): ?><span class="vital-reading-notes"><?php echo htmlspecialchars($v["notes"]); ?></span><?php endif; ?>
          </div>
          <span class="vital-date"><?php echo $v["log_date"]; ?></span>
          <form action="remove_vital_process.php" method="POST" onsubmit="return confirm('Remove this reading?');">
            <input type="hidden" name="vital_id" value="<?php echo $v['id']; ?>">
            <button type="submit" class="remove-btn">Remove</button>
          </form>
        </div>
      <?php endforeach; ?>
    <?php endif; ?>
  </section>
</div>

<!-- ================= BMI CALCULATOR TAB ================= -->
<div id="bmiTab" class="tab-panel" style="display:<?php echo $activeTab === 'bmi' ? 'block' : 'none'; ?>;">
  <section class="bmi-section">
    <div class="bmi-card">
      <h2>BMI Calculator</h2>
      <label>Weight (kg)</label>
      <input type="number" id="bmiWeight" placeholder="e.g., 70">
      <label>Height (cm)</label>
      <input type="number" id="bmiHeight" placeholder="e.g., 170">
      <div class="bmi-result" id="bmiResult">Enter your weight and height to calculate BMI.</div>
    </div>
    <div class="bmi-card">
      <h2>BMI Categories</h2>
      <div class="bmi-cat bmi-under">Under 18.5 <span>Underweight</span></div>
      <div class="bmi-cat bmi-normal">18.5 — 24.9 <span>Normal</span></div>
      <div class="bmi-cat bmi-over">25 — 29.9 <span>Overweight</span></div>
      <div class="bmi-cat bmi-obese">30+ <span>Obese</span></div>
      <p class="bmi-note">BMI is a general indicator. Consult a doctor for a comprehensive health assessment that considers muscle mass, age, and other factors.</p>
    </div>
  </section>
</div>

<!-- ================= Log Workout modal ================= -->
<div class="modal-overlay" id="workoutModalOverlay">
  <div class="modal-box">
    <h2>🏆 Log Workout</h2>
    <form action="workout_process.php" method="POST">
      <label>Exercise Type</label>
      <select name="exercise_type" required>
        <option value="">Select</option>
        <option value="Running">Running</option>
        <option value="Weight Training">Weight Training</option>
        <option value="Yoga">Yoga</option>
        <option value="Cycling">Cycling</option>
        <option value="Walking">Walking</option>
        <option value="Swimming">Swimming</option>
        <option value="Other">Other</option>
      </select>
      <label>Duration (minutes)</label>
      <input type="number" name="duration_minutes" placeholder="e.g., 30" required min="1">
      <label>Calories Burned (approx)</label>
      <input type="number" name="calories_burned" placeholder="e.g., 250" required min="0">
      <div class="modal-buttons">
        <button type="button" class="cancel-btn" id="cancelWorkoutModalBtn">Cancel</button>
        <button type="submit" class="save-btn">Log Workout</button>
      </div>
    </form>
  </div>
</div>

<!-- ================= Log Meal modal ================= -->
<div class="modal-overlay" id="mealModalOverlay">
  <div class="modal-box">
    <h2>🍽️ Log Meal</h2>
    <form action="meal_process.php" method="POST">
      <label>Meal Type</label>
      <select name="meal_type" required>
        <option value="">Select</option>
        <option value="Breakfast">Breakfast</option>
        <option value="Lunch">Lunch</option>
        <option value="Snack">Snack</option>
        <option value="Dinner">Dinner</option>
      </select>
      <label>Food Items</label>
      <input type="text" name="food_items" placeholder="e.g., 2 eggs, toast, banana" required>
      <label>Calories (approx)</label>
      <input type="number" name="calories" placeholder="e.g., 450" required min="0">
      <label>Protein (g)</label>
      <input type="number" name="protein" placeholder="e.g., 25" value="0" min="0">
      <label>Carbs (g)</label>
      <input type="number" name="carbs" placeholder="e.g., 50" value="0" min="0">
      <label>Fat (g)</label>
      <input type="number" name="fat" placeholder="e.g., 15" value="0" min="0">
      <div class="modal-buttons">
        <button type="button" class="cancel-btn" id="cancelMealModalBtn">Cancel</button>
        <button type="submit" class="save-btn" style="background:#4caf7d;">Log Meal</button>
      </div>
    </form>
  </div>
</div>

<!-- ================= Log Vital modal ================= -->
<div class="modal-overlay" id="vitalModalOverlay">
  <div class="modal-box">
    <h2>📊 Log Vital</h2>
    <form action="vital_process.php" method="POST">
      <label>Vital Type</label>
      <select name="vital_type" required>
        <option value="">Select Type</option>
        <?php foreach ($vitalTypes as $type => $info): ?>
          <option value="<?php echo $type; ?>"><?php echo $type; ?></option>
        <?php endforeach; ?>
      </select>
      <label>Value</label>
      <input type="text" name="value" placeholder="e.g., 120/80 mmHg" required>
      <label>Notes (optional)</label>
      <input type="text" name="notes" placeholder="e.g., After morning walk">
      <div class="modal-buttons">
        <button type="button" class="cancel-btn" id="cancelVitalModalBtn">Cancel</button>
        <button type="submit" class="save-btn">Log Vital</button>
      </div>
    </form>
  </div>
</div>

<!-- ================= Day drill-down popup (read-only) ================= -->
<div class="modal-overlay" id="dayModalOverlay">
  <div class="modal-box">
    <h2 id="dayModalTitle"></h2>
    <div id="dayModalContent"></div>
    <div class="modal-buttons">
      <button type="button" class="cancel-btn" id="closeDayModalBtn">Close</button>
    </div>
  </div>
</div>

<script src="../js/home.js"></script>
<script src="../js/fitness.js"></script>
</body>
</html>