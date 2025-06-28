<?php
require 'backend/student_login_process.php';
// rest of your frontend code here
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Library Login</title>
  <link rel="icon" type="image/x-icon" href="img/LIBRARY LOGO.png">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
  <link rel="stylesheet" href="css/sl.css">
  <style>
    body {
      display: flex;
      flex-direction: column;
      height: 100vh;
      overflow: hidden;
      position: relative;
    }
    /* Blurred Background */
    body::after {
      content: "";
      position: absolute;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: url('img/trimex bldg.jpg') no-repeat center center/cover;
      filter: blur(8px);
      z-index: -1;
    }
    .left-panel {
      width: 50%;
      background: url('img/ECD_8341.jpg') no-repeat center center/cover;
    }
    @keyframes big-shake {
      0%, 100% { transform: translateX(0); }
      10%, 30%, 50%, 70%, 90% { transform: translateX(-10px); }
      20%, 40%, 60%, 80% { transform: translateX(10px); }
    }
    .big-shake {
      animation: big-shake 0.7s ease;
      border: 2px solid red !important;
      box-shadow: 0 0 8px rgba(255, 0, 0, 0.7);
    }
  </style>
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
          <h2>Log In to Your Library Account</h2>
          <form id="loginForm">
            <input type="text" id="library_id" placeholder="Library ID" required>
            <div class="password-container">
              <input type="password" id="password" placeholder="Password" required>
              <i class="fas fa-eye" id="togglePassword" onclick="togglePassword()"></i>
            </div>
            <a href="forgot_password.php" class="forgot-password">Forgot Password?</a>
            <button type="submit" class="login-btn">Log In</button>
            <a href="student_register.php" class="signup-btn">Sign Up</a>
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
    document.getElementById("loginForm").addEventListener("submit", function(event) {
      event.preventDefault(); // Prevent form submission
      let libraryId = document.getElementById("library_id").value;
      let password = document.getElementById("password").value;
      let errorMessage = document.getElementById("error-message");
      let passwordField = document.getElementById("password");
      passwordField.classList.remove("big-shake");
      fetch("student_login.php", {
          method: "POST",
          headers: { "Content-Type": "application/x-www-form-urlencoded" },
          body: `library_id=${encodeURIComponent(libraryId)}&password=${encodeURIComponent(password)}`
      })
      .then(response => response.json())
      .then(data => {
          if (data.status === "success") {
              window.location.href = "student_dashboard.php";
          } else {
              errorMessage.textContent = data.message;
              errorMessage.style.display = "block";
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
