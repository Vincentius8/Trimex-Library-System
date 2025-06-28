<?php
// Set session cookie lifetime
$sessionLifetime = 30 * 24 * 60 * 60;
ini_set('session.gc_maxlifetime', $sessionLifetime);
session_set_cookie_params($sessionLifetime);
session_start();

if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

require_once 'db_connection.php'; // Defines $pdo
$admin_id = $_SESSION['admin_id'];
$message = "";

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Approve password request
        if (isset($_POST['approve_password_request_id'])) {
            $req_id = intval($_POST['approve_password_request_id']);

            $stmt = $pdo->prepare("SELECT user_id FROM password_requests WHERE request_id = :req_id");
            $stmt->bindParam(':req_id', $req_id, PDO::PARAM_INT);
            $stmt->execute();
            $user = $stmt->fetch();

            if ($user && isset($user['user_id'])) {
                $user_id = $user['user_id'];

                // Generate temporary password
                $tempPassword = substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789"), 0, 10);
                $hashedPassword = password_hash($tempPassword, PASSWORD_DEFAULT);

                // Update user's password
                $stmt = $pdo->prepare("UPDATE users SET password = :password WHERE user_id = :user_id");
                $stmt->bindParam(':password', $hashedPassword);
                $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stmt->execute();

                // Mark the request as approved
                $stmt = $pdo->prepare("UPDATE password_requests SET status = 'approved' WHERE request_id = :req_id");
                $stmt->bindParam(':req_id', $req_id, PDO::PARAM_INT);
                $stmt->execute();

                $message = "Request approved. Temporary password: $tempPassword";
            } else {
                $message = "Error: User not found for this request.";
            }
        }

        // Decline password request
        if (isset($_POST['decline_password_request_id'])) {
            $req_id = intval($_POST['decline_password_request_id']);
            $stmt = $pdo->prepare("UPDATE password_requests SET status = 'declined' WHERE request_id = :req_id");
            $stmt->bindParam(':req_id', $req_id, PDO::PARAM_INT);
            $stmt->execute();

            $message = "Request declined.";
        }
    }

    // Fetch pending password reset requests
    $sql = "SELECT pr.request_id, u.library_id, u.firstname, u.lastname, pr.request_date, pr.status 
            FROM password_requests pr
            JOIN users u ON pr.user_id = u.user_id
            WHERE pr.status = 'pending'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute();
    $passRequestsResult = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    error_log("Error in password_request.php: " . $e->getMessage());
    $message = "An error occurred while processing your request.";
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Password Requests - Library Admin Dashboard</title>
    <link rel="icon" type="image/x-icon" href="img/LIBRARY LOGO.png">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing: border-box; }
        body { 
            font-family: 'Poppins', sans-serif; 
            background: linear-gradient(to right, #ece9e6, #ffffff);
            color: #333; 
            min-height: 100vh; 
            overflow-x: hidden; 
            padding: 20px;
        }

        /* Navbar */
        .navbar {
            background:rgb(136, 8, 4);
            padding: 15px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: white;
        }
        .navbar h1 {
            font-size: 22px;
            font-weight: 600;
        }
        .back-button {
            background-color:#8B0000;
            color:rgb(255, 255, 255);
            border: none;
            padding: 8px 14px;
            cursor: pointer;
            border-radius: 5px;
            font-weight: 600;
            text-decoration: none;
            transition: 0.3s;
        }
        .back-button:hover {
            background-color:#B22222;
        }

        /* Content */
        .container {
            max-width: 1000px;
            margin: 20px auto;
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 { margin-bottom: 20px; color: #003366; }
        .message {
            text-align: center;
            margin-bottom: 20px;
            font-weight: 500;
            color: green;
        }

        /* Table */
        .user-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .user-table th, .user-table td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        .user-table th {
            background-color: rgb(136, 8, 4);
            color: white;
            font-weight: 600;
        }
        .user-table tr:hover {
            background-color: #f1f1f1;
        }

        /* Buttons */
        .action-button {
            background-color:#B22222;
            color: white;
            border: none;
            padding: 6px 10px;
            cursor: pointer;
            border-radius: 4px;
            font-weight: 500;
            transition: 0.3s;
        }
        .action-button:hover {
            background-color: #8B0000;
        }
        .decline-button {
            background-color: #B22222;
            color: white;
        }
        .decline-button:hover {
            background-color: #8B0000;
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <div class="navbar">
        <a href="user_management.php" class="back-button">⬅ Back</a>
    </div>

    <!-- Content -->
    <div class="container">
        <h2>Password Reset Requests</h2>
        <p class="message"><?php echo $message; ?></p>

        <table class="user-table">
            <tr>
                <th>Library ID</th>
                <th>First Name</th>
                <th>Last Name</th>
                <th>Request Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
           <?php if (!empty($passRequestsResult)): ?>
    <?php foreach ($passRequestsResult as $row): ?>

        <tr>
            <td><?php echo htmlspecialchars($row['library_id']); ?></td>
            <td><?php echo htmlspecialchars($row['firstname']); ?></td>
            <td><?php echo htmlspecialchars($row['lastname']); ?></td>
            <td><?php echo htmlspecialchars($row['request_date']); ?></td>
            <td><?php echo htmlspecialchars($row['status']); ?></td>
            <td>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="approve_password_request_id" value="<?php echo $row['request_id']; ?>">
                    <button type="submit" class="action-button">Approve</button>
                </form>
                <form method="post" style="display:inline;">
                    <input type="hidden" name="decline_password_request_id" value="<?php echo $row['request_id']; ?>">
                    <button type="submit" class="action-button decline-button">Decline</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
    <tr><td colspan="6">No pending password reset requests.</td></tr>
<?php endif; ?>

        </table>
    </div>

</body>
</html>
