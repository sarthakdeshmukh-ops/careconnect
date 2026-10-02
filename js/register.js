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