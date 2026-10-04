document.addEventListener("DOMContentLoaded", function () {
    const passwordFields = document.querySelectorAll('input[type="password"]');

    const eyeOpenSVG = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle></svg>`;

    const eyeClosedSVG = `<svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.94 10.94 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line></svg>`;

    passwordFields.forEach(function (field) {
        // Create a wrapper and force it to behave with inline styles (can't be overridden)
        const wrapper = document.createElement("div");
        wrapper.style.position = "relative";
        wrapper.style.width = "100%";
        wrapper.style.display = "block";

        field.parentNode.insertBefore(wrapper, field);
        wrapper.appendChild(field);

        // Force the input itself to leave room on the right for the icon
        field.style.paddingRight = "42px";
        field.style.boxSizing = "border-box";
        field.style.width = "100%";

        const toggleIcon = document.createElement("span");
        toggleIcon.innerHTML = eyeClosedSVG;

        // Inline styles guarantee the icon sits inside the input, vertically centered
        toggleIcon.style.position = "absolute";
        toggleIcon.style.top = "50%";
        toggleIcon.style.right = "12px";
        toggleIcon.style.transform = "translateY(-50%)";
        toggleIcon.style.cursor = "pointer";
        toggleIcon.style.display = "flex";
        toggleIcon.style.alignItems = "center";
        toggleIcon.style.color = "#9aa5ba";
        toggleIcon.style.zIndex = "5";

        wrapper.appendChild(toggleIcon);

        toggleIcon.addEventListener("click", function () {
            if (field.type === "password") {
                field.type = "text";
                toggleIcon.innerHTML = eyeOpenSVG;
            } else {
                field.type = "password";
                toggleIcon.innerHTML = eyeClosedSVG;
            }
        });

        toggleIcon.addEventListener("mouseenter", function () {
            toggleIcon.style.color = "#4a90d9";
        });
        toggleIcon.addEventListener("mouseleave", function () {
            toggleIcon.style.color = "#9aa5ba";
        });
    });
});