document.getElementById("registerForm").addEventListener("submit", function (e) {
    const password = document.getElementById("password").value;
    const confirm = document.getElementById("confirm_password").value;

    if (password !== confirm) {
        e.preventDefault();
        alert("Passwords do not match. Please check and try again.");
    }
    if (password.length < 6) {
        e.preventDefault();
        alert("Password should be at least 6 characters.");
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


const confirmPasswordInput = document.getElementById("confirm_password");
const confirmPasswordToggle = document.getElementById("confirmPasswordToggle");

confirmPasswordToggle.addEventListener("click", function () {

    if (confirmPasswordInput.type === "password") {
        confirmPasswordInput.type = "text";
        confirmPasswordToggle.textContent = "Hide";
    } else {
        confirmPasswordInput.type = "password";
        confirmPasswordToggle.textContent = "Show";
    }

});
