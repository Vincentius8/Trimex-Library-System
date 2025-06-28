<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header("Content-Type: application/json");

    // Include secure PDO connection
    require_once "db_connection.php";

    // Sanitize and trim input
    $school_id = isset($_POST['school_id']) ? trim($_POST['school_id']) : '';
    $password  = isset($_POST['password']) ? trim($_POST['password']) : '';

    // Basic validation
    if (empty($school_id) || empty($password)) {
        echo json_encode([
            "status"  => "error",
            "message" => "Please fill in all required fields."
        ]);
        exit;
    }

    try {
        // Prepare secure query
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE school_id = :school_id");
        $stmt->execute(['school_id' => $school_id]);
        $admin = $stmt->fetch();

        if ($admin) {
            // Verify password
            if (password_verify($password, $admin['password'])) {
                $_SESSION['admin_id']  = $admin['admin_id'];
                $_SESSION['school_id'] = $admin['school_id'];

                echo json_encode([
                    "status"  => "success",
                    "message" => "Login successful."
                ]);
            } else {
                echo json_encode([
                    "status"  => "error",
                    "message" => "Invalid password."
                ]);
            }
        } else {
            echo json_encode([
                "status"  => "error",
                "message" => "No admin account found with that School ID."
            ]);
        }
    } catch (PDOException $e) {
        // Catch any PDO-related errors
        echo json_encode([
            "status"  => "error",
            "message" => "An error occurred during login. Please try again later."
        ]);
    }
    exit;
}
?>
