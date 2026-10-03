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

$activeTab = isset($_GET["tab"]) ? $_GET["tab"] : "articles";

/* ============ Health Articles ============ */
$articles = [
    [
        "id" => 1,
        "category" => "Heart Health",
        "icon" => "❤️",
        "bg" => "#fde6e4",
        "title" => "5 Simple Habits to Keep Your Heart Healthy",
        "author" => "Dr. Arjun Patel",
        "date" => "Mar 10, 2026",
        "read_time" => "3 min read",
        "summary" => "Small daily changes like walking, eating fiber, and reducing salt can dramatically reduce heart disease risk.",
        "body" => "<p>Heart disease is the leading cause of death globally, but the good news is that many risk factors are preventable.</p>
<ol>
<li>Walk for 30 minutes daily — even a brisk walk counts as cardio.</li>
<li>Eat more fiber — whole grains, fruits, and vegetables help lower cholesterol.</li>
<li>Reduce salt intake — aim for less than 5g per day.</li>
<li>Manage stress — chronic stress raises blood pressure. Try meditation or deep breathing.</li>
<li>Get regular checkups — monitor blood pressure and cholesterol levels yearly.</li>
</ol>
<p>Small, consistent changes make a big difference over time. Start today!</p>"
    ],
    [
        "id" => 2,
        "category" => "Diabetes",
        "icon" => "🩸",
        "bg" => "#e6eefb",
        "title" => "Understanding Type 2 Diabetes: Prevention & Management",
        "author" => "Dr. Priya Sharma",
        "date" => "Mar 8, 2026",
        "read_time" => "4 min read",
        "summary" => "Learn about the risk factors, early signs, and lifestyle changes that can help prevent or manage Type 2 diabetes.",
        "body" => "<p>Type 2 diabetes affects how your body processes blood sugar (glucose). It develops when the body becomes resistant to insulin.</p>
<p>Risk factors include obesity, sedentary lifestyle, family history, and age over 45.</p>
<p><strong>Early warning signs:</strong></p>
<ul>
<li>Frequent urination</li>
<li>Increased thirst and hunger</li>
<li>Fatigue and blurred vision</li>
<li>Slow-healing wounds</li>
</ul>
<p><strong>Prevention tips:</strong></p>
<ul>
<li>Maintain a healthy weight</li>
<li>Exercise at least 150 minutes per week</li>
<li>Choose whole grains over refined carbohydrates</li>
<li>Monitor blood sugar levels regularly</li>
<li>Limit sugary drinks and processed foods</li>
</ul>
<p>If you experience any symptoms, consult a doctor promptly for proper testing.</p>"
    ],
    [
        "id" => 3,
        "category" => "Mental Health",
        "icon" => "🧠",
        "bg" => "#f0ebfb",
        "title" => "Breaking the Stigma: Why Mental Health Matters",
        "author" => "Dr. Nandini Rao",
        "date" => "Mar 5, 2026",
        "read_time" => "4 min read",
        "summary" => "Mental health is just as important as physical health. Here's how to recognize when you need help and where to find it.",
        "body" => "<p>One in four people globally will be affected by a mental health condition at some point in their lives. Yet stigma and misinformation prevent many from seeking help.</p>
<p><strong>Common signs you may need support:</strong></p>
<ul>
<li>Persistent sadness or anxiety lasting more than 2 weeks</li>
<li>Withdrawal from friends and activities you once enjoyed</li>
<li>Changes in sleep or appetite</li>
<li>Difficulty concentrating or making decisions</li>
<li>Feelings of worthlessness or hopelessness</li>
</ul>
<p><strong>What you can do:</strong></p>
<ul>
<li>Talk to someone you trust — a friend, family member, or counselor</li>
<li>Practice self-care: exercise, sleep well, eat nutritious food</li>
<li>Seek professional help — therapists and psychiatrists can help</li>
<li>Call a helpline if in crisis: iCall — 9152987821</li>
</ul>
<p>You are not alone. Recovery is real and help is available.</p>"
    ],
    [
        "id" => 4,
        "category" => "Nutrition",
        "icon" => "🥗",
        "bg" => "#e3f7ea",
        "title" => "Balanced Diet 101: What Your Plate Should Look Like",
        "author" => "Nutritionist Meera",
        "date" => "Mar 3, 2026",
        "read_time" => "3 min read",
        "summary" => "A guide to building a balanced meal with the right proportions of proteins, carbs, fats, and micronutrients.",
        "body" => "<p>A balanced diet provides all the nutrients your body needs to function properly.</p>
<p><strong>Ideal plate composition:</strong></p>
<ul>
<li>50% Vegetables and fruits — aim for variety and color</li>
<li>25% Protein — dal, paneer, eggs, lean meat, legumes</li>
<li>25% Whole grains — brown rice, roti, oats, millet</li>
<li>Include healthy fats — nuts, seeds, olive oil, ghee in moderation</li>
</ul>
<p><strong>Key tips:</strong></p>
<ul>
<li>Drink 2-3 liters of water daily</li>
<li>Eat 5 small meals instead of 3 heavy ones</li>
<li>Minimize processed and packaged foods</li>
<li>Include calcium-rich foods for bone health</li>
<li>If vegetarian, ensure adequate B12 and iron intake</li>
</ul>
<p>Remember: no single food is a superfood. Consistency and variety are what matter most.</p>"
    ],
    [
        "id" => 5,
        "category" => "Women's Health",
        "icon" => "👩",
        "bg" => "#fdf1de",
        "title" => "PCOS Awareness: Symptoms, Causes, and Lifestyle Tips",
        "author" => "Dr. Fatima Khan",
        "date" => "Feb 28, 2026",
        "read_time" => "4 min read",
        "summary" => "Polycystic Ovary Syndrome affects 1 in 10 women. Learn about its symptoms and how lifestyle changes can help.",
        "body" => "<p>PCOS (Polycystic Ovary Syndrome) is a common hormonal disorder among women of reproductive age.</p>
<p><strong>Common symptoms:</strong></p>
<ul>
<li>Irregular or missed periods</li>
<li>Excess hair growth (face, body)</li>
<li>Acne and oily skin</li>
<li>Weight gain, especially around the waist</li>
<li>Thinning hair on the scalp</li>
</ul>
<p><strong>Causes:</strong> The exact cause is unknown, but it involves insulin resistance, hormonal imbalance, and genetics.</p>
<p><strong>Lifestyle tips that help:</strong></p>
<ul>
<li>Regular exercise (30 min/day) — improves insulin sensitivity</li>
<li>Low-glycemic diet — avoid sugar spikes</li>
<li>Maintain a healthy weight — even 5-10% weight loss helps</li>
<li>Manage stress — cortisol worsens hormonal imbalance</li>
<li>Get 7-8 hours of sleep</li>
</ul>
<p>Consult a gynecologist for proper diagnosis. PCOS is manageable with the right approach.</p>"
    ],
    [
        "id" => 6,
        "category" => "Hygiene",
        "icon" => "🫧",
        "bg" => "#e6eefb",
        "title" => "Hand Hygiene: Your First Line of Defense Against Illness",
        "author" => "Community Health Team",
        "date" => "Feb 25, 2026",
        "read_time" => "2 min read",
        "summary" => "Proper handwashing is one of the most effective ways to prevent the spread of infections and diseases.",
        "body" => "<p>According to the WHO, handwashing with soap can reduce diarrheal diseases by 30% and respiratory infections by 20%.</p>
<p><strong>When to wash your hands:</strong></p>
<ul>
<li>Before and after eating</li>
<li>After using the restroom</li>
<li>After coughing, sneezing, or blowing your nose</li>
<li>After touching public surfaces</li>
<li>Before and after caring for a sick person</li>
</ul>
<p><strong>How to wash properly (20 seconds):</strong></p>
<ol>
<li>Wet hands with clean water</li>
<li>Apply soap and lather well</li>
<li>Scrub all surfaces — palms, backs, between fingers, under nails</li>
<li>Rinse thoroughly</li>
<li>Dry with a clean towel or air dry</li>
</ol>
<p>When soap isn't available, use a hand sanitizer with at least 60% alcohol.</p>"
    ],
    [
        "id" => 7,
        "category" => "Children's Health",
        "icon" => "👶",
        "bg" => "#e3f7ea",
        "title" => "Keeping Children Healthy: Nutrition & Vaccination Guide",
        "author" => "Dr. Amit Joshi",
        "date" => "Feb 20, 2026",
        "read_time" => "4 min read",
        "summary" => "Essential tips for child nutrition, growth milestones, and recommended vaccinations from birth to age 5.",
        "body" => "<p>The first five years are critical for a child's growth and development.</p>
<p><strong>Nutrition essentials:</strong></p>
<ul>
<li>Exclusive breastfeeding for the first 6 months</li>
<li>Introduce solid foods gradually after 6 months</li>
<li>Include iron-rich foods — spinach, lentils, fortified cereals</li>
<li>Ensure adequate calcium — milk, curd, cheese</li>
<li>Limit sugar and salt in meals</li>
</ul>
<p><strong>Key vaccinations (India schedule):</strong></p>
<ul>
<li>BCG, OPV, Hepatitis B — at birth</li>
<li>DPT, IPV, Rotavirus — 6, 10, 14 weeks</li>
<li>Measles, Vitamin A — 9 months</li>
<li>MMR, Varicella — 12-15 months</li>
<li>Boosters — 16-24 months</li>
</ul>
<p>Regular pediatric checkups help track growth milestones and catch issues early. Consult your pediatrician for personalized guidance.</p>"
    ]
];

