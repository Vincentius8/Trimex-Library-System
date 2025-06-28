<?php
// Process form submissions at the top
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 1. Collect form data
    $firstname  = trim($_POST["firstname"]);
    $lastname   = trim($_POST["lastname"]);
    $email      = trim($_POST["email"]);
    $library_id = trim($_POST["library_id"]);
    $contact    = trim($_POST["contact"]);
    $password   = trim($_POST["password"]);

    // Include database connection
    require_once "db_connection.php"; // $pdo is expected

    try {
        // List of accepted email domains
        $allowed_domains = [
            "@trimexcolleges.edu.ph",
            "@faculty.trimexcolleges.edu.ph",
            "@student.trimexcolleges.edu.ph"
        ];

        // Flag to track email validity
        $email_is_valid = false;

        foreach ($allowed_domains as $domain) {
            if (str_ends_with(strtolower($email), strtolower($domain))) {
                $email_is_valid = true;
                break;
            }
        }

        if (!$email_is_valid) {
            $error_message = "Email must end with a valid Trimex domain.";
        } else {
            // Check if Library ID already exists in users table
            $checkSQL_users = "SELECT user_id FROM users WHERE library_id = ?";
            $stmtCheck_users = $pdo->prepare($checkSQL_users);
            $stmtCheck_users->execute([$library_id]);
            $resultCheck_users = $stmtCheck_users->rowCount();

            // Also check in admin table
            $checkSQL_admin = "SELECT admin_id FROM admin WHERE school_id = ?";
            $stmtCheck_admin = $pdo->prepare($checkSQL_admin);
            $stmtCheck_admin->execute([$library_id]);
            $resultCheck_admin = $stmtCheck_admin->rowCount();

            if ($resultCheck_users > 0 || $resultCheck_admin > 0) {
                $error_message = "An account with this Library ID already exists.";
            } else {
                // Securely hash the password
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

                // Generate a unique RFID placeholder
                $rfid = "RFID" . uniqid();

                // Insert into users table
                $sql = "INSERT INTO users (library_id, role, firstname, lastname, email, password, rfid, contact) 
                        VALUES (?, 'student', ?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([
                    $library_id,
                    $firstname,
                    $lastname,
                    $email,
                    $hashedPassword,
                    $rfid,
                    $contact
                ]);

                $success_message = "Successfully Registered! Redirecting to Login...";
            }
        }
    } catch (PDOException $e) {
        $error_message = "Error: " . $e->getMessage();
    }
}
?>
