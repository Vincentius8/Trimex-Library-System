<?php
/**
 * Cancel a *pending* reservation. Approved borrows can’t be cancelled here.
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../student_login.php");
    exit;
}

require_once __DIR__ . '/../db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_id'])) {
    $book_id = (int) $_POST['book_id'];
    $user_id = (int) $_SESSION['user_id'];

    try {
        // 1. Check if reservation exists and is eligible for cancellation
        $stmt = $pdo->prepare("
            SELECT status
              FROM reservations
             WHERE user_id = :user_id
               AND book_id = :book_id
               AND status IN ('pending', 'approved')
               AND return_date IS NULL
             LIMIT 1
        ");
        $stmt->execute(['user_id' => $user_id, 'book_id' => $book_id]);
        $reservation = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($reservation) {
            if ($reservation['status'] === 'approved') {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'text' => 'This reservation has been approved and cannot be cancelled.'
                ];
                header("Location: ../student_dashboard.php");
                exit;
            }

            // 2. Cancel the reservation (pending only)
            $cancel = $pdo->prepare("
                UPDATE reservations
                   SET status = 'cancelled'
                 WHERE user_id = :user_id
                   AND book_id = :book_id
                   AND status = 'pending'
                   AND return_date IS NULL
            ");
            $cancel->execute(['user_id' => $user_id, 'book_id' => $book_id]);

            // 3. Free the book if reservation was cancelled
            if ($cancel->rowCount() > 0) {
                $bookUpdate = $pdo->prepare("UPDATE books SET status = 'available' WHERE book_id = :book_id");
                $bookUpdate->execute(['book_id' => $book_id]);

                $_SESSION['flash'] = [
                    'type' => 'success',
                    'text' => 'Your reservation has been cancelled.'
                ];
            } else {
                $_SESSION['flash'] = [
                    'type' => 'error',
                    'text' => 'Error cancelling your reservation.'
                ];
            }
        }

    } catch (PDOException $e) {
        // Log this in production
        $_SESSION['flash'] = [
            'type' => 'error',
            'text' => 'A database error occurred. Please try again later.'
        ];
    }
}

header("Location: ../student_dashboard.php");
exit;
