/* ---------- Global State & Fetching ---------- */
let currentWorkoutDate = new Date().toISOString().split('T')[0];
let currentNutritionDate = new Date().toISOString().split('T')[0];

document.addEventListener("DOMContentLoaded", () => {
    loadFitnessData();
});

// Fetch data from PHP API dynamically without reloading the page
async function loadFitnessData() {
    try {
        const response = await fetch(`../php/fitness.php?week=${currentWorkoutDate}&ndate=${currentNutritionDate}`);
        
        if (response.status === 401) {
            window.location.href = "login.html"; // Redirect if unauthenticated
            return;
        }

        const data = await response.json();
        
        // 1. Render Workouts Tab Data
        renderWorkouts(data);
        
        // 2. Render Nutrition Tab Data
        renderNutrition(data);
        
        // 3. Render Vitals Tab Data
        renderVitals(data);

    } catch (error) {
        console.error("Error loading fitness data:", error);
    }
}

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
    const tabs = { 
        workouts: [tabWorkoutsBtn, workoutsTab], 
        nutrition: [tabNutritionBtn, nutritionTab], 
        vitals: [tabVitalsBtn, vitalsTab], 
        bmi: [tabBmiBtn, bmiTab] 
    };

    Object.keys(tabs).forEach(function (key) {
        const [btn, panel] = tabs[key];
        if (btn && panel) {
            if (key === name) {
                btn.classList.add("active");
                panel.style.display = "block";
            } else {
                btn.classList.remove("active");
                panel.style.display = "none";
            }
        }
    });
}

if (tabWorkoutsBtn) tabWorkoutsBtn.addEventListener("click", () => activateTab("workouts"));
if (tabNutritionBtn) tabNutritionBtn.addEventListener("click", () => activateTab("nutrition"));
if (tabVitalsBtn) tabVitalsBtn.addEventListener("click", () => activateTab("vitals"));
if (tabBmiBtn) tabBmiBtn.addEventListener("click", () => activateTab("bmi"));

/* ---------- Date Pickers (AJAX-based, No Page Reload) ---------- */
const workoutDatePicker = document.getElementById("workoutDatePicker");
if (workoutDatePicker) {
    workoutDatePicker.addEventListener("change", function () {
        currentWorkoutDate = this.value;
        loadFitnessData();
    });
}

const nutritionDatePicker = document.getElementById("nutritionDatePicker");
if (nutritionDatePicker) {
    nutritionDatePicker.addEventListener("change", function () {
        currentNutritionDate = this.value;
        loadFitnessData();
    });
}

/* ---------- Render Workouts Tab & Interactive Bar Chart ---------- */
function renderWorkouts(data) {
    // Summary values
    if (document.getElementById("totalWorkouts")) document.getElementById("totalWorkouts").innerText = data.weekTotalWorkouts || 0;
    if (document.getElementById("totalMinutes")) document.getElementById("totalMinutes").innerText = data.weekTotalMinutes || 0;
    if (document.getElementById("totalCalories")) document.getElementById("totalCalories").innerText = data.weekTotalCal || 0;

    // Render Weekly Bar Chart
    const barChartContainer = document.getElementById("barChartContainer");
    if (barChartContainer && data.days) {
        barChartContainer.innerHTML = "";
        data.days.forEach(day => {
            const bar = document.createElement("div");
            bar.className = `bar ${day.isFuture ? 'bar-future' : ''}`;
            bar.style.height = `${day.heightPct || 10}%`;
            bar.setAttribute("data-day", JSON.stringify(day));

            bar.innerHTML = `
                <span class="bar-value">${day.minutes}m</span>
                <span class="bar-label">${day.label}</span>
            `;

            // Attach day detail popup event
            if (!day.isFuture) {
                bar.addEventListener("click", () => openDayModal(day));
            }
            barChartContainer.appendChild(bar);
        });
    }

    // Render Workout List
    const todayWorkoutsList = document.getElementById("todayWorkoutsList");
    if (todayWorkoutsList && data.weekWorkouts) {
        if (data.weekWorkouts.length === 0) {
            todayWorkoutsList.innerHTML = '<p class="empty-msg">No workouts logged for this period.</p>';
        } else {
            todayWorkoutsList.innerHTML = data.weekWorkouts.map(w => `
                <div class="day-detail-row">
                    <span><strong>${w.exercise_type}</strong></span>
                    <span>${w.duration_minutes} min · ${w.calories_burned} cal</span>
                </div>
            `).join('');
        }
    }
}

