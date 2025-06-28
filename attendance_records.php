<?php
// Set the session cookie lifetime to 30 days
$sessionLifetime = 30 * 24 * 60 * 60;
ini_set('session.gc_maxlifetime', $sessionLifetime);
session_set_cookie_params($sessionLifetime);
session_start();

// Ensure admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Include PDO connection
require_once "db_connection.php";

$attendanceRecords = [];

try {
    // Use prepared statement (no params, but safer and standard)
    $stmt = $pdo->prepare("
        SELECT 
            u.lastname,
            u.firstname,
            u.library_id,
            u.role,
            a.time_in,
            a.time_out
        FROM attendance a
        JOIN users u ON a.user_id = u.user_id
        ORDER BY a.time_in DESC
    ");
    $stmt->execute();
    $attendanceRecords = $stmt->fetchAll();
} catch (PDOException $e) {
    error_log("Error fetching attendance records: " . $e->getMessage());
    $attendanceRecords = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Attendance Records</title>
  <link rel="icon" type="image/x-icon" href="img/LIBRARY LOGO.png">
  <!-- Google Font: Poppins -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link 
    href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" 
    rel="stylesheet"
  />
  <!-- Font Awesome for icons -->
  <link 
    rel="stylesheet" 
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css"
  />
  <style>
    /* Inherit some basic style from the main design */
    body {
      font-family: 'Poppins', sans-serif;
      background: #f4f4f8;
      color: #444;
      /* Fade in animation for the whole page */
      animation: fadeIn 0.8s ease forwards;
      opacity: 0; /* start hidden, then fade in */
    }

    @keyframes fadeIn {
      0% { opacity: 0; }
      100% { opacity: 1; }
    }

    h1 {
      text-align: center;
      margin-top: 20px;
      color: #000;
      /* Slide in from top */
      animation: slideDown 0.8s ease forwards;
      transform: translateY(-20px);
      opacity: 0;
    }

    @keyframes slideDown {
      0% { transform: translateY(-20px); opacity: 0; }
      100% { transform: translateY(0); opacity: 1; }
    }

    .table-container {
      width: 90%;
      margin: 20px auto;
      background: #fff;
      padding: 20px;
      border-radius: 6px;
      /* Subtle slide up animation */
      animation: slideUp 0.6s ease forwards;
      transform: translateY(20px);
      opacity: 0;
    }

    @keyframes slideUp {
      0% { transform: translateY(20px); opacity: 0; }
      100% { transform: translateY(0); opacity: 1; }
    }

    table {
      width: 100%;
      border-collapse: collapse;
      font-size: 0.9rem;
    }

    table thead th {
      background-color: #f9f9fb;
      border-bottom: 2px solid #ccc;
      padding: 8px;
      text-align: left;
    }

    table tbody tr {
      transition: background-color 0.3s ease, transform 0.2s ease;
    }
    table tbody tr:hover {
      background-color: #f0f0f0; 
      transform: scale(1.01);
    }

    table tbody td {
      border-bottom: 1px solid #ccc;
      padding: 8px;
    }

    .back-button {
      display: inline-block;
      margin: 0 0 15px 0;
      padding: 6px 12px;
      background-color: #7B1113;
      color: #fff;
      text-decoration: none;
      border-radius: 4px;
      transition: background 0.3s ease, transform 0.3s ease;
    }
    .back-button:hover {
      background-color: #990f17;
      transform: translateY(-2px);
    }
  </style>
</head>
<body>
  <h1>Attendance Records</h1>
  <div class="table-container">
    <!-- Simple back link to go back or anywhere else you want -->
    <a class="back-button" href="attendance_dashboard.php">Back to Dashboard</a>

    <table>
      <thead>
        <tr>
          <th>Last Name</th>
          <th>First Name</th>
          <th>Library ID</th>
          <th>Role</th>
          <th>Time In</th>
          <th>Time Out</th>
        </tr>
      </thead>
      <tbody>
  <?php if (!empty($attendanceRecords)): ?>
    <?php foreach ($attendanceRecords as $row): ?>
      <tr>
        <td><?php echo htmlspecialchars($row['lastname']); ?></td>
        <td><?php echo htmlspecialchars($row['firstname']); ?></td>
        <td><?php echo htmlspecialchars($row['library_id']); ?></td>
        <td>
          <?php 
            $displayRole = $row['role'];
            if ($displayRole === 'teacher') {
              $displayRole = 'Faculty';
            } elseif ($displayRole === 'faculty') {
              $displayRole = 'Staff';
            }
            echo htmlspecialchars($displayRole);
          ?>
        </td>
        <td><?php echo htmlspecialchars($row['time_in']); ?></td>
        <td><?php echo htmlspecialchars($row['time_out']); ?></td>
      </tr>
    <?php endforeach; ?>
  <?php else: ?>
    <tr>
      <td colspan="6">No attendance records found.</td>
    </tr>
  <?php endif; ?>
</tbody>

    </table>
  </div>


</body>
</html>
