
<?php
require 'backend/student_register_process.php';
// rest of your frontend code here
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Library Registration</title>
  <link rel="icon" type="image/x-icon" href="img/LIBRARY LOGO.png">
  
  <!-- Google Font: Roboto -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap"
    rel="stylesheet"
  />

  <link rel="stylesheet" href="css/student_register.css"/>
</head>
<body>

  <div class="container" id="container">
    <img src="img/LIBRARY LOGO.png" alt="Library Logo">
    <h2>Library Student Registration</h2>

    <!-- Display success or error messages if set -->
    <?php if (!empty($success_message)): ?>
      <div class="message success" id="success-msg">
        <strong><?php echo $success_message; ?></strong>
      </div>

      <!-- JavaScript animations and redirect -->
      <script>
        // Wait 1 second, then fade out the entire container
        setTimeout(function() {
          document.getElementById("container").classList.add("fade-out");
        }, 1000);

        // After 2 more seconds (3 total), redirect to login
        setTimeout(function(){
          window.location.href = "student_login.php";
        }, 3000);
      </script>

    <?php elseif (!empty($error_message)): ?>
      <div class="message error">
        <strong><?php echo $error_message; ?></strong>
      </div>
    <?php endif; ?>

    <!-- Registration Form -->
    <!-- Form posts back to the same file -->
    <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post">
      
      <!-- First Row: First Name and Last Name -->
      <div class="row">
        <div class="form-field">
          <label for="firstname">First Name</label>
          <input 
            type="text"
            name="firstname"
            id="firstname"
            placeholder="Enter your first name"
            required
          />
        </div>
        <div class="form-field">
          <label for="lastname">Last Name</label>
          <input
            type="text"
            name="lastname"
            id="lastname"
            placeholder="Enter your last name"
            required
          />
        </div>
      </div>

      <!-- Single-column fields -->
      <label for="library_id">Library ID</label>
      <input
        type="text"
        name="library_id"
        id="library_id"
        placeholder="Enter your library ID"
        required
      />

      <label for="email">Email</label>
      <input
        type="email"
        name="email"
        id="email"
        placeholder="Your email address"
        required
      />

      <label for="contact">Phone</label>
      <input
        type="text"
        name="contact"
        id="contact"
        placeholder="Enter your contact number"
        required
      />

      <label for="password">Password</label>
      <input
        type="password"
        name="password"
        id="password"
        placeholder="Enter a secure password"
        required
      />

      <button type="submit">Register</button>
    </form>

    <div class="form-footer">
      <p>Already have an account? <a href="student_login.php">Login here</a></p>
    </div>
  </div>

</body>
</html>
