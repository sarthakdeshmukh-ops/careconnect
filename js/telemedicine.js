const searchBox = document.getElementById("searchBox");
const specialtyFilter = document.getElementById("specialtyFilter");
const locationFilter = document.getElementById("locationFilter");
const modeButtons = document.querySelectorAll(".mode-btn");
const cards = document.querySelectorAll(".doctor-card");
const noResults = document.getElementById("noResults");

let activeMode = "all";

function applyFilters() {
    const searchTerm = searchBox.value.toLowerCase();
    const selectedSpecialty = specialtyFilter.value;
    const selectedLocation = locationFilter.value;
    let visibleCount = 0;

    cards.forEach(function (card) {
        const name = card.getAttribute("data-name");
        const specialty = card.getAttribute("data-specialty");
        const mode = card.getAttribute("data-mode");
        const location = card.getAttribute("data-location");

        const matchesSearch = name.includes(searchTerm) || specialty.toLowerCase().includes(searchTerm);
        const matchesSpecialty = selectedSpecialty === "all" || specialty === selectedSpecialty;
        const matchesLocation = selectedLocation === "all" || location === selectedLocation;
        const matchesMode = activeMode === "all" || mode === activeMode || mode === "Both";

        if (matchesSearch && matchesSpecialty && matchesLocation && matchesMode) {
            card.style.display = "block";
            visibleCount++;
        } else {
            card.style.display = "none";
        }
    });

    noResults.style.display = visibleCount === 0 ? "block" : "none";
}

searchBox.addEventListener("input", applyFilters);
specialtyFilter.addEventListener("change", applyFilters);
locationFilter.addEventListener("change", applyFilters);

modeButtons.forEach(function (btn) {
    btn.addEventListener("click", function () {
        modeButtons.forEach(b => b.classList.remove("active"));
        btn.classList.add("active");
        activeMode = btn.getAttribute("data-mode");
        applyFilters();
    });
});

/* ---------- Offline booking popup ---------- */
const offlineModalOverlay = document.getElementById("offlineModalOverlay");
const offlineDoctorId = document.getElementById("offlineDoctorId");
const offlineDoctorName = document.getElementById("offlineDoctorName");
const cancelOfflineModalBtn = document.getElementById("cancelOfflineModalBtn");

document.querySelectorAll(".book-offline-btn").forEach(function (btn) {
    btn.addEventListener("click", function () {
        offlineDoctorId.value = btn.getAttribute("data-id");
        offlineDoctorName.value = btn.getAttribute("data-name") + " — " + btn.getAttribute("data-specialty");
        offlineModalOverlay.classList.add("show");
    });
});
cancelOfflineModalBtn.addEventListener("click", function () {
    offlineModalOverlay.classList.remove("show");
});
offlineModalOverlay.addEventListener("click", function (e) {
    if (e.target === offlineModalOverlay) offlineModalOverlay.classList.remove("show");
});

/* ---------- Step 1: Start Consultation popup ---------- */
const startConsultOverlay = document.getElementById("startConsultOverlay");
const startDoctorName = document.getElementById("startDoctorName");
const startDoctorMeta = document.getElementById("startDoctorMeta");
const cancelStartConsultBtn = document.getElementById("cancelStartConsultBtn");
const startVideoCallBtn = document.getElementById("startVideoCallBtn");
const patientNameInput = document.getElementById("patientNameInput");
const patientConcernInput = document.getElementById("patientConcernInput");

let currentDoctorId = "";
let currentDoctorName = "";

document.querySelectorAll(".consult-btn:not(:disabled)").forEach(function (btn) {
    btn.addEventListener("click", function () {
        currentDoctorId = btn.getAttribute("data-id");
        currentDoctorName = btn.getAttribute("data-name");
        startDoctorName.textContent = currentDoctorName;
        startDoctorMeta.textContent = btn.getAttribute("data-specialty") + " • ₹" + btn.getAttribute("data-fee");
        patientNameInput.value = "";
        patientConcernInput.value = "";
        startConsultOverlay.classList.add("show");
    });
});

cancelStartConsultBtn.addEventListener("click", function () {
    startConsultOverlay.classList.remove("show");
    patientNameInput.value = "";
    patientConcernInput.value = "";
});
startConsultOverlay.addEventListener("click", function (e) {
    if (e.target === startConsultOverlay) {
        startConsultOverlay.classList.remove("show");
        patientNameInput.value = "";
        patientConcernInput.value = "";
    }
});

/* ---------- Step 2: Video Call screen ---------- */
const videoCallOverlay = document.getElementById("videoCallOverlay");
const callDoctorName = document.getElementById("callDoctorName");
const myVideoFeed = document.getElementById("myVideoFeed");
const myVideoPlaceholder = document.getElementById("myVideoPlaceholder");
const toggleCameraBtn = document.getElementById("toggleCameraBtn");
const toggleMicBtn = document.getElementById("toggleMicBtn");
const endCallBtn = document.getElementById("endCallBtn");

let cameraStream = null;
let cameraOn = false;
let micOn = true;

startVideoCallBtn.addEventListener("click", async function () {
    const name = patientNameInput.value.trim();
    const concern = patientConcernInput.value.trim();

    // Validation — block the call from starting if fields are empty
    if (!name || !concern) {
        alert("Please enter your name and describe your concern before starting the call.");
        return;
    }

    try {
        const formData = new FormData();
        formData.append("doctor_id", currentDoctorId);
        formData.append("patient_name", name);
        formData.append("concern", concern);

        const response = await fetch("start_consultation_process.php", {
            method: "POST",
            body: formData
        });
        const result = await response.json();

        if (!result.success) {
            alert("Something went wrong starting the consultation. Please try again.");
            return;
        }
    } catch (err) {
        alert("Could not connect to the server. Please check your connection and try again.");
        return;
    }

    startConsultOverlay.classList.remove("show");
    callDoctorName.textContent = currentDoctorName;
    videoCallOverlay.classList.add("show");
});

toggleCameraBtn.addEventListener("click", async function () {
    if (!cameraOn) {
        try {
            cameraStream = await navigator.mediaDevices.getUserMedia({ video: true, audio: true });
            myVideoFeed.srcObject = cameraStream;
            myVideoFeed.style.display = "block";
            myVideoPlaceholder.style.display = "none";
            cameraOn = true;
            toggleCameraBtn.classList.remove("active-off");
        } catch (err) {
            alert("Couldn't access your camera. Please allow camera permission in your browser and try again.");
        }
    } else {
        if (cameraStream) {
            cameraStream.getTracks().forEach(track => track.stop());
        }
        myVideoFeed.style.display = "none";
        myVideoPlaceholder.style.display = "block";
        cameraOn = false;
        toggleCameraBtn.classList.add("active-off");
    }
});

toggleMicBtn.addEventListener("click", function () {
    micOn = !micOn;
    if (cameraStream) {
        cameraStream.getAudioTracks().forEach(track => track.enabled = micOn);
    }
    toggleMicBtn.classList.toggle("active-off", !micOn);
});

endCallBtn.addEventListener("click", function () {
    if (cameraStream) {
        cameraStream.getTracks().forEach(track => track.stop());
    }
    cameraStream = null;
    cameraOn = false;
    myVideoFeed.style.display = "none";
    myVideoPlaceholder.style.display = "block";
    videoCallOverlay.classList.remove("show");

    // Reset fields so next consultation starts fresh
    patientNameInput.value = "";
    patientConcernInput.value = "";
});