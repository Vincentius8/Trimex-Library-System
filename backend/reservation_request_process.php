<?php
// ── SESSION & CONFIG ────────────────────────────────────────────────
$sessionLifetime = 30 * 24 * 60 * 60;
ini_set('session.gc_maxlifetime', $sessionLifetime);
session_set_cookie_params($sessionLifetime);
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

require_once __DIR__ . '/../db_connection.php';

try {
    // ── Fetch admin name (optional) ────────────────────────────────
    $stmt = $pdo->prepare("SELECT CONCAT(firstname, ' ', lastname) AS username FROM admin WHERE admin_id = ?");
    $stmt->execute([$_SESSION['admin_id']]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    $username = $row['username'] ?? '';

    $message = '';

    // ── POST PROCESSING ────────────────────────────────────────────────
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // APPROVE RESERVATION
        if (!empty($_POST['approve_reservation_id'])) {
            $res_id = (int) $_POST['approve_reservation_id'];

            $stmt = $pdo->prepare("
                UPDATE reservations
                SET status = 'approved',
                    due_date = DATE_ADD(CURDATE(), INTERVAL 3 DAY)
                WHERE reservation_id = ?
                  AND status = 'pending'
            ");
            $stmt->execute([$res_id]);

            if ($stmt->rowCount() > 0) {
                $stmt = $pdo->prepare("SELECT book_id, user_id FROM reservations WHERE reservation_id = ?");
                $stmt->execute([$res_id]);
                $res = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($res) {
                    $book_id = (int) $res['book_id'];
                    $thisUserId = (int) $res['user_id'];

                    $pdo->prepare("UPDATE books SET status = 'borrowed' WHERE book_id = ?")
                        ->execute([$book_id]);

                    $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)")
                        ->execute([$thisUserId, "Your reservation request has been approved. Please return on time."]);

                    $message = "Reservation approved, due date set, and book marked as borrowed.";
                } else {
                    $message = "Reservation approved, but error retrieving book info.";
                }
            } else {
                $message = "Error updating reservation.";
            }
        }

        // APPROVE RETURN
        if (!empty($_POST['approve_return_id'])) {
            $res_id = (int) $_POST['approve_return_id'];

            $stmt = $pdo->prepare("SELECT due_date, user_id FROM reservations WHERE reservation_id = ? AND status = 'return_requested'");
            $stmt->execute([$res_id]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($row) {
                $dueDate = $row['due_date'];
                $thisUserId = (int) $row['user_id'];

                $stmt = $pdo->prepare("
                    UPDATE reservations
                    SET status = 'returned',
                        return_date = CURDATE(),
                        fine_amount = CASE
                            WHEN DATEDIFF(CURDATE(), due_date) > 0
                            THEN DATEDIFF(CURDATE(), due_date) * 5
                            ELSE 0
                        END
                    WHERE reservation_id = ?
                      AND status = 'return_requested'
                ");
                $stmt->execute([$res_id]);

                if ($stmt->rowCount() > 0) {
                    $stmt = $pdo->prepare("SELECT book_id FROM reservations WHERE reservation_id = ?");
                    $stmt->execute([$res_id]);
                    $res = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($res) {
                        $book_id = (int) $res['book_id'];

                        $pdo->prepare("UPDATE books SET status = 'available' WHERE book_id = ?")
                            ->execute([$book_id]);

                        $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)")
                            ->execute([$thisUserId, "Your return request has been approved. Check if you have any fines."]);

                        $message = "Return request approved, book available, and fine calculated.";
                    } else {
                        $message = "Return approved, but error retrieving book info.";
                    }
                } else {
                    $message = "Error updating return status.";
                }
            } else {
                $message = "Reservation record not found or not in 'return_requested' status.";
            }
        }

        // CANCEL RESERVATION
        if (!empty($_POST['cancel_reservation_id'])) {
            $res_id = (int) $_POST['cancel_reservation_id'];

            $stmt = $pdo->prepare("UPDATE reservations SET status = 'cancelled' WHERE reservation_id = ?");
            $stmt->execute([$res_id]);

            if ($stmt->rowCount() > 0) {
                $stmt = $pdo->prepare("SELECT book_id, user_id FROM reservations WHERE reservation_id = ?");
                $stmt->execute([$res_id]);
                $res = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($res) {
                    $book_id = (int) $res['book_id'];
                    $thisUserId = (int) $res['user_id'];

                    $pdo->prepare("UPDATE books SET status = 'available' WHERE book_id = ?")
                        ->execute([$book_id]);

                    $pdo->prepare("INSERT INTO notifications (user_id, message) VALUES (?, ?)")
                        ->execute([$thisUserId, "Your reservation request has been cancelled by the admin."]);

                    $message = "Reservation cancelled and book marked as available.";
                } else {
                    $message = "Reservation cancelled, but error retrieving book info.";
                }
            } else {
                $message = "Error updating reservation.";
            }
        }
    }

    // ── Fetch Pending and Return Requests (renamed to match frontend) ─────────
    $resRequests = $pdo->query("
        SELECT r.reservation_id, u.library_id, u.firstname, u.lastname, b.title, r.reservation_date, r.status
        FROM reservations r
        JOIN users u ON r.user_id = u.user_id
        JOIN books b ON r.book_id = b.book_id
        WHERE r.status = 'pending'
    ")->fetchAll(PDO::FETCH_ASSOC);

    $returnRequests = $pdo->query("
        SELECT r.reservation_id, u.library_id, u.firstname, u.lastname, b.title, r.reservation_date, r.status
        FROM reservations r
        JOIN users u ON r.user_id = u.user_id
        JOIN books b ON r.book_id = b.book_id
        WHERE r.status = 'return_requested'
    ")->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
