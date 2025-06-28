<?php
require 'backend/faculty_login_process.php';
// rest of your frontend code here
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1.0" />
  <title>Faculty Login</title>
  <link rel="icon" type="image/x-icon" href="img/LIBRARY LOGO.png">

  <!-- Reuse the same icons & base stylesheet from student_login.php -->
  <link
    rel="stylesheet"
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css"
  />
  <link rel="stylesheet" href="css/faculty_login.css" />

</head>
<body>
  <!-- Navbar with Back Button (matches student_login layout) -->
  <div class="navbar">
    <button class="back-button" onclick="goBack()">
      <i class="fas fa-arrow-left"></i> Back
    </button>
    <div></div>
  </div>

  <div class="container">
    <div class="container-box">
      <!-- Left panel image background -->
      <div class="left-panel"></div>

      <!-- Right panel for the login form -->
      <div class="right-panel">
        <div class="login-box">
          <img src="img/LIBRARY LOGO.png" alt="Library Logo" class="logo" />
          <h2>Teachers & Staff Login</h2>
          <p class="error-message" id="error-message"></p>
          <form id="loginForm">
            <input type="text" id="library_id" placeholder="Library ID" required />
            <div class="password-container">
              <input type="password" id="password" placeholder="Password" required />
              <i class="fas fa-eye" id="togglePassword" onclick="togglePassword()"></i>
            </div>
            <a href="forgot_password.php" class="forgot-password">Forgot Password?</a>
            <button type="submit">Log In</button>
            <a href="faculty_register.php" class="signup-btn">Don't have an account? Sign Up</a>
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
      const pass = document.getElementById("password");
      const icon = document.getElementById("togglePassword");
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

    document.getElementById("loginForm").addEventListener("submit", function(event) {
      event.preventDefault();

      const libraryId    = document.getElementById("library_id").value;
      const password     = document.getElementById("password").value;
      const errorMessage = document.getElementById("error-message");
      const passwordField = document.getElementById("password");

      // Reset any previous error state
      errorMessage.style.display = "none";
      passwordField.classList.remove("big-shake");

      // AJAX login request
      fetch("faculty_login.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `library_id=${encodeURIComponent(libraryId)}&password=${encodeURIComponent(password)}`
      })
      .then(response => response.json())
      .then(data => {
        if (data.status === "success") {
          // Redirect after successful login
          window.location.href = "student_dashboard.php";
        } else {
          // Display error message
          errorMessage.textContent = data.message;
          errorMessage.style.display = "block";

          // Animate password field if invalid password
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
