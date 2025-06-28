<?php
// Set the session cookie lifetime to 30 days (30 * 24 * 60 * 60 seconds)
$sessionLifetime = 30 * 24 * 60 * 60;
ini_set('session.gc_maxlifetime', $sessionLifetime);
session_set_cookie_params($sessionLifetime);

// Start the session
session_start();

// Debug
error_log("DEBUG: Session started. Session data: " . print_r($_SESSION, true));

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

$admin_id = $_SESSION['admin_id'];
error_log("DEBUG: Logged in as admin_id: " . $admin_id);

// Include PDO database connection
require_once "db_connection.php";

// --- Retrieve Admin Info ---
try {
    $stmt = $pdo->prepare("SELECT firstname, profile_image FROM admin WHERE admin_id = :admin_id");
    $stmt->execute(['admin_id' => $admin_id]);
    $admin = $stmt->fetch();

    $admin_firstname = $admin['firstname'] ?? '';
    $admin_profile   = $admin['profile_image'] ?? '';

    error_log("DEBUG: Admin firstname: $admin_firstname, profile image: $admin_profile");
} catch (PDOException $e) {
    error_log("ERROR: " . $e->getMessage());
}

$adminName = $admin_firstname ?: 'Admin';

// --- Dashboard Overview Counts ---
function countQuery($pdo, $sql) {
    try {
        $stmt = $pdo->query($sql);
        return $stmt->fetchColumn();
    } catch (PDOException $e) {
        error_log("ERROR in countQuery: " . $e->getMessage());
        return 0;
    }
}

$totalBooks   = countQuery($pdo, "SELECT COUNT(*) FROM books");
$totalUsers   = countQuery($pdo, "SELECT COUNT(*) FROM users");
$recentTrans  = countQuery($pdo, "SELECT COUNT(*) FROM book_transactions WHERE DATE(borrow_date) = CURDATE()");
$pendingRes   = countQuery($pdo, "SELECT COUNT(*) FROM reservations WHERE status = 'pending'");
$pendingReturn= countQuery($pdo, "SELECT COUNT(*) FROM reservations WHERE status = 'return_requested'");
$pendingPass  = countQuery($pdo, "SELECT COUNT(*) FROM password_requests WHERE status = 'pending'");
$totalNotifications = $pendingRes + $pendingReturn + $pendingPass;

// --- Reservation Details ---
$reservationDetails = [];
try {
    $stmt = $pdo->query("SELECT r.reservation_id, u.firstname, u.lastname, u.library_id, r.reservation_date 
                         FROM reservations r 
                         JOIN users u ON r.user_id = u.user_id 
                         WHERE r.status = 'pending'");
    $reservationDetails = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("ERROR fetching reservations: " . $e->getMessage());
}

// --- Return Details ---
$returnDetails = [];
try {
    $stmt = $pdo->query("SELECT r.reservation_id, u.firstname, u.lastname, u.library_id, r.reservation_date 
                         FROM reservations r 
                         JOIN users u ON r.user_id = u.user_id 
                         WHERE r.status = 'return_requested'");
    $returnDetails = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("ERROR fetching returns: " . $e->getMessage());
}

// --- Password Requests ---
$passwordDetails = [];
try {
    $stmt = $pdo->query("SELECT pr.request_id, u.firstname, u.lastname, u.library_id, pr.request_date 
                         FROM password_requests pr 
                         JOIN users u ON pr.user_id = u.user_id 
                         WHERE pr.status = 'pending'");
    $passwordDetails = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("ERROR fetching password requests: " . $e->getMessage());
}

// --- Book Search ---
$book_search = "";
$resultBooksSearch = [];

if (isset($_GET['book_search'])) {
    $book_search = trim($_GET['book_search']);
}

try {
    if (!empty($book_search)) {
        $search_param = '%' . $book_search . '%';
        $sql = "SELECT * FROM books 
                WHERE accession_no LIKE :search 
                   OR call_no LIKE :search 
                   OR author LIKE :search 
                   OR title LIKE :search 
                   OR publisher LIKE :search 
                   OR copies LIKE :search 
                   OR copyright LIKE :search 
                   OR course LIKE :search";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['search' => $search_param]);
        $resultBooksSearch = $stmt->fetchAll();
    } else {
        $stmt = $pdo->query("SELECT * FROM books");
        $resultBooksSearch = $stmt->fetchAll();
    }
} catch (PDOException $e) {
    error_log("ERROR in book search: " . $e->getMessage());
}
?>
