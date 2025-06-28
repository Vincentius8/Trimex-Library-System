<?php
session_start();

// Ensure the student is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: student_login.php");
    exit;
}

require_once '../db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_id']) && is_numeric($_POST['book_id'])) {
    $book_id = (int)$_POST['book_id'];
    $user_id = $_SESSION['user_id'];

    try {
        $stmt = $pdo->prepare("
            UPDATE reservations 
               SET status = 'return_requested' 
             WHERE user_id = :user_id 
               AND book_id = :book_id 
               AND status = 'approved' 
               AND return_date IS NULL
        ");

        $stmt->execute([
            'user_id' => $user_id,
            'book_id' => $book_id
        ]);

        if ($stmt->rowCount() > 0) {
            $message = "Your return request has been submitted. Please wait for admin approval.";
        } else {
            $message = "Error submitting return request or no active borrow found.";
        }

    } catch (PDOException $e) {
        // Log the error in real-world app
        $message = "Something went wrong. Please try again later.";
    }

    header("Location: ../student_dashboard.php?return_message=" . urlencode($message));
    exit;

} else {
    header("Location: ../student_dashboard.php");
    exit;
}
?>
