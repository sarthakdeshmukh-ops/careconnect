<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/db_connect.php';

if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$userId = $_SESSION["user_id"];
$today = date("Y-m-d");

/* ============ Mood Scale Config ============ */
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

/* ============ Week Calculations ============ */
$weekParam = isset($_GET["week"]) ? $_GET["week"] : $today;
$weekDate = new DateTime($weekParam);
$dayOfWeek = (int)$weekDate->format("N");
$weekDate->modify("-" . ($dayOfWeek - 1) . " days");
$weekStart = $weekDate->format("Y-m-d");

$weekEndObj = clone $weekDate;
$weekEndObj->modify("+6 days");
$weekEnd = $weekEndObj->format("Y-m-d");

$todayObj = new DateTime($today);
$todayDow = (int)$todayObj->format("N");
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

/* ============ Fetch Mood Data ============ */
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
    $days[] = [
        "label" => $d->format("D"),
        "date"  => $dateStr,
        "mood"  => $moodByDate[$dateStr]["mood"] ?? null,
        "value" => (int)($moodByDate[$dateStr]["mood_value"] ?? 0)
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

// Fetch Today's Mood
$stmtToday = $conn->prepare("SELECT mood FROM mood_logs WHERE user_id = ? AND log_date = ?");
$stmtToday->bind_param("is", $userId, $today);
$stmtToday->execute();
$todayMoodRes = $stmtToday->get_result()->fetch_assoc();
$todayMood = $todayMoodRes["mood"] ?? null;

/* ============ Gratitude Journal ============ */
$stmtJournal = $conn->prepare("SELECT * FROM gratitude_entries WHERE user_id = ? ORDER BY created_at DESC");
$stmtJournal->bind_param("i", $userId);
$stmtJournal->execute();
$journalEntries = $stmtJournal->get_result()->fetch_all(MYSQLI_ASSOC);

/* ============ Output Payload ============ */
echo json_encode([
    "today"           => $today,
    "weekParam"       => $weekParam,
    "weekStart"       => $weekStart,
    "weekEnd"         => $weekEnd,
    "prevWeek"        => $prevWeek,
    "nextWeek"        => $nextWeek,
    "canGoNextWeek"   => $canGoNextWeek,
    "isCurrentWeek"   => $isCurrentWeek,
    "moodScale"       => $moodScale,
    "moodMessages"    => $moodMessages,
    "todayMood"       => $todayMood,
    "days"            => $days,
    "weekAverage"     => $weekAverage,
    "weekCategory"    => $weekCategory,
    "journalEntries"  => $journalEntries
]);
exit();
?>