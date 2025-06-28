<?php
require 'backend/reservation_request_process.php';
// rest of your frontend code here
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Reservation Requests - Library Admin Dashboard</title>
  <link rel="icon" type="image/x-icon" href="img/LIBRARY LOGO.png">
  <!-- Google Font: Poppins -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="css/reservation_request.css">
<body>
  <!-- Full-width Navbar -->
  <div class="navbar">
    <div class="nav-links">
      <a href="user_management.php">Back</a>
    </div>
  </div>

  <div class="container">
    <div class="section-container">
      <h2>Reservation Requests</h2>
      <?php if (!empty($message)) echo '<p class="um-message">' . htmlspecialchars($message) . '</p>'; ?>
      
      <h3>New Reservation Requests</h3>
      <table class="user-table">
        <thead>
          <tr>
            <th>Library ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Book Title</th>
            <th>Reservation Date</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
       <tbody>
<?php if (!empty($resRequests)): ?>
  <?php foreach ($resRequests as $row): ?>
    <tr>
      <td><?php echo htmlspecialchars($row['library_id']); ?></td>
      <td><?php echo htmlspecialchars($row['firstname']); ?></td>
      <td><?php echo htmlspecialchars($row['lastname']); ?></td>
      <td><?php echo htmlspecialchars($row['title']); ?></td>
      <td><?php echo htmlspecialchars($row['reservation_date']); ?></td>
      <td><?php echo htmlspecialchars($row['status']); ?></td>
      <td>
        <div class="action-buttons">
          <form method="post" style="margin: 0;">
            <input type="hidden" name="approve_reservation_id" value="<?php echo (int)$row['reservation_id']; ?>">
            <input type="submit" class="action-button" value="Approve">
          </form>
          <form method="post" style="margin: 0;">
            <input type="hidden" name="cancel_reservation_id" value="<?php echo (int)$row['reservation_id']; ?>">
            <input type="submit" class="action-button" value="Cancel">
          </form>
        </div>
      </td>
    </tr>
  <?php endforeach; ?>
<?php else: ?>
  <tr><td colspan="7" style="text-align:center;">No new reservation requests.</td></tr>
<?php endif; ?>
</tbody>

      </table>
    </div>

    <div class="section-container">
      <h3>Return Requests</h3>
      <table class="user-table">
        <thead>
          <tr>
            <th>Library ID</th>
            <th>First Name</th>
            <th>Last Name</th>
            <th>Book Title</th>
            <th>Request Date</th>
            <th>Status</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
         <tbody>
<?php if (!empty($returnRequests)): ?>
  <?php foreach ($returnRequests as $row): ?>
    <tr>
      <td><?php echo htmlspecialchars($row['library_id']); ?></td>
      <td><?php echo htmlspecialchars($row['firstname']); ?></td>
      <td><?php echo htmlspecialchars($row['lastname']); ?></td>
      <td><?php echo htmlspecialchars($row['title']); ?></td>
      <td><?php echo htmlspecialchars($row['reservation_date']); ?></td>
      <td><?php echo htmlspecialchars($row['status']); ?></td>
      <td>
        <div class="action-buttons">
          <form method="post" style="margin: 0;">
            <input type="hidden" name="approve_return_id" value="<?php echo (int)$row['reservation_id']; ?>">
            <input type="submit" class="action-button" value="Approve Return">
          </form>
        </div>
      </td>
    </tr>
  <?php endforeach; ?>
<?php else: ?>
  <tr><td colspan="7" style="text-align:center;">No return requests.</td></tr>
<?php endif; ?>
</tbody>

      </table>
    </div>
  </div>
</body>
</html>