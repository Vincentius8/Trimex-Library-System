<?php
$sessionLifetime = 30*24*60*60;
ini_set('session.gc_maxlifetime', $sessionLifetime);
session_set_cookie_params($sessionLifetime);

session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: student_login.php");
    exit;
}
require_once 'db_connection.php';

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $user_id   = $_SESSION['user_id'];
    $role      = $_SESSION['role']      ?? 'Student';
    $fullName  = $_SESSION['user_name'] ?? '';
    $firstName = $fullName ? explode(' ', $fullName)[0] : '';

    // 1. Profile picture
    $profilePic = 'img/default_profile.png';
    $stmt = $pdo->prepare("SELECT profile_image FROM users WHERE user_id = ?");
    $stmt->execute([$user_id]);
    if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        if (!empty($row['profile_image'])) {
            $profilePic = $row['profile_image'];
        }
    }

    // 2. Notifications
    $notifications = [];
    $stmt = $pdo->prepare("SELECT message, created_at FROM notifications WHERE user_id = ? ORDER BY created_at DESC");
    $stmt->execute([$user_id]);
    $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // 3. Active reservations
    $reservedStatus = [];
    $stmt = $pdo->prepare("SELECT book_id, status FROM reservations WHERE user_id = ? AND status IN ('pending','approved','return_requested') AND return_date IS NULL");
    $stmt->execute([$user_id]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $reservedStatus[$row['book_id']] = $row['status'];
    }

    // 3.5. Active loans
    $activeLoans = [];
    $stmt = $pdo->query("SELECT book_id, user_id FROM book_transactions WHERE return_date IS NULL");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $activeLoans[$row['book_id']] = $row['user_id'];
    }

    // 4. Due & Overdue Reminders
    $finePerDay = 5;
    $today = new DateTime();
    $stmt = $pdo->prepare("
        SELECT b.title, b.author, bt.due_date
          FROM book_transactions bt
          JOIN books b ON bt.book_id = b.book_id
         WHERE bt.user_id = ? AND bt.return_date IS NULL
    ");
    $stmt->execute([$user_id]);
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $due = new DateTime($row['due_date']);
        $diff = (int)$today->diff($due)->format('%r%a');
        if ($diff <= 3 && $diff >= 0) {
            $notifications[] = [
                'message'    => "Reminder: return '{$row['title']}' ({$row['author']}) in {$diff} day(s).",
                'created_at' => $today->format('Y-m-d H:i:s')
            ];
        } elseif ($diff < 0) {
            $d = abs($diff);
            $fine = $d * $finePerDay;
            $notifications[] = [
                'message'    => "Overdue: '{$row['title']}' ({$row['author']}) – {$d} day(s) late. Fine ₱{$fine}.",
                'created_at' => $today->format('Y-m-d H:i:s')
            ];
        }
    }

    // 5. Book list
    $searchTerm = trim($_GET['search'] ?? '');
    if ($searchTerm !== '') {
        $like = "%{$searchTerm}%";
        $stmt = $pdo->prepare("
            SELECT * FROM books
             WHERE (course LIKE ? OR title LIKE ? OR author LIKE ?
                    OR publisher LIKE ? OR accession_no LIKE ?)
               AND status NOT IN ('discarded','discard','missing')
          ORDER BY course, title
        ");
        $stmt->execute([$like, $like, $like, $like, $like]);
        $bookRes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        $stmt = $pdo->query("
            SELECT * FROM books
             WHERE status NOT IN ('discarded','discard','missing')
          ORDER BY course, title
        ");
        $bookRes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 6. Group books and attach statuses
    $groupedBooks = [];
    foreach ($bookRes as $row) {
        $row['user_status'] = $reservedStatus[$row['book_id']] ?? '';
        $row['loaned_by_other'] = isset($activeLoans[$row['book_id']]) && $activeLoans[$row['book_id']] !== $user_id;
        $course = $row['course'] ?: 'Uncategorized';
        $groupedBooks[$course][] = $row;
    }
    foreach ($groupedBooks as &$books) {
        usort($books, fn($a, $b) => strcasecmp($a['title'], $b['title']));
    }
    unset($books);

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
