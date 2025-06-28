<?php
require_once 'db_connection.php'; // Ensure $pdo is available

$message = "";

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Secure input
        $library_id = trim($_POST['library_id']);

        // Lookup user_id using Library ID
        $stmt = $pdo->prepare("SELECT user_id FROM users WHERE library_id = :library_id");
        $stmt->bindParam(':library_id', $library_id, PDO::PARAM_STR);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        $stmt = null;

        if ($user) {
            $user_id = $user['user_id'];

            // Check for existing pending reset request
            $stmt = $pdo->prepare("
                SELECT COUNT(*) FROM password_requests 
                WHERE user_id = :user_id AND status = 'pending'
            ");
            $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt->execute();
            $pendingCount = $stmt->fetchColumn();
            $stmt = null;

            if ($pendingCount > 0) {
                $message = "The reset password request is already submitted. Please wait for admin approval.";
            } else {
                // Insert password reset request
                $stmt = $pdo->prepare("
                    INSERT INTO password_requests (user_id, request_date, status)
                    VALUES (:user_id, NOW(), 'pending')
                ");
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

                if ($stmt->execute()) {
                    $message = "Password reset request submitted. Please wait for admin approval, But go to the librarian first.";
                } else {
                    $message = "Error: Unable to submit your request.";
                }
                $stmt = null;
            }
        } else {
            $message = "Library ID not found.";
        }
    }
} catch (PDOException $e) {
    error_log("Database error in forgot_password.php: " . $e->getMessage());
    $message = "An error occurred while processing your request.";
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Forgot Password | Trimex Colleges</title>
  <link rel="icon" type="image/x-icon" href="img/LIBRARY LOGO.png">
  <!-- Modern Font Integration -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <style>
      :root {
          --primary-color: #8B0000;
          --secondary-color: #FFD700;
          --accent-color: #003366;
          --background-gradient: linear-gradient(135deg, #f8f9fa, #e9ecef);
          --container-bg: #ffffff;
          --box-shadow: rgba(0, 0, 0, 0.1) 0px 10px 25px;
      }
      body {
          font-family: 'Poppins', sans-serif;
          background: var(--background-gradient);
          margin: 0;
          padding: 0;
          display: flex;
          flex-direction: column;
          min-height: 100vh;
      }
      .navbar {
          background-color: var(--primary-color);
          color: #fff;
          padding: 20px;
          font-size: 1.5rem;
          font-weight: 600;
          box-shadow: 0 2px 4px rgba(0,0,0,0.1);
          display: flex;
          justify-content: space-between;
          align-items: center;
      }
      .back-button {
          background-color: transparent;
          border: 2px solid #fff;
          color: #fff;
          padding: 8px 16px;
          text-decoration: none;
          border-radius: 4px;
          font-size: 1rem;
          transition: background-color 0.3s ease, transform 0.3s ease;
      }
      .back-button:hover {
          background-color: #fff;
          color: var(--primary-color);
          transform: scale(1.05);
      }
      .container {
          background: var(--container-bg);
          margin: 40px auto;
          padding: 40px 30px;
          border-radius: 12px;
          box-shadow: var(--box-shadow);
          max-width: 400px;
          width: 90%;
          text-align: center;
          transition: transform 0.3s ease, box-shadow 0.3s ease;
      }
      .container:hover {
          transform: translateY(-5px);
          box-shadow: 0 12px 30px rgba(0,0,0,0.15);
      }
      h2 {
          color: #333;
          margin-bottom: 20px;
          font-size: 2rem;
      }
      label {
          display: block;
          margin: 20px 0 10px;
          font-weight: 600;
          color: #555;
      }
      input[type="text"] {
          width: 200px;
          padding: 12px 15px;
          border: 1px solid #ccc;
          border-radius: 6px;
          font-size: 1rem;
          transition: border-color 0.3s ease, box-shadow 0.3s ease;
      }
      input[type="text"]:focus {
          border-color: var(--primary-color);
          box-shadow: 0 0 5px rgba(139, 0, 0, 0.5);
          outline: none;
      }
      button {
          background-color: var(--secondary-color);
          color: var(--accent-color);
          border: none;
          padding: 15px;
          width: 150px;
          border-radius: 6px;
          font-size: 1rem;
          font-weight: 600;
          cursor: pointer;
          transition: background-color 0.3s ease, transform 0.3s ease;
          margin-top: 10px;
      }
      button:hover {
          background-color: #e6c200;
          transform: scale(1.02);
      }
      .message {
          margin-top: 20px;
          font-weight: 600;
          color: green;
      }
      /* Hide the back button on Android-sized screens (max-width: 768px) */
      @media (max-width: 768px) {
          .back-button {
              display: none;
          }
      }
  </style>
</head>
<body>
    <div class="navbar">
      <a href="choose.html" class="back-button">Back</a>
    </div>
    <div class="container">
        <h2>Forgot Password</h2>
        <form method="post">
            <label>Enter Your Library ID:</label>
            <input type="text" name="library_id" placeholder="Library ID or School ID" required>
            <button type="submit">Request Reset</button>
            <p>Go to the library staff or librarian first before request to easily get temporary password </p>
        </form>
        <p class="message"><?php echo htmlspecialchars($message); ?></p>
    </div>
</body>
</html>
