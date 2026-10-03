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

$today = date("Y-m-d");

/* ============ Mood scale ============ */
$moodScale = [
    "Great" => ["value" => 90, "emoji" => "😄"],
    "Good"  => ["value" => 70, "emoji" => "🙂"],
    "Okay"  => ["value" => 50, "emoji" => "😐"],
    "Low"   => ["value" => 30, "emoji" => "😔"],
    "Awful" => ["value" => 10, "emoji" => "😢"]
];

$moodMessages = [
    "Awful" => "We're sorry to hear that. Please talk to someone you trust. 💜",
    "Low"   => "Hang in there. Try the breathing exercise below. 💙",
    "Okay"  => "It's okay to have neutral days. Take it easy. 🍃",
    "Good"  => "Nice! You're doing well — keep it up! 💚",
    "Great" => "Wonderful! Keep spreading that positive energy! 🌟"
];

/* ============ Build the selected week ============ */
$weekParam = isset($_GET["week"]) ? $_GET["week"] : $today;
$weekDate = new DateTime($weekParam);
$dayOfWeek = (int) $weekDate->format("N");
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
$isCurrentWeek = ($weekStart === $currentWeekStart);

$prevWeekObj = clone $weekDate;
$prevWeekObj->modify("-7 days");
$prevWeek = $prevWeekObj->format("Y-m-d");

$nextWeekObj = clone $weekDate;
$nextWeekObj->modify("+7 days");
$canGoNextWeek = ($nextWeekObj <= new DateTime($currentWeekStart));
$nextWeek = $nextWeekObj->format("Y-m-d");

$stmt = $conn->prepare("SELECT * FROM mood_logs WHERE user_id = ? AND log_date BETWEEN ? AND ?");
$stmt->bind_param("iss", $userId, $weekStart, $weekEnd);
$stmt->execute();
$weekMoods = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$moodByDate = [];
foreach ($weekMoods as $m) {
    $moodByDate[$m["log_date"]] = $m;
}

$days = [];
for ($i = 0; $i < 7; $i++) {
    $d = clone $weekDate;
    $d->modify("+$i days");
    $dateStr = $d->format("Y-m-d");
    $days[$dateStr] = [
        "label" => $d->format("D"),
        "date" => $dateStr,
        "mood" => $moodByDate[$dateStr]["mood"] ?? null,
        "value" => $moodByDate[$dateStr]["mood_value"] ?? 0
    ];
}

$loggedValues = array_filter(array_column($days, "value"), function ($v) { return $v > 0; });
$weekAverage = count($loggedValues) > 0 ? round(array_sum($loggedValues) / count($loggedValues)) : null;

function categoryFromScore($score) {
    if ($score <= 20) return "Awful";
    if ($score <= 40) return "Low";
    if ($score <= 60) return "Okay";
    if ($score <= 80) return "Good";
    return "Great";
}
$weekCategory = $weekAverage !== null ? categoryFromScore($weekAverage) : null;

/* ============ Today's mood (for the "How are you feeling" section) ============ */
$todayMood = $moodByDate[$today]["mood"] ?? null;

