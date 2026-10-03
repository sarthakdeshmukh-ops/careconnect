/* ---------- Tab switching ---------- */
const tabArticlesBtn = document.getElementById("tabArticlesBtn");
const tabDrivesBtn = document.getElementById("tabDrivesBtn");
const tabPreventionBtn = document.getElementById("tabPreventionBtn");
const tabEmergencyBtn = document.getElementById("tabEmergencyBtn");
const articlesTab = document.getElementById("articlesTab");
const drivesTab = document.getElementById("drivesTab");
const preventionTab = document.getElementById("preventionTab");
const emergencyTab = document.getElementById("emergencyTab");

function activateTab(name) {
    const tabs = {
        articles: [tabArticlesBtn, articlesTab],
        drives: [tabDrivesBtn, drivesTab],
        prevention: [tabPreventionBtn, preventionTab],
        emergency: [tabEmergencyBtn, emergencyTab]
    };
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

tabArticlesBtn.addEventListener("click", () => activateTab("articles"));
tabDrivesBtn.addEventListener("click", () => activateTab("drives"));
tabPreventionBtn.addEventListener("click", () => activateTab("prevention"));
tabEmergencyBtn.addEventListener("click", () => activateTab("emergency"));

/* ---------- Article search/filter ---------- */
const searchBox = document.getElementById("searchBox");
const categoryFilter = document.getElementById("categoryFilter");
const articleCards = document.querySelectorAll(".article-card");
const noResults = document.getElementById("noResults");

function applyArticleFilters() {
    const term = searchBox.value.toLowerCase();
    const cat = categoryFilter.value;
    let visible = 0;

    articleCards.forEach(function (card) {
        const title = card.getAttribute("data-title");
        const category = card.getAttribute("data-category");
        const matchesSearch = title.includes(term) || category.toLowerCase().includes(term);
        const matchesCat = cat === "all" || category === cat;

        if (matchesSearch && matchesCat) {
            card.style.display = "block";
            visible++;
        } else {
            card.style.display = "none";
        }
    });
    noResults.style.display = visible === 0 ? "block" : "none";
}

searchBox.addEventListener("input", applyArticleFilters);
categoryFilter.addEventListener("change", applyArticleFilters);

/* ---------- Article popup ---------- */
const articleModalOverlay = document.getElementById("articleModalOverlay");
const modalCatBadge = document.getElementById("modalCatBadge");
const modalTitle = document.getElementById("modalTitle");
const modalByline = document.getElementById("modalByline");
const modalBody = document.getElementById("modalBody");
const closeArticleModalBtn = document.getElementById("closeArticleModalBtn");

articleCards.forEach(function (card) {
    card.addEventListener("click", function () {
        const id = parseInt(card.getAttribute("data-id"));
        const article = articlesData.find(a => a.id === id);
        if (!article) return;

        modalCatBadge.textContent = article.category;
        modalTitle.textContent = article.title;
        modalByline.textContent = "By " + article.author + " • " + article.date + " • " + article.read_time;
        modalBody.innerHTML = article.body;

        articleModalOverlay.classList.add("show");
    });
});

closeArticleModalBtn.addEventListener("click", () => articleModalOverlay.classList.remove("show"));
articleModalOverlay.addEventListener("click", function (e) {
    if (e.target === articleModalOverlay) articleModalOverlay.classList.remove("show");
});