<?php
/**
 * Submit a new reservation using PDO.
 * Enforce 3 active reservation cap, insert the new reservation,
 * update the book status, and set flash messages accordingly.
 */

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../student_login.php");
    exit;
}

require_once __DIR__ . '/../db_connection.php';

$user_id = $_SESSION['user_id'];

try {
    // ── 1. Enforce 3-book reservation cap ──────────────────────────────
    $stmt = $pdo->prepare("
        SELECT COUNT(*) FROM reservations
        WHERE user_id = :user_id
          AND return_date IS NULL
          AND status IN ('pending','approved','return_requested')
    ");
    $stmt->execute(['user_id' => $user_id]);
    $active = (int)$stmt->fetchColumn();

    if ($active >= 3) {
        $_SESSION['flash'] = [
            'type' => 'error',
            'text' => 'You can only reserve up to 3 books.'
        ];
        header("Location: ../student_dashboard.php");
        exit;
    }

    // ── 2. Handle New Reservation ──────────────────────────────────────
    if (isset($_POST['book_id']) && is_numeric($_POST['book_id'])) {
        $book_id = (int)$_POST['book_id'];

        $pdo->beginTransaction();

        // Insert reservation
        $stmt = $pdo->prepare("
            INSERT INTO reservations (user_id, book_id, reservation_date, due_date, status)
            VALUES (:user_id, :book_id, CURDATE(), DATE_ADD(CURDATE(), INTERVAL 3 DAY), 'pending')
        ");
        $stmt->execute([
            'user_id' => $user_id,
            'book_id' => $book_id
        ]);

        // Mark book as reserved
        $stmt = $pdo->prepare("UPDATE books SET status = 'reserved' WHERE book_id = :book_id");
        $stmt->execute(['book_id' => $book_id]);

        $pdo->commit();

        $_SESSION['flash'] = [
            'type' => 'success',
            'text' => 'Reservation submitted successfully.'
        ];
    }

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }

    $_SESSION['flash'] = [
        'type' => 'error',
        'text' => 'Error submitting reservation.'
    ];

    // Optional: log $e->getMessage() for debugging
}

header("Location: ../student_dashboard.php");
exit;
?>
