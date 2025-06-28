<?php
// ────────────────────────
// SESSION & CONFIG
// ────────────────────────
$sessionLifetime = 30 * 24 * 60 * 60;
ini_set('session.gc_maxlifetime', $sessionLifetime);
session_set_cookie_params($sessionLifetime);
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// ────────────────────────
// DB CONNECTION (PDO)
// ────────────────────────
require_once "db_connection.php"; // Assumes $pdo is your PDO object

// ────────────────────────
// FETCH ADMIN FIRST/LAST NAME
// ────────────────────────
try {
    $stmt = $pdo->prepare("SELECT firstname, lastname FROM admin WHERE admin_id = :id");
    $stmt->execute(['id' => $_SESSION['admin_id']]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);
    $admin_firstname = $admin['firstname'] ?? '';
    $admin_lastname = $admin['lastname'] ?? '';
} catch (PDOException $e) {
    error_log("Error fetching admin: " . $e->getMessage());
    $admin_firstname = $admin_lastname = '';
}

// ────────────────────────
// SET YEAR & ROLES
// ────────────────────────
$selectedYear = isset($_GET['year']) ? intval($_GET['year']) : date('Y');
$roles = ['student', 'teacher', 'faculty'];

// ────────────────────────
// FETCH MONTHLY ATTENDANCE STATS
// ────────────────────────
$attendanceData = [];
foreach ($roles as $role) {
    for ($m = 1; $m <= 12; $m++) {
        $attendanceData[$role][$m] = 0;
    }
}

try {
    $sql = "
        SELECT u.role, MONTH(a.time_in) AS month, COUNT(*) AS total_sessions
        FROM attendance a
        JOIN users u ON a.user_id = u.user_id
        WHERE YEAR(a.time_in) = :year
        GROUP BY u.role, MONTH(a.time_in)
        ORDER BY month, u.role
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['year' => $selectedYear]);

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $role  = strtolower($row['role']);
        $month = (int)$row['month'];
        if (in_array($role, $roles)) {
            $attendanceData[$role][$month] = (int)$row['total_sessions'];
        }
    }
} catch (PDOException $e) {
    error_log("Error fetching attendance stats: " . $e->getMessage());
}

// ────────────────────────
// CALCULATE MONTHLY TOTALS
// ────────────────────────
$monthLabels = [
    1 => "January", 2 => "February", 3 => "March", 4 => "April",
    5 => "May", 6 => "June", 7 => "July", 8 => "August",
    9 => "September", 10 => "October", 11 => "November", 12 => "December"
];
$monthlyTotals = [];
for ($m = 1; $m <= 12; $m++) {
    $total = 0;
    foreach ($roles as $role) {
        $total += $attendanceData[$role][$m];
    }
    $monthlyTotals[$m] = $total;
}

// ────────────────────────
// CALCULATE ROLE TOTALS (PIE CHART)
// ────────────────────────
$roleTotals = [
    'student' => array_sum($attendanceData['student']),
    'teacher' => array_sum($attendanceData['teacher']),
    'faculty' => array_sum($attendanceData['faculty'])
];
?>
