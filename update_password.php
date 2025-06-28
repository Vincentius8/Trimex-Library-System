<?php
require 'db_connection.php'; // Make sure $pdo is initialized here

session_start();
if (!isset($_SESSION['user_id'])) {
    echo json_encode(["status" => "error", "message" => "User not logged in."]);
    exit();
}

try {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
        $user_id = $_SESSION['user_id'];
        $current_password = $_POST['current_password'] ?? '';
        $new_password = $_POST['new_password'] ?? '';

        if (empty($current_password) || empty($new_password)) {
            $_SESSION['message'] = ["status" => "error", "text" => "❌ Please fill in all fields."];
            header("Location: update_password.php");
            exit();
        }

        // Fetch the current hashed password from the database
        $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = :user_id");
        $stmt->execute([':user_id' => $user_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $hashed_password = $user['password'];

            // Verify current password
            if (password_verify($current_password, $hashed_password)) {
                // Hash new password
                $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

                // Update password
                $updateStmt = $pdo->prepare("UPDATE users SET password = :new_password WHERE user_id = :user_id");
                $updateSuccess = $updateStmt->execute([
                    ':new_password' => $new_hashed_password,
                    ':user_id' => $user_id
                ]);

                if ($updateSuccess) {
                    $_SESSION['message'] = ["status" => "success", "text" => "✅ Password updated successfully!"];
                } else {
                    $_SESSION['message'] = ["status" => "error", "text" => "❌ Failed to update password. Please try again."];
                }
            } else {
                $_SESSION['message'] = ["status" => "error", "text" => "❌ Incorrect current password."];
            }
        } else {
            $_SESSION['message'] = ["status" => "error", "text" => "❌ User not found."];
        }

        header("Location: update_password.php");
        exit();
    }
} catch (PDOException $e) {
    $_SESSION['message'] = ["status" => "error", "text" => "❌ Database error: " . htmlspecialchars($e->getMessage())];
    header("Location: update_password.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Password Change</title>
    <link rel="icon" type="image/x-icon" href="img/LIBRARY LOGO.png">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            background-color: #f4f4f4;
        }
        .message-box {
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            text-align: center;
            max-width: 400px;
        }
        .message {
            font-size: 18px;
            font-weight: 500;
            margin-bottom: 15px;
            padding: 10px;
            border-radius: 5px;
        }
        .success {
            color: #155724;
            background: #d4edda;
            border: 1px solid #c3e6cb;
        }
        .error {
            color: #721c24;
            background: #f8d7da;
            border: 1px solid #f5c6cb;
        }
        .back-btn {
            display: inline-block;
            padding: 10px 15px;
            background: #007bff;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 500;
            transition: background 0.3s;
        }
        .back-btn:hover {
            background: #0056b3;
        }
    </style>
</head>
<body>

    <div class="message-box">
        <?php
        if (isset($_SESSION['message'])) {
            $message = $_SESSION['message'];
            echo "<p class='message " . ($message['status'] == 'success' ? "success" : "error") . "'>" . $message['text'] . "</p>";
            unset($_SESSION['message']); // Clear the message after displaying it
        }
        ?>
        <a href="user_profile.php" class="back-btn">Back</a>
    </div>

</body>
</html>
