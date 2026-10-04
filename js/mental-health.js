/* ---------- State Variables ---------- */
let currentWeekDate = new Date().toISOString().split('T')[0];
let prevWeekStr = "";
let nextWeekStr = "";

const affirmationsList = [
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

/* ---------- Page Load & Fetch ---------- */
document.addEventListener("DOMContentLoaded", () => {
    loadMentalHealthData();
    initAffirmations();
});

async function loadMentalHealthData() {
    try {
        const response = await fetch(`../php/mental-health.php?week=${currentWeekDate}`);

        if (response.status === 401) {
            window.location.href = "login.html";
            return;
        }

        const data = await response.json();

        // Update Week Navigation Limits & Text
        prevWeekStr = data.prevWeek;
        nextWeekStr = data.nextWeek;

        const datePicker = document.getElementById("moodDatePicker");
        if (datePicker) {
            datePicker.value = data.weekParam;
            datePicker.max = data.today;
        }

        const weekRangeText = document.getElementById("weekRangeText");
        if (weekRangeText) {
            const startDateFormatted = formatDateString(data.weekStart, false);
            const endDateFormatted = formatDateString(data.weekEnd, true);
            weekRangeText.textContent = `${startDateFormatted} — ${endDateFormatted}`;
        }

        const nextBtn = document.getElementById("nextWeekBtn");
        if (nextBtn) {
            if (data.canGoNextWeek) {
                nextBtn.classList.remove("disabled");
                nextBtn.disabled = false;
            } else {
                nextBtn.classList.add("disabled");
                nextBtn.disabled = true;
            }
        }

        // Render Components
        renderTodayMood(data);
        renderMoodChart(data);
        renderGratitudeJournal(data.journalEntries);

    } catch (error) {
        console.error("Error fetching mental health data:", error);
    }
}

/* ---------- Date Navigation ---------- */
const moodDatePicker = document.getElementById("moodDatePicker");
if (moodDatePicker) {
    moodDatePicker.addEventListener("change", function () {
        currentWeekDate = this.value;
        loadMentalHealthData();
    });
}

const prevWeekBtn = document.getElementById("prevWeekBtn");
if (prevWeekBtn) {
    prevWeekBtn.addEventListener("click", () => {
        if (prevWeekStr) {
            currentWeekDate = prevWeekStr;
            loadMentalHealthData();
        }
    });
}

const nextWeekBtn = document.getElementById("nextWeekBtn");
if (nextWeekBtn) {
    nextWeekBtn.addEventListener("click", () => {
        if (nextWeekStr && !nextWeekBtn.disabled) {
            currentWeekDate = nextWeekStr;
            loadMentalHealthData();
        }
    });
}

/* ---------- Today's Mood Section ---------- */
function renderTodayMood(data) {
    const todaySection = document.getElementById("todayMoodSection");
    const buttonsContainer = document.getElementById("moodButtonsContainer");
    const messageContainer = document.getElementById("moodMessageContainer");

    if (!todaySection || !buttonsContainer) return;

    if (!data.isCurrentWeek) {
        todaySection.style.display = "none";
        return;
    }

    todaySection.style.display = "block";
    buttonsContainer.innerHTML = "";
    messageContainer.innerHTML = "";

    Object.keys(data.moodScale).forEach(moodName => {
        const info = data.moodScale[moodName];
        const isSelected = data.todayMood === moodName;

        const form = document.createElement("form");
        form.action = "../php/mood_process.php";
        form.method = "POST";
        form.className = "mood-form";

        form.innerHTML = `
            <input type="hidden" name="mood" value="${moodName}">
            <button type="submit" class="mood-btn ${isSelected ? 'selected' : ''}">
                <span class="mood-emoji">${info.emoji}</span>
                <span class="mood-label">${moodName}</span>
            </button>
        `;
        buttonsContainer.appendChild(form);
    });

    if (data.todayMood && data.moodMessages[data.todayMood]) {
        messageContainer.textContent = data.moodMessages[data.todayMood];
    }
}

/* ---------- Mood History Bar Chart ---------- */
function renderMoodChart(data) {
    const chartContainer = document.getElementById("moodChartContainer");
    const avgContainer = document.getElementById("weekAverageText");

    if (!chartContainer) return;

    chartContainer.innerHTML = "";

    data.days.forEach(day => {
        const hasData = day.value > 0;
        const barHeight = hasData ? day.value : 4;
        const dayEmoji = hasData && data.moodScale[day.mood] ? data.moodScale[day.mood].emoji : "–";
        const titleText = `${day.date}${hasData ? ' — ' + day.mood : ' — no entry'}`;

        const col = document.createElement("div");
        col.className = "mood-col";
        col.innerHTML = `
            <span class="mood-day-emoji">${dayEmoji}</span>
            <div class="mood-bar ${!hasData ? 'mood-bar-empty' : ''}" 
                 style="height: ${barHeight}%;" 
                 title="${titleText}"></div>
            <span class="mood-day-label">${day.label}</span>
        `;
        chartContainer.appendChild(col);
    });

    if (avgContainer) {
        if (data.weekAverage !== null) {
            avgContainer.innerHTML = `Week average: <strong>${data.weekAverage}/100</strong> — <strong>${data.weekCategory}</strong>`;
        } else {
            avgContainer.textContent = "No mood entries logged for this week yet.";
        }
    }
}

/* ---------- Gratitude Journal List ---------- */
function renderGratitudeJournal(entries) {
    const journalContainer = document.getElementById("journalListContainer");
    if (!journalContainer) return;

    if (!entries || entries.length === 0) {
        journalContainer.innerHTML = '<p class="empty-msg">No entries yet.</p>';
        return;
    }

    journalContainer.innerHTML = entries.map(entry => {
        const entryDate = entry.created_at ? entry.created_at.split(' ')[0] : '';
        const escapedText = escapeHtml(entry.entry_text).replace(/\n/g, '<br>');
        return `
            <div class="journal-entry">
                <span class="journal-date">${entryDate}</span>
                <p>${escapedText}</p>
            </div>
        `;
    }).join('');
}

/* ---------- Breathing Exercise ---------- */
const breathingCircle = document.getElementById("breathingCircle");
const breathingText = document.getElementById("breathingText");
const beginBreathingBtn = document.getElementById("beginBreathingBtn");

let breathingRunning = false;

function runBreathingCycle(cyclesLeft) {
    if (cyclesLeft <= 0 || !breathingRunning) {
        if (breathingText) breathingText.textContent = "Start";
        if (breathingCircle) breathingCircle.style.transform = "scale(1)";
        if (beginBreathingBtn) beginBreathingBtn.textContent = "Begin Exercise";
        breathingRunning = false;
        return;
    }

    breathingText.textContent = "Breathe In";
    breathingCircle.style.transition = "transform 4s ease-in-out";
    breathingCircle.style.transform = "scale(1.3)";

    setTimeout(function () {
        if (!breathingRunning) return;
        breathingText.textContent = "Hold";

        setTimeout(function () {
            if (!breathingRunning) return;
            breathingText.textContent = "Breathe Out";
            breathingCircle.style.transition = "transform 8s ease-in-out";
            breathingCircle.style.transform = "scale(1)";

            setTimeout(function () {
                runBreathingCycle(cyclesLeft - 1);
            }, 8000);
        }, 7000);
    }, 4000);
}

if (beginBreathingBtn) {
    beginBreathingBtn.addEventListener("click", function () {
        if (breathingRunning) {
            breathingRunning = false;
            breathingText.textContent = "Start";
            breathingCircle.style.transform = "scale(1)";
            beginBreathingBtn.textContent = "Begin Exercise";
            return;
        }
        breathingRunning = true;
        beginBreathingBtn.textContent = "Stop";
        runBreathingCycle(3);
    });
}

/* ---------- Daily Affirmations ---------- */
function initAffirmations() {
    const affirmationBox = document.getElementById("affirmationBox");
    const newAffirmationBtn = document.getElementById("newAffirmationBtn");

    const pickRandomAffirmation = () => {
        const random = affirmationsList[Math.floor(Math.random() * affirmationsList.length)];
        if (affirmationBox) affirmationBox.textContent = '"' + random + '"';
    };

    pickRandomAffirmation();

    if (newAffirmationBtn) {
        newAffirmationBtn.addEventListener("click", pickRandomAffirmation);
    }
}

/* ---------- Utility Functions ---------- */
function formatDateString(dateStr, includeYear) {
    const d = new Date(dateStr + "T00:00:00");
    const day = String(d.getDate()).padStart(2, '0');
    const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
    const month = months[d.getMonth()];
    return includeYear ? `${day} ${month} ${d.getFullYear()}` : `${day} ${month}`;
}

function escapeHtml(text) {
    if (!text) return "";
    return text
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}