$categories = array_unique(array_column($articles, "category"));
sort($categories);

/* ============ Vaccination Drives ============ */
$drives = [
    ["name" => "COVID-19 Booster Drive", "date" => "Mar 18-20, 2026", "location" => "City General Hospital, Sector 5", "age" => "18+", "status" => "Upcoming"],
    ["name" => "Polio Immunization (Pulse Polio)", "date" => "Mar 25, 2026", "location" => "All Govt. Health Centers", "age" => "0-5 years", "status" => "Upcoming"],
    ["name" => "Hepatitis B Awareness Camp", "date" => "Apr 2, 2026", "location" => "Community Hall, Nehru Nagar", "age" => "All ages", "status" => "Upcoming"],
    ["name" => "Flu Vaccination Camp", "date" => "Feb 15, 2026", "location" => "Apollo Clinic, Station Road", "age" => "All ages", "status" => "Completed"],
    ["name" => "HPV Vaccination (Girls)", "date" => "Apr 10, 2026", "location" => "Govt. Girls School, Ward 7", "age" => "9-14 years", "status" => "Upcoming"]
];

/* ============ Disease Prevention ============ */
$preventionTopics = [
    ["icon" => "🫁", "title" => "Respiratory Infections", "tips" => ["Wash hands frequently with soap", "Wear a mask in crowded places", "Cover mouth while coughing/sneezing", "Get annual flu vaccination", "Keep rooms well-ventilated"]],
    ["icon" => "🦟", "title" => "Mosquito-borne Diseases", "tips" => ["Eliminate stagnant water around home", "Use mosquito nets while sleeping", "Apply mosquito repellent", "Wear long-sleeved clothing at dusk", "Report dengue/malaria symptoms early"]],
    ["icon" => "🍽️", "title" => "Food & Waterborne Diseases", "tips" => ["Drink boiled or filtered water only", "Wash fruits and vegetables thoroughly", "Cook food at proper temperatures", "Avoid street food during monsoon", "Store food in clean containers"]],
    ["icon" => "❤️", "title" => "Cardiovascular Disease", "tips" => ["Exercise 30 minutes daily", "Reduce salt and trans-fat intake", "Monitor blood pressure regularly", "Quit smoking and limit alcohol", "Manage stress with relaxation techniques"]],
    ["icon" => "🩸", "title" => "Diabetes Prevention", "tips" => ["Maintain healthy body weight", "Choose whole grains over refined carbs", "Exercise regularly — walk, swim, cycle", "Get blood sugar checked yearly after 35", "Limit sugary beverages and sweets"]],
    ["icon" => "🧠", "title" => "Mental Health Awareness", "tips" => ["Talk about your feelings openly", "Stay physically active", "Maintain a consistent sleep schedule", "Limit social media screen time", "Seek professional help when needed"]]
];

