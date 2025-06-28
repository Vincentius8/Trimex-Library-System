<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: student_login.php");
    exit;
}

require_once "db_connection.php"; // Should define $pdo

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user_id          = intval($_POST['user_id']);
        $current_password = trim($_POST['current_password']);
        $new_password     = trim($_POST['new_password']);
        $confirm_password = trim($_POST['confirm_password']);

        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            die("❌ All password fields are required.");
        }

        if ($new_password !== $confirm_password) {
            die("❌ New password and confirm password do not match.");
        }

        // Get current password hash from DB
        $stmt = $pdo->prepare("SELECT password FROM users WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userData || !password_verify($current_password, $userData['password'])) {
            die("❌ Incorrect current password.");
        }

        // Hash and update new password
        $newHashed = password_hash($new_password, PASSWORD_DEFAULT);
        $updateStmt = $pdo->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $updateStmt->execute([$newHashed, $user_id]);

        header("Location: user_profile.php");
        exit;
    }

} catch (PDOException $e) {
    error_log("PDO Error: " . $e->getMessage());
    die("❌ Database error. Please try again later.");
}
?>
