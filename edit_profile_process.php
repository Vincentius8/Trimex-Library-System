<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: student_login.php");
    exit;
}

require_once "db_connection.php"; // Defines $pdo

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user_id   = intval($_POST['user_id']);
        $firstname = trim($_POST['firstname']);
        $lastname  = trim($_POST['lastname']);
        $email     = trim($_POST['email']);

        $profileImagePath = '';

        // Handle image upload securely
        if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
            $fileType     = mime_content_type($_FILES['profile_image']['tmp_name']);

            if (in_array($fileType, $allowedTypes)) {
                $fileExt    = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
                $uniqueName = "profile_" . uniqid('', true) . "." . $fileExt;
                $uploadDir  = "uploads/";
                $uploadPath = $uploadDir . $uniqueName;

                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }

                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $uploadPath)) {
                    $profileImagePath = $uploadPath;
                } else {
                    die("Error uploading file.");
                }
            } else {
                die("Unsupported file type. Only JPG, PNG, and GIF are allowed.");
            }
        }

        // Prepare update query using PDO
        if ($profileImagePath !== '') {
            $sql = "UPDATE users 
                    SET firstname = :firstname, lastname = :lastname, email = :email, profile_image = :profile_image 
                    WHERE user_id = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':firstname'      => $firstname,
                ':lastname'       => $lastname,
                ':email'          => $email,
                ':profile_image'  => $profileImagePath,
                ':user_id'        => $user_id
            ]);
        } else {
            $sql = "UPDATE users 
                    SET firstname = :firstname, lastname = :lastname, email = :email 
                    WHERE user_id = :user_id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':firstname' => $firstname,
                ':lastname'  => $lastname,
                ':email'     => $email,
                ':user_id'   => $user_id
            ]);
        }
    }

    header("Location: user_profile.php");
    exit;

} catch (PDOException $e) {
    error_log("PDO Error: " . $e->getMessage());
    die("An error occurred while updating the profile.");
}
?>
