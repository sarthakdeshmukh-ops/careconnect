document.getElementById("loginForm").addEventListener("submit", function (e) {
    const password = document.querySelector('input[name="password"]').value;
    if (password.length < 1) {
        e.preventDefault();
        alert("Please enter your password.");
    }
});

const passwordInput = document.getElementById("password");
const passwordToggle = document.getElementById("passwordToggle");

passwordToggle.addEventListener("click", function () {

    if (passwordInput.type === "password") {
        passwordInput.type = "text";
        passwordToggle.textContent = "Hide";
    } else {
        passwordInput.type = "password";
        passwordToggle.textContent = "Show";
    }

});
