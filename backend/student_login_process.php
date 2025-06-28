<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header("Content-Type: application/json");

    require_once 'db_connection.php'; // $pdo is expected from here

    try {
        // Retrieve and sanitize POST data
        $library_id = isset($_POST['library_id']) ? trim($_POST['library_id']) : '';
        $password   = isset($_POST['password']) ? $_POST['password'] : '';

        // Validate inputs
        if (empty($library_id) || empty($password)) {
            echo json_encode([
                "status"  => "error",
                "message" => "Please fill in all required fields."
            ]);
            exit;
        }

        // Prepare and execute query
        $stmt = $pdo->prepare("SELECT user_id, library_id, firstname, lastname, password FROM users WHERE library_id = ?");
        $stmt->execute([$library_id]);

        // Check if user found
        if ($user = $stmt->fetch(PDO::FETCH_ASSOC)) {

            if (password_verify($password, $user['password'])) {
                // Set session
                $_SESSION['user_id']    = $user['user_id'];
                $_SESSION['library_id'] = $user['library_id'];
                $_SESSION['user_name']  = $user['firstname'] . ' ' . $user['lastname'];

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
        echo json_encode([
            "status"  => "error",
            "message" => "Database error: " . $e->getMessage()
        ]);
    }

    exit;
}
?>
