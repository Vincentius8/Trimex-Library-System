<?php
require 'backend/user_management_process.php';
// rest of your frontend code here
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Library Admin Dashboard - User Management</title>
  <link rel="icon" type="image/x-icon" href="img/LIBRARY LOGO.png">

  <!-- Google Font: Poppins -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="css/um.css">
</head>
<body>

  <!-- SIDENAV -->
  <div id="mySidenav" class="sidenav">
    <span class="close-btn" id="closeSidenav">&times;</span>
    <a href="admin_dashboard.php" class="nav-link">Dashboard</a>
    <a href="book_management.php" class="nav-link">Books Management</a>
    <a href="user_management.php" class="nav-link active">User Management</a>
    <a href="transaction.php" class="nav-link">Transactions</a>
    <a href="admin_management.php" class="nav-link">Admin Management</a>
    <!-- Dropdown for Statistical Reports -->
    <div class="dropdown">
      <a href="javascript:void(0)" class="nav-link dropdown-toggle">Statistical Reports</a>
      <div class="dropdown-content">
        <a href="attendance_dashboard.php" class="dropdown-item">Attendance Dashboard</a>
        <a href="reports_stats.php" class="dropdown-item">Report and Stats</a>
      </div>
    </div>
    <a href="backend/admin_logout.php" class="logout-link">Log Out</a>
  </div>

  <!-- OVERLAY -->
  <div id="overlay" class="overlay"></div>

  <!-- MAIN CONTENT -->
  <div id="main">
    <!-- TOPBAR -->
    <div class="topbar">
      <span class="menu-icon" id="openSidenav">&#9776;</span>
      <div class="page-title">
        <img src="img/Trimexlogo1.png" alt="Trimex Logo" />
        <img src="img/LIBRARY LOGO.png" alt="Library Logo" />
      </div>
      <div class="user-info">
        <span><?php echo htmlspecialchars($adminName); ?></span>
      </div>
    </div>

    <!-- Greeting Message -->
    <div id="greeting" class="greeting">
      <h1>Welcome, <?php echo htmlspecialchars($adminName); ?>!</h1>
      <p>You are now logged in as administrator.</p>
    </div>

    <!-- Additional Management Options -->
    <div class="section-container">
      <h2 class="section-title">Additional Management Options</h2>
      <a href="walk_in.php" class="action-button">Walk-In Borrow/Return</a>
      <a href="reservation_request.php" class="action-button">Reservation Requests</a>
      <a href="password_request.php" class="action-button">Password Requests</a>
    </div>

    <!-- User Management Section -->
    <div id="users-section" class="dashboard-section">
      <h2 class="section-title">User Management</h2>
      <div class="user-management-container">
        <!-- Search Bar -->
        <div class="search-container">
          <form method="get" action="user_management.php">
            <input 
              type="text" 
              name="search" 
              placeholder="Search by First Name, Last Name, or Library ID"
              value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>"
            />
            <input type="submit" value="Search">
          </form>
        </div>

        <?php if (isset($message)): ?>
          <p class="um-message"><?php echo htmlspecialchars($message); ?></p>
        <?php endif; ?>

        <table class="user-table">
          <thead>
            <tr>
              <th>Profile</th>
              <th>Library ID</th>
              <th>First Name</th>
              <th>Last Name</th>
              <th>Email</th>
              <th>Contact</th>
              <th>Action</th>
            </tr>
          </thead>
          <tbody>
            <?php if (!empty($result)): ?>
  <?php foreach ($result as $row): ?>
    <tr>
      <td>
        <a 
          href="<?php echo htmlspecialchars($row['profile_image'] ?: 'img/placeholder.png'); ?>" 
          target="_blank"
          title="Click to view full image"
        >
          <img 
            src="<?php echo htmlspecialchars($row['profile_image'] ?: 'img/placeholder.png'); ?>"
            alt="User Profile"
            class="profile-img"
          >
        </a>
      </td>
      <td><?php echo htmlspecialchars($row['library_id']); ?></td>
      <td><?php echo htmlspecialchars($row['firstname']); ?></td>
      <td><?php echo htmlspecialchars($row['lastname']); ?></td>
      <td><?php echo htmlspecialchars($row['email']); ?></td>
      <td><?php echo htmlspecialchars($row['contact']); ?></td>
      <td>
        <!-- Delete Button Triggering the Delete Modal -->
        <button type="button" class="delete-button" data-userid="<?php echo (int)$row['user_id']; ?>">Delete</button>
        <!-- Edit RFID Button -->
        <button 
          type="button" 
          class="edit-button" 
          data-userid="<?php echo (int)$row['user_id']; ?>" 
          data-rfid="<?php echo htmlspecialchars($row['rfid']); ?>"
        >
          Edit RFID
        </button>
      </td>
    </tr>
  <?php endforeach; ?>
<?php else: ?>
  <tr>
    <td colspan="7">No user found.</td>
  </tr>
