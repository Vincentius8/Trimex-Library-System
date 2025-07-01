<?php
// ✅ Prevent whitespace output
ob_start();
ob_clean();

require 'db_connection.php'; // ⚠️ Make sure this doesn't echo/print anything!

header("Content-Type: application/json");
date_default_timezone_set("Asia/Manila");

try {
    $pdo->exec("SET time_zone = '+08:00'");

    if (!isset($_POST['rfid']) || empty(trim($_POST['rfid']))) {
        echo json_encode(["status" => "error", "message" => "RFID not provided"]);
        exit;
    }

    $rfid = trim($_POST['rfid']);
    $today = date("Y-m-d");

    // 🔍 Get user info
    $stmt = $pdo->prepare("SELECT user_id, firstname, lastname, library_id, profile_image FROM users WHERE rfid = :rfid");
    $stmt->bindParam(':rfid', $rfid);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        echo json_encode(["status" => "error", "message" => "Account or ID does not exist"]);
        exit;
    }

    $user_id = $user['user_id'];

    // ⏱ Check today's attendance
    $stmt = $pdo->prepare("SELECT * FROM attendance WHERE user_id = :user_id AND record_date = :record_date AND time_out IS NULL");
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':record_date', $today);
    $stmt->execute();
    $attendance = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($attendance) {
        // 📌 TIME OUT
        $time_out = date("Y-m-d H:i:s");
        $stmt = $pdo->prepare("UPDATE attendance SET time_out = :time_out, updated_at = NOW() WHERE attendance_id = :id");
        $stmt->bindParam(':time_out', $time_out);
        $stmt->bindParam(':id', $attendance['attendance_id']);
        $stmt->execute();

        $action = "Time Out recorded";
        $time_in = $attendance['time_in'];
    } else {
        // ⏰ TIME IN
        $time_in = date("Y-m-d H:i:s");
        $stmt = $pdo->prepare("INSERT INTO attendance (user_id, rfid, time_in, record_date, created_at, updated_at) VALUES (:user_id, :rfid, :time_in, :record_date, NOW(), NOW())");
        $stmt->bindParam(':user_id', $user_id);
        $stmt->bindParam(':rfid', $rfid);
        $stmt->bindParam(':time_in', $time_in);
        $stmt->bindParam(':record_date', $today);
        $stmt->execute();

        $time_out = null;
        $action = "Time In recorded";
    }

    // ✅ Clean output buffer (in case of accidental echo)
    ob_clean();

    echo json_encode([
        "status" => "success",
        "action" => $action,
        "user" => $user,
        "time_in" => $time_in,
        "time_out" => $time_out,
        "timestamp" => date("F j, Y g:i a")
    ]);
    exit;

} catch (PDOException $e) {
    ob_clean();
    echo json_encode(["status" => "error", "message" => "Server error: " . $e->getMessage()]);
    exit;
}
