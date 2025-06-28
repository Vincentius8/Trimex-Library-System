<?php
session_start();

// Siguraduhin na naka-login ang admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}

// Include database connection
require_once "db_connection.php";

// Query para sa mga discarded na libro
$sqlDiscarded = "SELECT * FROM books WHERE status = 'discarded'";
$resultDiscarded = $conn->query($sqlDiscarded);
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Discarded Books Dashboard - Library Admin</title>
  <link rel="icon" type="image/x-icon" href="img/LIBRARY LOGO.png">
  <!-- Google Font: Poppins -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet" />
  <style>
    body {
      font-family: 'Poppins', sans-serif;
      background-color: #f9f9fb;
      color: #333;
      margin: 20px;
    }
    h1 {
      text-align: center;
      color: #7B1113;
      margin-bottom: 20px;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-bottom: 20px;
    }
    th, td {
      padding: 12px 15px;
      border: 1px solid #ddd;
      text-align: left;
      font-size: 0.95rem;
    }
    th {
      background-color: #f1f1f1;
      font-weight: 600;
    }
    tr:hover {
      background-color: #f1f1f1;
    }
  </style>
</head>
<body>
  <h1>Discarded Books Dashboard</h1>
  <table>
    <thead>
      <tr>
        <th>Accession No</th>
        <th>Call No</th>
        <th>Author</th>
        <th>Title</th>
        <th>Publisher</th>
        <th>Copies</th>
        <th>Publish Year</th>
        <th>Course</th>
      </tr>
    </thead>
    <tbody>
      <?php if ($resultDiscarded && $resultDiscarded->num_rows > 0): ?>
        <?php while ($row = $resultDiscarded->fetch_assoc()): ?>
          <tr>
            <td><?php echo htmlspecialchars($row['accession_no']); ?></td>
            <td><?php echo htmlspecialchars($row['call_no']); ?></td>
            <td><?php echo htmlspecialchars($row['author']); ?></td>
            <td><?php echo htmlspecialchars($row['title']); ?></td>
            <td><?php echo htmlspecialchars($row['publisher']); ?></td>
            <td><?php echo htmlspecialchars($row['copies']); ?></td>
            <td><?php echo htmlspecialchars($row['copyright']); ?></td>
            <td><?php echo htmlspecialchars($row['course']); ?></td>
          </tr>
        <?php endwhile; ?>
      <?php else: ?>
          <tr>
            <td colspan="8" style="text-align:center;">Walang discarded na libro.</td>
          </tr>
      <?php endif; ?>
    </tbody>
  </table>
</body>
</html>
<?php
$conn->close();
?>
