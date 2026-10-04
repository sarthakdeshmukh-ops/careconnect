<?php
session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/db_connect.php';

// Auth check
if (!isset($_SESSION["user_id"])) {
    http_response_code(401);
    echo json_encode(["error" => "Unauthorized"]);
    exit();
}

$userId = $_SESSION["user_id"];

// Read date parameters passed via fetch URL
$weekDateParam = isset($_GET['week']) ? $_GET['week'] : date('Y-m-d');
$nutritionDateParam = isset($_GET['ndate']) ? $_GET['ndate'] : date('Y-m-d');

// -------------------------------------------------------------
// 1. WORKOUTS DATA & WEEKLY SUMMARY
// -------------------------------------------------------------
$today = new DateTime($weekDateParam);
$dayOfWeek = (int)$today->format('w'); // 0 = Sun, 1 = Mon, ...
$monday = clone $today;
$monday->modify('-' . ($dayOfWeek == 0 ? 6 : $dayOfWeek - 1) . ' days');

$sunday = clone $monday;
$sunday->modify('+6 days');

$startDateStr = $monday->format('Y-m-d');
$endDateStr = $sunday->format('Y-m-d');

// Fetch workouts for current week
$stmtWorkouts = $conn->prepare("SELECT * FROM workouts WHERE user_id = ? AND workout_date BETWEEN ? AND ? ORDER BY workout_date ASC");
$stmtWorkouts->bind_param("iss", $userId, $startDateStr, $endDateStr);
$stmtWorkouts->execute();
$weekWorkouts = $stmtWorkouts->get_result()->fetch_all(MYSQLI_ASSOC);

// Build daily chart structure (Mon-Sun)
$days = [];
$labels = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'];
$currIter = clone $monday;
$nowDate = new DateTime();

$weekTotalWorkouts = count($weekWorkouts);
$weekTotalMinutes = 0;
$weekTotalCal = 0;
$maxMinutes = 0;

for ($i = 0; $i < 7; $i++) {
    $dateStr = $currIter->format('Y-m-d');
    $dayExercises = array_filter($weekWorkouts, function($w) use ($dateStr) {
        return date('Y-m-d', strtotime($w['workout_date'])) === $dateStr;
    });

    $dayMins = 0;
    $dayCal = 0;
    foreach ($dayExercises as $ex) {
        $dayMins += $ex['duration_minutes'];
        $dayCal += $ex['calories_burned'];
    }

    $weekTotalMinutes += $dayMins;
    $weekTotalCal += $dayCal;
    if ($dayMins > $maxMinutes) $maxMinutes = $dayMins;

    $days[$dateStr] = [
        'label' => $labels[$i],
        'date' => $dateStr,
        'minutes' => $dayMins,
        'calories' => $dayCal,
        'exercises' => array_values($dayExercises),
        'isFuture' => $currIter > $nowDate
    ];
    $currIter->modify('+1 day');
}

// Compute chart height percentages
foreach ($days as &$d) {
    $d['heightPct'] = $maxMinutes > 0 ? round(($d['minutes'] / $maxMinutes) * 100) : 0;
}

// -------------------------------------------------------------
// 2. NUTRITION DATA
// -------------------------------------------------------------
$stmtMeals = $conn->prepare("SELECT * FROM meals WHERE user_id = ? AND DATE(meal_date) = ? ORDER BY meal_date ASC");
$stmtMeals->bind_param("is", $userId, $nutritionDateParam);
$stmtMeals->execute();
$dayMeals = $stmtMeals->get_result()->fetch_all(MYSQLI_ASSOC);

$dayCalories = 0;
$dayProtein = 0;
$dayCarbs = 0;
$dayFat = 0;
foreach ($dayMeals as $m) {
    $dayCalories += $m['calories'];
    $dayProtein += $m['protein'];
    $dayCarbs += $m['carbs'];
    $dayFat += $m['fat'];
}

// Calorie goal check
$stmtGoal = $conn->prepare("SELECT daily_calorie_goal FROM user_goals WHERE user_id = ?");
$calorieGoal = 2000;
if ($stmtGoal) {
    $stmtGoal->bind_param("i", $userId);
    $stmtGoal->execute();
    $resGoal = $stmtGoal->get_result()->fetch_assoc();
    if ($resGoal && isset($resGoal['daily_calorie_goal'])) {
        $calorieGoal = $resGoal['daily_calorie_goal'];
    }
}

// -------------------------------------------------------------
// 3. VITALS DATA
// -------------------------------------------------------------
$stmtVitals = $conn->prepare("SELECT * FROM vitals WHERE user_id = ? ORDER BY recorded_at DESC LIMIT 20");
$stmtVitals->bind_param("i", $userId);
$stmtVitals->execute();
$allVitals = $stmtVitals->get_result()->fetch_all(MYSQLI_ASSOC);

// Output JSON Payload
echo json_encode([
    'weekWorkouts'      => $weekWorkouts,
    'weekTotalWorkouts' => $weekTotalWorkouts,
    'weekTotalMinutes'  => $weekTotalMinutes,
    'weekTotalCal'      => $weekTotalCal,
    'days'              => array_values($days),
    'dayMeals'          => $dayMeals,
    'dayCalories'       => $dayCalories,
    'dayProtein'        => $dayProtein,
    'dayCarbs'          => $dayCarbs,
    'dayFat'            => $dayFat,
    'calorieGoal'       => $calorieGoal,
    'allVitals'         => $allVitals
]);
exit();
?>