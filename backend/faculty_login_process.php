<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header("Content-Type: application/json");

    require_once 'db_connection.php';

    try {
        // Sanitize and validate input
        $library_id = isset($_POST['library_id']) ? trim($_POST['library_id']) : '';
        $password   = isset($_POST['password']) ? $_POST['password'] : '';

        if (empty($library_id) || empty($password)) {
            echo json_encode([
                "status"  => "error",
                "message" => "Please fill in all required fields."
            ]);
            exit;
        }

        // Prepare and execute query
        $sql = "SELECT user_id, library_id, firstname, lastname, password, role FROM users WHERE library_id = :library_id LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute(['library_id' => $library_id]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Check if role is allowed
            if (!in_array($user['role'], ['teacher', 'faculty'])) {
                echo json_encode([
                    "status"  => "error",
                    "message" => "This account is not authorized for faculty access."
                ]);
                exit;
            }

            // Verify password
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id']    = $user['user_id'];
                $_SESSION['library_id'] = $user['library_id'];
                $_SESSION['user_name']  = $user['firstname'] . ' ' . $user['lastname'];
                $_SESSION['role']       = $user['role'];

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
                "message" => "No account found for the provided Library ID."
            ]);
        }
    } catch (PDOException $e) {
        // Log or handle error if needed
        echo json_encode([
            "status"  => "error",
            "message" => "Database error occurred. Please try again."
        ]);
    }
    exit;
}
?>
