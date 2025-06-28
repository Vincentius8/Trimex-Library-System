<?php
require 'backend/admin_login_process.php';
// rest of your frontend code here
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="icon" type="image/x-icon" href="img/LIBRARY LOGO.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <link rel ="stylesheet" href="css/admin_login.css">
</head>
<body>
    <!-- Navbar with Back Button -->
    <div class="navbar">
        <button class="back-button" onclick="goBack()">
            <i class="fas fa-arrow-left"></i> Back
        </button>
    </div>

    <div class="container">
        <div class="container-box">
            <div class="left-panel"></div>
            <div class="right-panel">
                <div class="login-box">
                    <img src="img/LIBRARY LOGO.png" alt="Library Logo" class="logo">
                    <h2>Log In to Your Library (Admin)</h2>
                    <form id="adminLoginForm">
                        <input type="text" id="school_id" placeholder="School ID" required>
                        <div class="password-container">
                            <input type="password" id="password" placeholder="Password" required>
                            <i class="fas fa-eye" id="togglePassword" onclick="togglePassword()"></i>
                        </div>
                        <a href="forgot_password.php" class="forgot-password">Forgot Password?</a>
                        <button type="submit" class="login-btn">Log In</button>
                        <!-- Sign Up link styled as a button -->
                        <p id="error-message" style="color: red; display: none;"></p>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        function goBack() {
            window.location.href = 'choose.html';
        }
        function togglePassword() {
            let pass = document.getElementById("password");
            let icon = document.getElementById("togglePassword");
            if (pass.type === "password") {
                pass.type = "text";
                icon.classList.remove("fa-eye");
                icon.classList.add("fa-eye-slash");
            } else {
                pass.type = "password";
                icon.classList.remove("fa-eye-slash");
                icon.classList.add("fa-eye");
            }
        }
        // Handle admin login via AJAX (using fetch)
        document.getElementById("adminLoginForm").addEventListener("submit", function(event) {
            event.preventDefault();

            let schoolId = document.getElementById("school_id").value;
            let password = document.getElementById("password").value;
            let errorMessage = document.getElementById("error-message");
            let passwordField = document.getElementById("password");

            // Remove previous shake animation if any
            passwordField.classList.remove("big-shake");

            fetch("admin_login.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: `school_id=${encodeURIComponent(schoolId)}&password=${encodeURIComponent(password)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.status === "success") {
                    // Redirect to the admin dashboard upon successful login
                    window.location.href = "admin_dashboard.php";
                } else {
                    // Display error message
                    errorMessage.textContent = data.message;
                    errorMessage.style.display = "block";

                    // Apply shake animation for invalid password scenario
                    if (data.message === "Invalid password.") {
                        passwordField.classList.add("big-shake");
                        setTimeout(() => {
                            passwordField.classList.remove("big-shake");
                        }, 700);
                    }
                }
            })
            .catch(error => console.error("Error:", error));
        });
    </script>
</body>
</html>
