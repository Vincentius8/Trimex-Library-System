<?php
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // 1. Collect & sanitize form data
    $firstname  = trim($_POST["firstname"]);
    $lastname   = trim($_POST["lastname"]);
    $email      = trim($_POST["email"]);
    $library_id = trim($_POST["library_id"]);
    $contact    = trim($_POST["contact"]);
    $password   = trim($_POST["password"]);
    $role       = trim($_POST["role"]);

    require_once "db_connection.php";

    try {
        // 2. Validate email domain
        if (strtolower(substr($email, -strlen("@trimexcolleges.edu.ph"))) !== "@trimexcolleges.edu.ph") {
            $error_message = "Email must end with @trimexcolleges.edu.ph.";
        } else {
            // 3. Check for duplicate library_id in `users` and `admin`
            $query_users  = "SELECT user_id FROM users WHERE library_id = :lib_id";
            $query_admins = "SELECT admin_id FROM admin WHERE school_id = :lib_id";

            $stmtUser  = $pdo->prepare($query_users);
            $stmtAdmin = $pdo->prepare($query_admins);

            $stmtUser->execute(['lib_id' => $library_id]);
            $stmtAdmin->execute(['lib_id' => $library_id]);

            if ($stmtUser->rowCount() > 0 || $stmtAdmin->rowCount() > 0) {
                $error_message = "An account with this Library ID already exists.";
            } else {
                // 4. Hash password + generate RFID
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                $rfid = "RFID" . uniqid();

                // 5. Insert new faculty user
                $insertSQL = "
                    INSERT INTO users 
                        (library_id, role, firstname, lastname, email, password, rfid, contact) 
                    VALUES 
                        (:lib_id, :role, :fname, :lname, :email, :password, :rfid, :contact)
                ";

                $stmtInsert = $pdo->prepare($insertSQL);
                $stmtInsert->execute([
                    'lib_id'   => $library_id,
                    'role'     => $role,
                    'fname'    => $firstname,
                    'lname'    => $lastname,
                    'email'    => $email,
                    'password' => $hashedPassword,
                    'rfid'     => $rfid,
                    'contact'  => $contact
                ]);

                $success_message = "Successfully Registered! Redirecting to Login...";
            }
        }

    } catch (PDOException $e) {
        $error_message = "Database error: " . $e->getMessage();
    }
}
?>