/* ---------- Render Nutrition Tab ---------- */
function renderNutrition(data) {
    if (document.getElementById("dayCaloriesVal")) document.getElementById("dayCaloriesVal").innerText = data.dayCalories || 0;
    if (document.getElementById("calorieGoalVal")) document.getElementById("calorieGoalVal").innerText = "/ " + (data.calorieGoal || 2000);
    if (document.getElementById("proteinVal")) document.getElementById("proteinVal").innerText = (data.dayProtein || 0) + "g";
    if (document.getElementById("carbsVal")) document.getElementById("carbsVal").innerText = (data.dayCarbs || 0) + "g";
    if (document.getElementById("fatVal")) document.getElementById("fatVal").innerText = (data.dayFat || 0) + "g";

    const dayMealsList = document.getElementById("dayMealsList");
    if (dayMealsList && data.dayMeals) {
        if (data.dayMeals.length === 0) {
            dayMealsList.innerHTML = '<p class="empty-msg">No meals logged for this day.</p>';
        } else {
            dayMealsList.innerHTML = data.dayMeals.map(m => `
                <div class="day-detail-row">
                    <span><strong>${m.meal_type}</strong>: ${m.food_items}</span>
                    <span>${m.calories} kcal</span>
                </div>
            `).join('');
        }
    }
}

/* ---------- Render Vitals Tab ---------- */
function renderVitals(data) {
    const allVitalsList = document.getElementById("allVitalsList");
    if (allVitalsList && data.allVitals) {
        if (data.allVitals.length === 0) {
            allVitalsList.innerHTML = '<p class="empty-msg">No vitals logged yet.</p>';
        } else {
            allVitalsList.innerHTML = data.allVitals.map(v => `
                <div class="day-detail-row">
                    <span><strong>${v.vital_type}</strong>: ${v.value}</span>
                    <span><em>${v.notes || ''}</em></span>
                </div>
            `).join('');
        }
    }
}

/* ---------- Day Drill-Down Popup (Clicking a Bar) ---------- */
const dayModalOverlay = document.getElementById("dayModalOverlay");
const dayModalTitle = document.getElementById("dayModalTitle");
const dayModalContent = document.getElementById("dayModalContent");
const closeDayModalBtn = document.getElementById("closeDayModalBtn");

function openDayModal(day) {
    if (!dayModalOverlay) return;
    
    dayModalTitle.textContent = "📊 " + day.label + " — " + day.date;
    if (!day.exercises || day.exercises.length === 0) {
        dayModalContent.innerHTML = '<p class="empty-msg">No workouts logged this day.</p>';
    } else {
        dayModalContent.innerHTML = day.exercises.map(ex => `
            <div class="day-detail-row">
                <span>${ex.exercise_type}</span>
                <span>${ex.duration_minutes} min · ${ex.calories_burned} cal</span>
            </div>
        `).join('');
    }
    dayModalOverlay.classList.add("show");
}

if (closeDayModalBtn) closeDayModalBtn.addEventListener("click", () => dayModalOverlay.classList.remove("show"));
if (dayModalOverlay) {
    dayModalOverlay.addEventListener("click", function (e) {
        if (e.target === dayModalOverlay) dayModalOverlay.classList.remove("show");
    });
}

/* ---------- Log Workout Modal ---------- */
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

/* ---------- Log Meal Modal ---------- */
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

/* ---------- Log Vital Modal ---------- */
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

/* ---------- BMI Calculator (Pure JS) ---------- */
const bmiWeight = document.getElementById("bmiWeight");
const bmiHeight = document.getElementById("bmiHeight");
const bmiResult = document.getElementById("bmiResult");

function calculateBmi() {
    if (!bmiWeight || !bmiHeight || !bmiResult) return;

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

if (bmiWeight) bmiWeight.addEventListener("input", calculateBmi);
if (bmiHeight) bmiHeight.addEventListener("input", calculateBmi);