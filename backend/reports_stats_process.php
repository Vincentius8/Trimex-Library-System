<?php
// ── Session config (30 days) ───────────────────────────────
$sessionLifetime = 30 * 24 * 60 * 60;
ini_set('session.gc_maxlifetime', $sessionLifetime);
session_set_cookie_params($sessionLifetime);

session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

require_once "db_connection.php"; // Assumes $pdo is defined here

try {
    // ── Admin Info ───────────────────────────────────────
    $stmt = $pdo->prepare("SELECT firstname, lastname FROM admin WHERE admin_id = :id");
    $stmt->execute(['id' => $_SESSION['admin_id']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    $admin_firstname = $admin['firstname'] ?? '';
    $admin_lastname  = $admin['lastname'] ?? '';

    // ── Year & Roles ─────────────────────────────────────
    $selectedYear = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');
    $roles = ['student', 'teacher', 'faculty'];

    // ── Initialize arrays ───────────────────────────────
    $borrowData = $reserveData = $combinedData = [];
    foreach ($roles as $role) {
        for ($m = 1; $m <= 12; $m++) {
            $borrowData[$role][$m]  = 0;
            $reserveData[$role][$m] = 0;
            $combinedData[$role][$m] = 0;
        }
    }

    // ── Borrow Stats ─────────────────────────────────────
    $sqlBorrow = "
        SELECT u.role, MONTH(bt.borrow_date) AS month, COUNT(*) AS total_borrows
        FROM book_transactions bt
        JOIN users u ON bt.user_id = u.user_id
        WHERE YEAR(bt.borrow_date) = :year
        GROUP BY u.role, MONTH(bt.borrow_date)
        ORDER BY month, u.role
    ";
    $stmt = $pdo->prepare($sqlBorrow);
    $stmt->execute(['year' => $selectedYear]);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $role = strtolower($row['role']);
        $month = (int)$row['month'];
        if (in_array($role, $roles)) {
            $borrowData[$role][$month] = (int)$row['total_borrows'];
        }
    }

    // ── Reservation Stats ───────────────────────────────
    $sqlReserve = "
        SELECT u.role, MONTH(r.reservation_date) AS month, COUNT(*) AS total_reservations
        FROM reservations r
        JOIN users u ON r.user_id = u.user_id
        WHERE YEAR(r.reservation_date) = :year
        GROUP BY u.role, MONTH(r.reservation_date)
        ORDER BY month, u.role
    ";
    $stmt = $pdo->prepare($sqlReserve);
    $stmt->execute(['year' => $selectedYear]);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $role = strtolower($row['role']);
        $month = (int)$row['month'];
        if (in_array($role, $roles)) {
            $reserveData[$role][$month] = (int)$row['total_reservations'];
        }
    }

    // ── Combine Borrow & Reserve ────────────────────────
    for ($m = 1; $m <= 12; $m++) {
        foreach ($roles as $role) {
            $combinedData[$role][$m] = $borrowData[$role][$m] + $reserveData[$role][$m];
        }
    }

    // ── Monthly Totals ───────────────────────────────────
    $monthLabels = [
        1 => "January", 2 => "February", 3 => "March", 4 => "April",
        5 => "May", 6 => "June", 7 => "July", 8 => "August",
        9 => "September", 10 => "October", 11 => "November", 12 => "December"
    ];
    $monthlyTotals = [];
    for ($m = 1; $m <= 12; $m++) {
        $monthlyTotals[$m] = array_sum(array_column($combinedData, $m));
    }

    // ── Pie Chart Data ───────────────────────────────────
    $walkInTotal = array_sum(array_map('array_sum', $borrowData));
    $reserveTotal = array_sum(array_map('array_sum', $reserveData));

} catch (PDOException $e) {
    die("Database error: " . $e->getMessage());
}
?>