/* ============ Gratitude journal ============ */
$stmt = $conn->prepare("SELECT * FROM gratitude_entries WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $userId);
$stmt->execute();
$journalEntries = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

/* ============ Daily affirmation ============ */
$affirmations = [
    "You are stronger than you think, and today is proof of that.",
    "Small steps still move you forward.",
    "Your feelings are valid, and so is your progress.",
    "You don't have to have it all figured out today.",
    "Rest is productive too.",
    "You've survived every hard day so far — that's a 100% success rate.",
    "It's okay to ask for help. That's strength, not weakness.",
    "You are allowed to take up space and be heard.",
    "Progress, not perfection.",
    "Today is a new page — write something kind on it.",
    "You are doing better than you think you are.",
    "Breathe. You've got this.",
    "Your worth isn't measured by your productivity.",
    "Be gentle with yourself — you're doing the best you can.",
    "Every storm runs out of rain eventually.",
    "You are not your worst day.",
    "Healing isn't linear, and that's completely okay.",
    "You bring value just by being you.",
    "One day at a time is enough.",
    "You are capable of amazing things.",
    "Your story isn't over yet.",
    "It's okay to outgrow people, places, and habits.",
    "You deserve the same kindness you give to others.",
    "Difficult roads often lead to beautiful destinations. Keep going.",
    "You are enough, exactly as you are right now.",
    "Growth is quiet before it is loud.",
    "Trust the process — you're closer than you think.",
    "Your mental health matters just as much as your physical health.",
    "Taking a break doesn't mean giving up.",
    "You have survived 100% of your hardest days.",
    "Be proud of how far you've come.",
    "You are allowed to prioritize your peace.",
    "Even the darkest night will end, and the sun will rise.",
    "You are worthy of love and belonging.",
    "This feeling is temporary — you are not.",
    "Celebrate the small wins, they add up."
];
$todaysAffirmation = $affirmations[array_rand($affirmations)];

$activePage = "mental-health";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Mental Health - CareConnection+</title>
<link rel="stylesheet" href="../css/navbar.css">
<link rel="stylesheet" href="../css/mental-health.css">
</head>
<body>

<?php include "includes/navbar.php"; ?>

<section class="page-header">
  <h1>🧠 Mental Health & Wellness</h1>
  <p>Track your mood, practice mindfulness, and nurture your mental well-being daily.</p>
</section>

<?php if ($isCurrentWeek): ?>
<section class="mood-card">
  <h2>How are you feeling today?</h2>
  <div class="mood-buttons">
    <?php foreach ($moodScale as $moodName => $info): ?>
      <form action="mood_process.php" method="POST" class="mood-form">
        <input type="hidden" name="mood" value="<?php echo $moodName; ?>">
        <button type="submit" class="mood-btn <?php echo $todayMood === $moodName ? 'selected' : ''; ?>">
          <span class="mood-emoji"><?php echo $info["emoji"]; ?></span>
          <span class="mood-label"><?php echo $moodName; ?></span>
        </button>
      </form>
    <?php endforeach; ?>
  </div>

  <?php if ($todayMood): ?>
    <div class="mood-message"><?php echo $moodMessages[$todayMood]; ?></div>
  <?php endif; ?>
</section>
<?php endif; ?>

<div class="two-col">
  <section class="panel">
      <div class="week-nav">
      <a href="mental-health.php?week=<?php echo $prevWeek; ?>" class="week-nav-btn">‹ Prev</a>
      <div class="week-nav-center">
        <span class="week-range"><?php echo (new DateTime($weekStart))->format("d M"); ?> — <?php echo (new DateTime($weekEnd))->format("d M Y"); ?></span>
        <input type="date" id="moodDatePicker" value="<?php echo $weekParam; ?>" max="<?php echo $today; ?>">
      </div>
      <?php if ($canGoNextWeek): ?>
        <a href="mental-health.php?week=<?php echo $nextWeek; ?>" class="week-nav-btn">Next ›</a>
      <?php else: ?>
        <span class="week-nav-btn disabled">Next ›</span>
      <?php endif; ?>
    </div>

    <h2>Mood History</h2>

    <div class="mood-chart">
      <?php foreach ($days as $dateStr => $day): ?>
        <?php
          $hasData = $day["value"] > 0;
          $barHeight = $hasData ? $day["value"] : 4;
          $dayEmoji = $hasData ? $moodScale[$day["mood"]]["emoji"] : "–";
        ?>
        <div class="mood-col">
          <span class="mood-day-emoji"><?php echo $dayEmoji; ?></span>
          <div class="mood-bar <?php echo !$hasData ? 'mood-bar-empty' : ''; ?>"
               style="height: <?php echo $barHeight; ?>%;"
               title="<?php echo $dateStr . ($hasData ? ' — ' . $day['mood'] : ' — no entry'); ?>"></div>
          <span class="mood-day-label"><?php echo $day["label"]; ?></span>
        </div>
      <?php endforeach; ?>
    </div>

    <?php if ($weekAverage !== null): ?>
      <div class="week-average">
        Week average: <strong><?php echo $weekAverage; ?>/100</strong> — <strong><?php echo $weekCategory; ?></strong>
      </div>
    <?php else: ?>
      <div class="week-average">No mood entries logged for this week yet.</div>
    <?php endif; ?>
  </section>

  <section class="panel">
    <h2>🌬️ Breathing Exercise</h2>
    <p class="panel-subtext">4-7-8 technique — inhale 4s, hold 7s, exhale 8s. Repeat for calmness.</p>
    <div class="breathing-wrap">
      <div class="breathing-circle" id="breathingCircle">
        <span id="breathingText">Start</span>
      </div>
      <button class="add-btn" id="beginBreathingBtn">Begin Exercise</button>
    </div>
  </section>
</div>

<div class="two-col">
  <section class="panel">
    <h2>📝 Gratitude Journal</h2>
    <p class="panel-subtext">Write down something you're grateful for today.</p>
    <form action="gratitude_process.php" method="POST">
      <textarea name="entry_text" placeholder="Today I am grateful for..." rows="3" required></textarea>
      <button type="submit" class="save-btn-green">Save Entry</button>
    </form>

    <div class="journal-list">
      <?php if (count($journalEntries) === 0): ?>
        <p class="empty-msg">No entries yet.</p>
      <?php else: ?>
        <?php foreach ($journalEntries as $entry): ?>
          <div class="journal-entry">
            <span class="journal-date"><?php echo date("Y-m-d", strtotime($entry["created_at"])); ?></span>
            <p><?php echo nl2br(htmlspecialchars($entry["entry_text"])); ?></p>
          </div>
        <?php endforeach; ?>
      <?php endif; ?>
    </div>
  </section>

  <section class="panel">
    <h2>💜 Daily Affirmation</h2>
    <div class="affirmation-box" id="affirmationBox">
      "<?php echo htmlspecialchars($todaysAffirmation); ?>"
    </div>
    <button class="add-btn" id="newAffirmationBtn">New Affirmation</button>

    <h3 class="wellness-heading">🧠 Wellness Tips</h3>
    <div class="wellness-tips">
      <div class="tip">Take 5-minute breaks every hour — your brain needs rest.</div>
      <div class="tip">Limit social media to 30 mins/day for better mental clarity.</div>
      <div class="tip">Talk to someone you trust when you're feeling overwhelmed.</div>
      <div class="tip">Sleep and wake at the same time daily for mood stability.</div>
      <div class="tip">Move your body for at least 20 minutes — exercise lifts mood.</div>
    </div>
  </section>
</div>

<section class="crisis-banner">
  <h3>Need someone to talk to?</h3>
  <p>If you're in crisis or need immediate support, please reach out.</p>
  <div class="crisis-contacts">
    <div><strong>iCall</strong><br>9152987821</div>
    <div><strong>Vandrevala Foundation</strong><br>1860-2662-345</div>
    <div><strong>NIMHANS</strong><br>080-46110007</div>
  </div>
</section>

<!-- Affirmations list duplicated here so "New Affirmation" can randomize without a page reload -->
<script>
const affirmationsList = <?php echo json_encode($affirmations); ?>;
</script>
<script src="../js/home.js"></script>
<script src="../js/mental-health.js"></script>
</body>
</html>