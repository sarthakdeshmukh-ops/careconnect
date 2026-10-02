/* ---------- Tab switching ---------- */
const tabWorkoutsBtn = document.getElementById("tabWorkoutsBtn");
const tabNutritionBtn = document.getElementById("tabNutritionBtn");
const tabVitalsBtn = document.getElementById("tabVitalsBtn");
const tabBmiBtn = document.getElementById("tabBmiBtn");
const workoutsTab = document.getElementById("workoutsTab");
const nutritionTab = document.getElementById("nutritionTab");
const vitalsTab = document.getElementById("vitalsTab");
const bmiTab = document.getElementById("bmiTab");

function activateTab(name) {
    const tabs = { workouts: [tabWorkoutsBtn, workoutsTab], nutrition: [tabNutritionBtn, nutritionTab], vitals: [tabVitalsBtn, vitalsTab], bmi: [tabBmiBtn, bmiTab] };
    Object.keys(tabs).forEach(function (key) {
        const [btn, panel] = tabs[key];
        if (key === name) {
            btn.classList.add("active");
            panel.style.display = "block";
        } else {
            btn.classList.remove("active");
            panel.style.display = "none";
        }
    });
}

tabWorkoutsBtn.addEventListener("click", () => activateTab("workouts"));
tabNutritionBtn.addEventListener("click", () => activateTab("nutrition"));
tabVitalsBtn.addEventListener("click", () => activateTab("vitals"));
tabBmiBtn.addEventListener("click", () => activateTab("bmi"));

/* ---------- Date pickers navigate via URL ---------- */
const workoutDatePicker = document.getElementById("workoutDatePicker");
if (workoutDatePicker) {
    workoutDatePicker.addEventListener("change", function () {
        window.location.href = "fitness.php?tab=workouts&week=" + this.value;
    });
}
const nutritionDatePicker = document.getElementById("nutritionDatePicker");
if (nutritionDatePicker) {
    nutritionDatePicker.addEventListener("change", function () {
        window.location.href = "fitness.php?tab=nutrition&ndate=" + this.value;
    });
}

/* ---------- Log Workout modal ---------- */
const openWorkoutModalBtn = document.getElementById("openWorkoutModalBtn");
const workoutModalOverlay = document.getElementById("workoutModalOverlay");
const cancelWorkoutModalBtn = document.getElementById("cancelWorkoutModalBtn");
if (openWorkoutModalBtn) {
    openWorkoutModalBtn.addEventListener("click", () => workoutModalOverlay.classList.add("show"));
    cancelWorkoutModalBtn.addEventListener("click", () => workoutModalOverlay.classList.remove("show"));
    workoutModalOverlay.addEventListener("click", function (e) {
        if (e.target === workoutModalOverlay) workoutModalOverlay.classList.remove("show");
    });
}

/* ---------- Log Meal modal ---------- */
const openMealModalBtn = document.getElementById("openMealModalBtn");
const mealModalOverlay = document.getElementById("mealModalOverlay");
const cancelMealModalBtn = document.getElementById("cancelMealModalBtn");
if (openMealModalBtn) {
    openMealModalBtn.addEventListener("click", () => mealModalOverlay.classList.add("show"));
    cancelMealModalBtn.addEventListener("click", () => mealModalOverlay.classList.remove("show"));
    mealModalOverlay.addEventListener("click", function (e) {
        if (e.target === mealModalOverlay) mealModalOverlay.classList.remove("show");
    });
}

/* ---------- Day drill-down popup (click a bar) ---------- */
const dayModalOverlay = document.getElementById("dayModalOverlay");
const dayModalTitle = document.getElementById("dayModalTitle");
const dayModalContent = document.getElementById("dayModalContent");
const closeDayModalBtn = document.getElementById("closeDayModalBtn");

document.querySelectorAll(".bar:not(.bar-future)").forEach(function (bar) {
    bar.addEventListener("click", function () {
        const day = JSON.parse(this.getAttribute("data-day"));
        dayModalTitle.textContent = "📊 " + day.label + " — " + day.date;

        if (day.exercises.length === 0) {
            dayModalContent.innerHTML = '<p class="empty-msg">No workouts logged this day.</p>';
        } else {
            let html = "";
            day.exercises.forEach(function (ex) {
                html += `<div class="day-detail-row"><span>${ex.exercise_type}</span><span>${ex.duration_minutes} min · ${ex.calories_burned} cal</span></div>`;
            });
            dayModalContent.innerHTML = html;
        }
        dayModalOverlay.classList.add("show");
    });
});
closeDayModalBtn.addEventListener("click", () => dayModalOverlay.classList.remove("show"));
dayModalOverlay.addEventListener("click", function (e) {
    if (e.target === dayModalOverlay) dayModalOverlay.classList.remove("show");
});

/* ---------- BMI Calculator (pure JS, no backend) ---------- */
const bmiWeight = document.getElementById("bmiWeight");
const bmiHeight = document.getElementById("bmiHeight");
const bmiResult = document.getElementById("bmiResult");

function calculateBmi() {
    const weight = parseFloat(bmiWeight.value);
    const heightCm = parseFloat(bmiHeight.value);

    if (!weight || !heightCm) {
        bmiResult.textContent = "Enter your weight and height to calculate BMI.";
        return;
    }

    const heightM = heightCm / 100;
    const bmi = weight / (heightM * heightM);
    let category = "";
    if (bmi < 18.5) category = "Underweight";
    else if (bmi < 25) category = "Normal";
    else if (bmi < 30) category = "Overweight";
    else category = "Obese";

    bmiResult.innerHTML = `Your BMI is <strong>${bmi.toFixed(1)}</strong> — <strong>${category}</strong>`;
}

bmiWeight.addEventListener("input", calculateBmi);
bmiHeight.addEventListener("input", calculateBmi);

/* ---------- Log Vital modal ---------- */
const openVitalModalBtn = document.getElementById("openVitalModalBtn");
const vitalModalOverlay = document.getElementById("vitalModalOverlay");
const cancelVitalModalBtn = document.getElementById("cancelVitalModalBtn");
if (openVitalModalBtn) {
    openVitalModalBtn.addEventListener("click", () => vitalModalOverlay.classList.add("show"));
    cancelVitalModalBtn.addEventListener("click", () => vitalModalOverlay.classList.remove("show"));
    vitalModalOverlay.addEventListener("click", function (e) {
        if (e.target === vitalModalOverlay) vitalModalOverlay.classList.remove("show");
    });
}