/* ============ Emergency Info ============ */
$emergencyNumbers = [
    ["label" => "Ambulance", "number" => "108"],
    ["label" => "Police", "number" => "100"],
    ["label" => "Fire", "number" => "101"],
    ["label" => "Women Helpline", "number" => "1091"],
    ["label" => "Child Helpline", "number" => "1098"],
    ["label" => "Mental Health", "number" => "9152987821"]
];

$nearbyHospitals = [
    ["name" => "City General Hospital", "info" => "MG Road, Sector 5 • Open 24/7 • Emergency: Yes"],
    ["name" => "Apollo Clinic", "info" => "Station Road, Block C • 8 AM – 10 PM • Emergency: Yes"],
    ["name" => "Community Health Center", "info" => "Nehru Nagar, Ward 3 • 9 AM – 5 PM • Emergency: No"],
    ["name" => "LifeCare Multi-Specialty", "info" => "Ring Road, Plot 12 • Open 24/7 • Emergency: Yes"]
];

$activePage = "community";
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Community Health - CareConnection+</title>
<link rel="stylesheet" href="../css/navbar.css">
<link rel="stylesheet" href="../css/community.css">
</head>
<body>

<?php include "includes/navbar.php"; ?>

<section class="page-header">
  <h1>🏥 Community Health Portal</h1>
  <p>Stay informed with health awareness content, vaccination info, and disease prevention tips.</p>
