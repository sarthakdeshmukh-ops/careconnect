let globalArticlesData = [];

document.addEventListener("DOMContentLoaded", function () {
    fetchCommunityData();
    setupTabSwitching();
    setupArticleFilters();
    setupModalEvents();
});

/* ---------- Fetch Data from Backend ---------- */
function fetchCommunityData() {
    fetch("../php/community.php")
        .then((response) => {
            if (response.redirected) {
                window.location.href = response.url;
                return;
            }
            return response.json();
        })
        .then((data) => {
            if (!data || data.error) {
                console.error("Error loading community data:", data ? data.error : "Unknown error");
                return;
            }

            globalArticlesData = data.articles;

            renderCategories(data.categories);
            renderArticles(data.articles);
            renderVaccinationDrives(data.drives);
            renderPreventionTopics(data.preventionTopics);
            renderEmergencyInfo(data.emergencyNumbers, data.nearbyHospitals);
        })
        .catch((error) => console.error("Fetch error:", error));
}

/* ---------- Tab Switching ---------- */
function setupTabSwitching() {
    const tabs = {
        articles: [document.getElementById("tabArticlesBtn"), document.getElementById("articlesTab")],
        drives: [document.getElementById("tabDrivesBtn"), document.getElementById("drivesTab")],
        prevention: [document.getElementById("tabPreventionBtn"), document.getElementById("preventionTab")],
        emergency: [document.getElementById("tabEmergencyBtn"), document.getElementById("emergencyTab")]
    };

    Object.keys(tabs).forEach((key) => {
        const [btn, panel] = tabs[key];
        btn.addEventListener("click", () => {
            Object.keys(tabs).forEach((k) => {
                const [b, p] = tabs[k];
                if (k === key) {
                    b.classList.add("active");
                    p.style.display = "block";
                } else {
                    b.classList.remove("active");
                    p.style.display = "none";
                }
            });
        });
    });
}

/* ---------- Render Dynamic Sections ---------- */
function renderCategories(categories) {
    const categoryFilter = document.getElementById("categoryFilter");
    categories.forEach((cat) => {
        const option = document.createElement("option");
        option.value = cat;
        option.textContent = cat;
        categoryFilter.appendChild(option);
    });
}

function renderArticles(articles) {
    const articleGrid = document.getElementById("articleGrid");
    articleGrid.innerHTML = "";

    articles.forEach((article) => {
        const card = document.createElement("div");
        card.className = "article-card";
        card.setAttribute("data-title", article.title.toLowerCase());
        card.setAttribute("data-category", article.category);
        card.setAttribute("data-id", article.id);

        card.innerHTML = `
            <div class="article-banner" style="background:${article.bg};">
                <span class="article-emoji">${article.icon}</span>
            </div>
            <div class="article-body">
                <span class="article-cat">${escapeHtml(article.category.toUpperCase())}</span>
                <h3>${escapeHtml(article.title)}</h3>
                <p>${escapeHtml(article.summary)}</p>
                <div class="article-meta">
                    <span>${escapeHtml(article.author)}</span>
                    <span>${escapeHtml(article.read_time)}</span>
                </div>
            </div>
        `;

        card.addEventListener("click", () => openArticleModal(article.id));
        articleGrid.appendChild(card);
    });
}

function renderVaccinationDrives(drives) {
    const drivesList = document.getElementById("drivesList");
    drivesList.innerHTML = drives.map((drive) => `
        <div class="drive-row">
            <div class="drive-icon">💉</div>
            <div class="drive-info">
                <strong>${escapeHtml(drive.name)}</strong>
                <span>📅 ${escapeHtml(drive.date)} &nbsp; 📍 ${escapeHtml(drive.location)} &nbsp; 👥 ${escapeHtml(drive.age)}</span>
            </div>
            <span class="badge ${drive.status === 'Upcoming' ? 'badge-upcoming' : 'badge-completed'}">${escapeHtml(drive.status)}</span>
        </div>
    `).join("");
}

function renderPreventionTopics(topics) {
    const preventionGrid = document.getElementById("preventionGrid");
    preventionGrid.innerHTML = topics.map((topic) => `
        <div class="prevention-card">
            <div class="prevention-icon">${topic.icon}</div>
            <h3>${escapeHtml(topic.title)}</h3>
            <ul>
                ${topic.tips.map((tip) => `<li>${escapeHtml(tip)}</li>`).join("")}
            </ul>
        </div>
    `).join("");
}

function renderEmergencyInfo(numbers, hospitals) {
    const emergencyGrid = document.getElementById("emergencyGrid");
    emergencyGrid.innerHTML = numbers.map((item) => `
        <div class="emergency-card">
            <span class="emergency-label">${escapeHtml(item.label)}</span>
            <span class="emergency-number">${escapeHtml(item.number)}</span>
        </div>
    `).join("");

    const hospitalList = document.getElementById("hospitalList");
    hospitalList.innerHTML = hospitals.map((hospital) => `
        <div class="hospital-row">
            <span class="hospital-icon">🏥</span>
            <div>
                <strong>${escapeHtml(hospital.name)}</strong>
                <span>${escapeHtml(hospital.info)}</span>
            </div>
        </div>
    `).join("");
}

/* ---------- Article Search/Filter ---------- */
function setupArticleFilters() {
    const searchBox = document.getElementById("searchBox");
    const categoryFilter = document.getElementById("categoryFilter");

    function applyArticleFilters() {
        const term = searchBox.value.toLowerCase();
        const cat = categoryFilter.value;
        const articleCards = document.querySelectorAll(".article-card");
        let visible = 0;

        articleCards.forEach((card) => {
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

        document.getElementById("noResults").style.display = visible === 0 ? "block" : "none";
    }

    searchBox.addEventListener("input", applyArticleFilters);
    categoryFilter.addEventListener("change", applyArticleFilters);
}

/* ---------- Article Modal Setup ---------- */
function setupModalEvents() {
    const articleModalOverlay = document.getElementById("articleModalOverlay");
    const closeArticleModalBtn = document.getElementById("closeArticleModalBtn");

    closeArticleModalBtn.addEventListener("click", () => articleModalOverlay.classList.remove("show"));
    articleModalOverlay.addEventListener("click", (e) => {
        if (e.target === articleModalOverlay) articleModalOverlay.classList.remove("show");
    });
}

function openArticleModal(id) {
    const article = globalArticlesData.find((a) => a.id === id);
    if (!article) return;

    document.getElementById("modalCatBadge").textContent = article.category;
    document.getElementById("modalTitle").textContent = article.title;
    document.getElementById("modalByline").textContent = `By ${article.author} • ${article.date} • ${article.read_time}`;
    document.getElementById("modalBody").innerHTML = article.body;

    document.getElementById("articleModalOverlay").classList.add("show");
}

/* Utility function to prevent HTML Injection */
function escapeHtml(text) {
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}