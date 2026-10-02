/* ---------- Breathing Exercise ---------- */
const breathingCircle = document.getElementById("breathingCircle");
const breathingText = document.getElementById("breathingText");
const beginBreathingBtn = document.getElementById("beginBreathingBtn");

let breathingRunning = false;

function runBreathingCycle(cyclesLeft) {
    if (cyclesLeft <= 0 || !breathingRunning) {
        breathingText.textContent = "Start";
        breathingCircle.style.transform = "scale(1)";
        beginBreathingBtn.textContent = "Begin Exercise";
        breathingRunning = false;
        return;
    }

    // Inhale - 4 seconds
    breathingText.textContent = "Breathe In";
    breathingCircle.style.transition = "transform 4s ease-in-out";
    breathingCircle.style.transform = "scale(1.3)";

    setTimeout(function () {
        if (!breathingRunning) return;
        // Hold - 7 seconds
        breathingText.textContent = "Hold";

        setTimeout(function () {
            if (!breathingRunning) return;
            // Exhale - 8 seconds
            breathingText.textContent = "Breathe Out";
            breathingCircle.style.transition = "transform 8s ease-in-out";
            breathingCircle.style.transform = "scale(1)";

            setTimeout(function () {
                runBreathingCycle(cyclesLeft - 1);
            }, 8000);
        }, 7000);
    }, 4000);
}

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

/* ---------- Daily Affirmation ---------- */
const affirmationBox = document.getElementById("affirmationBox");
const newAffirmationBtn = document.getElementById("newAffirmationBtn");

newAffirmationBtn.addEventListener("click", function () {
    const random = affirmationsList[Math.floor(Math.random() * affirmationsList.length)];
    affirmationBox.textContent = '"' + random + '"';
});