</section>

<div class="tabs">
  <button class="tab-btn <?php echo $activeTab === 'articles' ? 'active' : ''; ?>" id="tabArticlesBtn">Health Articles</button>
  <button class="tab-btn <?php echo $activeTab === 'drives' ? 'active' : ''; ?>" id="tabDrivesBtn">Vaccination Drives</button>
  <button class="tab-btn <?php echo $activeTab === 'prevention' ? 'active' : ''; ?>" id="tabPreventionBtn">Disease Prevention</button>
  <button class="tab-btn <?php echo $activeTab === 'emergency' ? 'active' : ''; ?>" id="tabEmergencyBtn">Emergency Info</button>
</div>

<!-- ================= HEALTH ARTICLES TAB ================= -->
<div id="articlesTab" class="tab-panel" style="display:<?php echo $activeTab === 'articles' ? 'block' : 'none'; ?>;">
  <section class="filters">
    <input type="text" id="searchBox" placeholder="Search health articles...">
    <select id="categoryFilter">
      <option value="all">All Categories</option>
      <?php foreach ($categories as $cat): ?>
        <option value="<?php echo htmlspecialchars($cat); ?>"><?php echo htmlspecialchars($cat); ?></option>
      <?php endforeach; ?>
    </select>
  </section>

  <section class="article-grid" id="articleGrid">
    <?php foreach ($articles as $article): ?>
      <div class="article-card"
           data-title="<?php echo strtolower(htmlspecialchars($article['title'])); ?>"
           data-category="<?php echo htmlspecialchars($article['category']); ?>"
           data-id="<?php echo $article['id']; ?>">
        <div class="article-banner" style="background:<?php echo $article['bg']; ?>;">
          <span class="article-emoji"><?php echo $article['icon']; ?></span>
        </div>
        <div class="article-body">
          <span class="article-cat"><?php echo strtoupper(htmlspecialchars($article['category'])); ?></span>
          <h3><?php echo htmlspecialchars($article['title']); ?></h3>
          <p><?php echo htmlspecialchars($article['summary']); ?></p>
          <div class="article-meta">
            <span><?php echo htmlspecialchars($article['author']); ?></span>
            <span><?php echo htmlspecialchars($article['read_time']); ?></span>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </section>
  <p class="no-results" id="noResults" style="display:none;">No articles match your search.</p>
