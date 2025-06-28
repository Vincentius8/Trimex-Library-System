<?php
// Connect using the shared database connection file
require 'db_connection.php'; // Should return a PDO instance

header("Content-Type: application/json");

// Set default timezone to Philippine Time
date_default_timezone_set("Asia/Manila");

try {
    // Set MySQL timezone for the session
    $conn->exec("SET time_zone = '+08:00'");

    if (!isset($_POST['rfid']) || empty(trim($_POST['rfid']))) {
        echo json_encode(["status" => "error", "message" => "RFID not provided"]);
        exit;
    }

    $rfid = trim($_POST['rfid']);
    $today = date("Y-m-d");

    // STEP 1: Find user by RFID
    $stmt = $conn->prepare("SELECT user_id, firstname, lastname, library_id, profile_image FROM users WHERE rfid = :rfid");
    $stmt->bindParam(':rfid', $rfid);
    $stmt->execute();

    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(["status" => "error", "message" => "Account or ID does not exist"]);
        exit;
    }

    $user_id = $user['user_id'];

    // STEP 2: Check if the user has an open attendance record for today
    $stmt = $conn->prepare("SELECT attendance_id FROM attendance WHERE user_id = :user_id AND record_date = :record_date AND time_out IS NULL");
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->bindParam(':record_date', $today);
    $stmt->execute();

    $attendance = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($attendance) {
        // TIME OUT: Update existing record
        $attendance_id = $attendance['attendance_id'];
        $time_out = date("Y-m-d H:i:s");

        $stmt_update = $conn->prepare("
            UPDATE attendance 
               SET time_out = :time_out, updated_at = CURRENT_TIMESTAMP() 
             WHERE attendance_id = :attendance_id
        ");
        $stmt_update->bindParam(':time_out', $time_out);
        $stmt_update->bindParam(':attendance_id', $attendance_id, PDO::PARAM_INT);
        $stmt_update->execute();

        $action = "Time Out recorded";
    } else {
        // TIME IN: Insert new record
        $time_in = date("Y-m-d H:i:s");

        $stmt_insert = $conn->prepare("
            INSERT INTO attendance (user_id, rfid, time_in, record_date, created_at, updated_at) 
            VALUES (:user_id, :rfid, :time_in, :record_date, NOW(), NOW())
        ");
        $stmt_insert->bindParam(':user_id', $user_id, PDO::PARAM_INT);
        $stmt_insert->bindParam(':rfid', $rfid);
        $stmt_insert->bindParam(':time_in', $time_in);
        $stmt_insert->bindParam(':record_date', $today);
        $stmt_insert->execute();

        $action = "Time In recorded";
    }

    // Format display timestamp
    $displayTimestamp = date("F j, Y g:i a");

    // Return JSON response
    echo json_encode([
        "status"    => "success",
        "action"    => $action,
        "user"      => $user,
        "timestamp" => $displayTimestamp
    ]);

} catch (PDOException $e) {
    echo json_encode([
        "status"  => "error",
        "message" => "Server error: " . $e->getMessage()
    ]);
    exit;
}
?>