<?php endif; ?>

          </tbody>
        </table>
      </div>
    </div>
  </div>
  
  <!-- Edit RFID Modal -->
  <div id="editModal" class="modal">
    <div class="modal-content">
      <span class="close-modal">&times;</span>
      <h2>Edit RFID</h2>
      <form id="editRFIDForm" method="post" action="user_management.php">
        <input type="hidden" name="edit_user_id" id="edit_user_id" value="">
        <label for="new_rfid">New RFID:</label>
        <input type="text" name="new_rfid" id="new_rfid" placeholder="Tap RFID or enter manually" required>
        <button type="submit" class="action-button">Update RFID</button>
      </form>
    </div>
  </div>

  <!-- Error Modal for Duplicate RFID -->
  <div id="errorModal" class="modal">
    <div class="modal-content">
      <span class="close-modal" id="closeErrorModal">&times;</span>
      <h2>Error</h2>
      <p id="errorMessage"></p>
    </div>
  </div>

  <!-- Delete Confirmation Modal -->
  <div id="deleteModal" class="modal">
    <div class="modal-content">
      <span class="close-modal" id="closeDeleteModal">&times;</span>
      <h2>Confirm Deletion</h2>
      <p>Are you sure you want to delete this account?</p>
      <form id="deleteForm" method="post" action="user_management.php">
        <input type="hidden" name="delete_user_id" id="delete_user_id" value="">
        <button type="submit" class="action-button">Yes, Delete</button>
        <button type="button" class="action-button" id="cancelDelete">Cancel</button>
      </form>
    </div>
  </div>

  <!-- JS for Sidenav, Modal, Dropdown & Animations -->
  <script>
    // Sidenav logic
    document.addEventListener("DOMContentLoaded", function() {
      const sidenav = document.getElementById("mySidenav");
      const overlay = document.getElementById("overlay");
      const mainContent = document.getElementById("main");
      const openBtn = document.getElementById("openSidenav");
      const closeBtn = document.getElementById("closeSidenav");
      
      function openNav() {
        sidenav.classList.add("open");
        overlay.classList.add("show");
        mainContent.classList.add("pushed");
      }
      
      function closeNav() {
        sidenav.classList.remove("open");
        overlay.classList.remove("show");
        mainContent.classList.remove("pushed");
      }
      
      if (openBtn) openBtn.addEventListener("click", openNav);
      if (closeBtn) closeBtn.addEventListener("click", closeNav);
      if (overlay) overlay.addEventListener("click", closeNav);
    });
    
    // Simple fadeIn effect for greeting
    window.addEventListener('load', () => {
      const greeting = document.getElementById("greeting");
      if (greeting) {
        greeting.style.opacity = '1';
        greeting.style.transform = 'translateY(0)';
      }
    });
    
    // Edit RFID Modal logic
    document.querySelectorAll('.edit-button').forEach(function(button) {
      button.addEventListener('click', function() {
        var userId = this.getAttribute('data-userid');
        var currentRfid = this.getAttribute('data-rfid');
        document.getElementById('edit_user_id').value = userId;
        document.getElementById('new_rfid').value = currentRfid;
        document.getElementById('editModal').style.display = 'block';
      });
    });
    
    // Delete Modal logic
    document.querySelectorAll('.delete-button').forEach(function(button) {
      button.addEventListener('click', function() {
        var userId = this.getAttribute('data-userid');
        document.getElementById('delete_user_id').value = userId;
        document.getElementById('deleteModal').style.display = 'block';
      });
    });
    
    // Close modals using close button
    document.querySelectorAll('.close-modal').forEach(function(element) {
      element.addEventListener('click', function() {
        this.parentElement.parentElement.style.display = 'none';
      });
    });
    
    // Close Delete Modal with Cancel button
    document.getElementById('cancelDelete').addEventListener('click', function() {
      document.getElementById('deleteModal').style.display = 'none';
    });
    
    // Close modals when clicking outside modal-content
    window.addEventListener('click', function(event) {
      var modals = document.querySelectorAll('.modal');
      modals.forEach(function(modal) {
        if (event.target == modal) {
          modal.style.display = 'none';
        }
      });
    });

    // Dropdown Toggle Functionality for Statistical Reports
    const dropdownToggle = document.querySelector('.dropdown-toggle');
    if (dropdownToggle) {
      dropdownToggle.addEventListener('click', function() {
        this.parentElement.classList.toggle('active');
      });
    }
  </script>

  <!-- Script to show error modal if duplicate RFID error occurs -->
  <?php if (isset($duplicateRFIDError) && $duplicateRFIDError): ?>
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    var errorModal = document.getElementById('errorModal');
    var closeErrorModal = document.getElementById('closeErrorModal');
    errorModal.style.display = 'block';
    document.getElementById('errorMessage').innerText = "<?php echo addslashes($message); ?>";

    closeErrorModal.addEventListener('click', function() {
      errorModal.style.display = 'none';
    });

    window.addEventListener('click', function(event) {
      if (event.target == errorModal) {
          errorModal.style.display = 'none';
      }
    });
  });
  </script>
  <?php endif; ?>

</body>
</html>