</div>

<!-- ================= VACCINATION DRIVES TAB ================= -->
<div id="drivesTab" class="tab-panel" style="display:<?php echo $activeTab === 'drives' ? 'block' : 'none'; ?>;">
  <section class="entries-list">
    <?php foreach ($drives as $drive): ?>
      <div class="drive-row">
        <div class="drive-icon">💉</div>
        <div class="drive-info">
          <strong><?php echo htmlspecialchars($drive['name']); ?></strong>
          <span>📅 <?php echo htmlspecialchars($drive['date']); ?> &nbsp; 📍 <?php echo htmlspecialchars($drive['location']); ?> &nbsp; 👥 <?php echo htmlspecialchars($drive['age']); ?></span>
        </div>
        <span class="badge <?php echo $drive['status'] === 'Upcoming' ? 'badge-upcoming' : 'badge-completed'; ?>"><?php echo $drive['status']; ?></span>
      </div>
    <?php endforeach; ?>
  </section>
</div>

<!-- ================= DISEASE PREVENTION TAB ================= -->
<div id="preventionTab" class="tab-panel" style="display:<?php echo $activeTab === 'prevention' ? 'block' : 'none'; ?>;">
  <section class="prevention-grid">
    <?php foreach ($preventionTopics as $topic): ?>
      <div class="prevention-card">
        <div class="prevention-icon"><?php echo $topic['icon']; ?></div>
        <h3><?php echo htmlspecialchars($topic['title']); ?></h3>
        <ul>
          <?php foreach ($topic['tips'] as $tip): ?>
            <li><?php echo htmlspecialchars($tip); ?></li>
          <?php endforeach; ?>
        </ul>
      </div>
    <?php endforeach; ?>
  </section>
</div>

<!-- ================= EMERGENCY INFO TAB ================= -->
<div id="emergencyTab" class="tab-panel" style="display:<?php echo $activeTab === 'emergency' ? 'block' : 'none'; ?>;">
  <section class="panel-block">
    <h2>🚑 Emergency Numbers</h2>
    <div class="emergency-grid">
      <?php foreach ($emergencyNumbers as $e): ?>
        <div class="emergency-card">
          <span class="emergency-label"><?php echo htmlspecialchars($e['label']); ?></span>
          <span class="emergency-number"><?php echo htmlspecialchars($e['number']); ?></span>
        </div>
      <?php endforeach; ?>
    </div>
  </section>

  <section class="panel-block">
    <h2>🏥 Nearby Hospitals (Sample Data)</h2>
    <div class="hospital-list">
      <?php foreach ($nearbyHospitals as $h): ?>
        <div class="hospital-row">
          <span class="hospital-icon">🏥</span>
          <div>
            <strong><?php echo htmlspecialchars($h['name']); ?></strong>
            <span><?php echo htmlspecialchars($h['info']); ?></span>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  </section>
</div>

<!-- ================= Article popup ================= -->
<div class="modal-overlay" id="articleModalOverlay">
  <div class="modal-box article-modal-box">
    <span class="modal-cat-badge" id="modalCatBadge"></span>
    <h2 id="modalTitle"></h2>
    <p class="modal-byline" id="modalByline"></p>
    <div class="modal-article-body" id="modalBody"></div>
    <div class="modal-buttons">
      <button type="button" class="cancel-btn" id="closeArticleModalBtn">Close</button>
    </div>
  </div>
</div>

<script>
const articlesData = <?php echo json_encode($articles); ?>;
</script>
<script src="../js/home.js"></script>
<script src="../js/community.js"></script>
</body>
</html>