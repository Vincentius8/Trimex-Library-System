<?php
session_start();
require_once '../db_connection.php';

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $library_id = isset($_POST["library_id"]) ? trim($_POST["library_id"]) : '';

    if (empty($library_id)) {
        echo json_encode(["status" => "error", "message" => "Library ID is required."]);
        exit;
    }

    try {
        // 1. Check if user exists
        $stmt = $pdo->prepare("SELECT id FROM users WHERE library_id = ?");
        $stmt->execute([$library_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $user_id = $user['id'];

            // 2. Check for existing pending request
            $stmt = $pdo->prepare("SELECT request_id FROM password_requests WHERE user_id = ? AND status = 'pending'");
            $stmt->execute([$user_id]);

            if ($stmt->rowCount() > 0) {
                echo json_encode(["status" => "error", "message" => "You already have a pending request."]);
            } else {
                // 3. Insert new request
                $stmt = $pdo->prepare("
                    INSERT INTO password_requests (user_id, request_date, status)
                    VALUES (?, NOW(), 'pending')
                ");

                if ($stmt->execute([$user_id])) {
                    echo json_encode(["status" => "success", "message" => "Password reset request sent."]);
                } else {
                    echo json_encode(["status" => "error", "message" => "Failed to request reset."]);
                }
            }
        } else {
            echo json_encode(["status" => "error", "message" => "Library ID not found."]);
        }

    } catch (PDOException $e) {
        // Log error in real app
        echo json_encode(["status" => "error", "message" => "Database error. Please try again."]);
    }
}
?>
