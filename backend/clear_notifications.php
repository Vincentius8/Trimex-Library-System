<?php
// ── Session & DB Connection ────────────────────────────────────────────────
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../student_login.php");
    exit;
}

require_once __DIR__ . '/../db_connection.php';

$user_id = (int) $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("DELETE FROM notifications WHERE user_id = :user_id");
    $stmt->execute(['user_id' => $user_id]);
} catch (PDOException $e) {
    // Optionally log the error
    http_response_code(500);
    exit("Error clearing notifications.");
}

// ── AJAX or Normal Redirect ────────────────────────────────────────────────
$isAjax = isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
          strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

if ($isAjax) {
    http_response_code(204); // No Content
    exit;
}

header("Location: ../student_dashboard.php");
exit;
