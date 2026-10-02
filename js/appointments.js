const openBtn = document.getElementById("openModalBtn");
const cancelBtn = document.getElementById("cancelModalBtn");
const overlay = document.getElementById("modalOverlay");

openBtn.addEventListener("click", function () {
    overlay.classList.add("show");
});

cancelBtn.addEventListener("click", function () {
    overlay.classList.remove("show");
});

overlay.addEventListener("click", function (e) {
    if (e.target === overlay) {
        overlay.classList.remove("show");
    }
});