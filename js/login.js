document.getElementById("loginForm").addEventListener("submit", function (e) {
    const password = document.querySelector('input[name="password"]').value;
    if (password.length < 1) {
        e.preventDefault();
        alert("Please enter your password.");
    }
});