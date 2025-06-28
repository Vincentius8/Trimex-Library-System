<?php
// Set the session cookie lifetime to 30 days (30 * 24 * 60 * 60 seconds)
$sessionLifetime = 30 * 24 * 60 * 60;
ini_set('session.gc_maxlifetime', $sessionLifetime);
session_set_cookie_params($sessionLifetime);

// Start the session
session_start();

// Ensure that admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Include database connection
require_once "db_connection.php"; // ✅ Changed: Make sure this now uses $pdo (PDO object)

try {
    // ✅ Changed from MySQLi to PDO
    // Get admin full name and profile image from admin table
    $admin_id = $_SESSION['admin_id'];
    $stmt = $pdo->prepare("SELECT CONCAT(firstname, ' ', lastname) AS username, profile_image FROM admin WHERE admin_id = ?");
    $stmt->execute([$admin_id]);
    $adminData = $stmt->fetch(PDO::FETCH_ASSOC);

    $adminName = $adminData && $adminData['username'] ? $adminData['username'] : 'Admin';
    $adminProfile = $adminData && $adminData['profile_image'] ? $adminData['profile_image'] : ""; // fallback later if empty

    // Process POST actions for user management
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        // ✅ Changed from MySQLi to PDO
        // Delete user account
        if (isset($_POST['delete_user_id'])) {
            $user_id = intval($_POST['delete_user_id']);
            $stmt = $pdo->prepare("DELETE FROM users WHERE user_id = ?");
            if ($stmt->execute([$user_id])) {
                $message = "User deleted successfully.";
            } else {
                $message = "Error deleting user.";
            }
        }

        // ✅ Changed from MySQLi to PDO
        // Edit RFID update action
        if (isset($_POST['edit_user_id']) && isset($_POST['new_rfid'])) {
            $user_id = intval($_POST['edit_user_id']);
            $new_rfid = trim($_POST['new_rfid']);

            // Check if the new RFID already exists for another user
            $checkStmt = $pdo->prepare("SELECT user_id FROM users WHERE rfid = ? AND user_id != ?");
            $checkStmt->execute([$new_rfid, $user_id]);
            if ($checkStmt->rowCount() > 0) {
                // Duplicate RFID exists, reject update and set error message and flag
                $message = "Error: This RFID are already used!";
                $duplicateRFIDError = true;
            } else {
                $stmt = $pdo->prepare("UPDATE users SET rfid = ? WHERE user_id = ?");
                if ($stmt->execute([$new_rfid, $user_id])) {
                    $message = "User RFID updated successfully.";
                } else {
                    $message = "Error updating RFID.";
                }
            }
        }
    }

    // --- Search Functionality for Users ---
    $search = "";
    if (isset($_GET['search'])) {
        $search = trim($_GET['search']);
    }

    // ✅ Changed from MySQLi to PDO
    if (!empty($search)) {
        $search_param = "%" . $search . "%";
        $stmt = $pdo->prepare("SELECT user_id, library_id, firstname, lastname, email, contact, profile_image, rfid
                               FROM users
                               WHERE firstname LIKE ? 
                                  OR lastname LIKE ? 
                                  OR library_id LIKE ?");
        $stmt->execute([$search_param, $search_param, $search_param]);
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // ✅ Changed from MySQLi query() to PDO query()
        $stmt = $pdo->query("SELECT user_id, library_id, firstname, lastname, email, contact, profile_image, rfid FROM users");
        $result = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

} catch (PDOException $e) {
    // ✅ Added try-catch for better error handling
    $message = "Database error: " . $e->getMessage();
}
?>
