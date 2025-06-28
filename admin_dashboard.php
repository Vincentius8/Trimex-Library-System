<?php
require 'backend/admin_dashboard_process.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Library Admin Dashboard</title>
  <link rel="icon" type="image/x-icon" href="img/LIBRARY LOGO.png">
  <!-- Google Font: Poppins -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet" />
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
  <link rel="stylesheet" href="css/admin_dash.css">

  <!-- DataTables CSS (ADDED) -->
  <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css"/>
</head>
<body>

  <!-- SIDENAV -->
  <div id="mySidenav" class="sidenav">
    <span class="close-btn" id="closeSidenav">&times;</span>
    <a href="admin_dashboard.php" class="nav-link active" data-target="dashboard-section">Dashboard</a>
    <a href="book_management.php" class="nav-link">Books Management</a>
    <a href="user_management.php" class="nav-link">User Management</a>
    <a href="transaction.php" class="nav-link">Transactions</a>
    <a href="admin_management.php" class="nav-link">Admin Management</a>
    <div class="dropdown">
      <a href="javascript:void(0)" class="nav-link dropdown-toggle">Statistical Reports</a>
      <div class="dropdown-content">
        <a href="attendance_dashboard.php" class="dropdown-item">Attendance Dashboard</a>
        <a href="reports_stats.php" class="dropdown-item">Report and Stats</a>
      </div>
    </div>
    <a href="js/book.html" class="nav-link">Library Manual</a>
    <a href="backend/admin_logout.php" class="logout-link">Logout</a>
  </div>

  <!-- OVERLAY -->
  <div id="overlay" class="overlay"></div>

  <!-- MAIN CONTENT -->
  <div id="main">
    <div class="topbar">
      <span class="menu-icon" id="openSidenav">&#9776;</span>
      <div class="page-title">
        <img src="img/Trimexlogo1.png" alt="Trimex Logo" />
        <img src="img/LIBRARY LOGO.png" alt="Library Logo" />
      </div>

      <div class="notification-container" style="position: relative;">
        <i class="fas fa-bell" id="notificationIcon" style="cursor: pointer; font-size: 28px;"></i>
        <?php if ($totalNotifications > 0): ?>
          <span class="notification-badge"><?php echo $totalNotifications; ?></span>
        <?php endif; ?>
        <div class="notification-dropdown" id="notificationDropdown" style="display:none; position: absolute; right: 0; background: #fff; width: 300px; max-height: 400px; overflow-y: auto; box-shadow: 0px 0px 10px rgba(0,0,0,0.1); z-index: 1000;">
          <?php if (!empty($reservationDetails)): ?>
            <?php foreach ($reservationDetails as $r): ?>
              <a href="reservation_request.php" style="text-decoration: none; color: inherit;">
                <div class="notification-item" style="padding: 10px; border-bottom: 1px solid #eee; font-size: 14px;">
                  Reservation Request: <?php echo htmlspecialchars($r['firstname'] . ' ' . $r['lastname']); ?> (<?php echo htmlspecialchars($r['library_id']); ?>)
                  on <?php echo htmlspecialchars($r['reservation_date']); ?>
                </div>
              </a>
            <?php endforeach; ?>
          <?php endif; ?>
          <?php if (!empty($returnDetails)): ?>
            <?php foreach ($returnDetails as $ret): ?>
              <a href="reservation_request.php" style="text-decoration: none; color: inherit;">
                <div class="notification-item" style="padding: 10px; border-bottom: 1px solid #eee; font-size: 14px;">
                  Return Request: <?php echo htmlspecialchars($ret['firstname'] . ' ' . $ret['lastname']); ?> (<?php echo htmlspecialchars($ret['library_id']); ?>)
                  on <?php echo htmlspecialchars($ret['reservation_date']); ?>
                </div>
              </a>
            <?php endforeach; ?>
          <?php endif; ?>
          <?php if (!empty($passwordDetails)): ?>
            <?php foreach ($passwordDetails as $p): ?>
              <a href="password_request.php" style="text-decoration: none; color: inherit;">
                <div class="notification-item" style="padding: 10px; border-bottom: 1px solid #eee; font-size: 14px;">
                  Password Request: <?php echo htmlspecialchars($p['firstname'] . ' ' . $p['lastname']); ?> (<?php echo htmlspecialchars($p['library_id']); ?>)
                  on <?php echo htmlspecialchars($p['request_date']); ?>
                </div>
              </a>
            <?php endforeach; ?>
          <?php endif; ?>
          <?php if (empty($reservationDetails) && empty($returnDetails) && empty($passwordDetails)): ?>
            <div class="notification-item" style="padding: 10px; font-size: 14px;">No new notifications.</div>
          <?php endif; ?>
        </div>
      </div>
    </div>

    <div id="greeting" class="greeting">
      <h1>Welcome, <?php echo htmlspecialchars($adminName); ?>!</h1>
      <p>You are now logged in as administrator.</p>
    </div>

    <!-- Dashboard Overview Section -->
    <div id="dashboard-section" class="dashboard-section active-section">
      <h2 class="section-title">Dashboard Overview</h2>
      <p>Welcome to your admin dashboard. Here you can view quick stats and recent activity.</p>
      <div class="dashboard-overview">
        <div class="card">
          <h3>Total Books</h3>
          <p><?php echo htmlspecialchars($totalBooks); ?></p>
        </div>
        <div class="card">
          <h3>Total Users</h3>
          <p><?php echo htmlspecialchars($totalUsers); ?></p>
        </div>
        <div class="card">
          <h3>Recent Transactions</h3>
          <p><?php echo htmlspecialchars($recentTrans); ?> today</p>
        </div>
      </div>
    </div>

    <!-- Books Inventory Section -->
    <div id="books-section" class="dashboard-section">
      <div class="books-section">
        <h2 class="section-title">Books Inventory</h2>
        <div class="search-container">
         
        </div>
        <div class="export-container">
          <a href="export.php<?php echo !empty($book_search) ? '?book_search=' . urlencode($book_search) : ''; ?>">Export to Excel</a>
          <button onclick="printPage('landscape');">Print Landscape</button>
          <button onclick="printPage('portrait');">Print Portrait</button>
        </div>
        <table class="books-table">
          <thead>
            <tr>
              <th><input type="checkbox" id="selectAll" onclick="toggleSelectAll(this)"></th>
              <th>Accession No</th>
              <th>Call No</th>
              <th>Author</th>
              <th>Title</th>
              <th>Publisher</th>
              <th>Copies</th>
              <th>Copyright</th>
              <th>Course</th>
            </tr>
          </thead>
          <tbody>
  <?php if (!empty($resultBooksSearch)): ?>
    <?php foreach ($resultBooksSearch as $row): ?>
      <tr>
        <td><input type="checkbox" class="rowCheckbox"></td>
        <td><?php echo htmlspecialchars($row['accession_no']); ?></td>
        <td><?php echo htmlspecialchars($row['call_no']); ?></td>
        <td><?php echo htmlspecialchars($row['author']); ?></td>
        <td><?php echo htmlspecialchars($row['title']); ?></td>
        <td><?php echo htmlspecialchars($row['publisher']); ?></td>
        <td><?php echo htmlspecialchars($row['copies']); ?></td>
        <td><?php echo htmlspecialchars($row['copyright']); ?></td>
        <td><?php echo htmlspecialchars($row['course']); ?></td>
      </tr>
    <?php endforeach; ?>
  <?php else: ?>
    <tr>
      <td colspan="9" style="text-align:center;">No books found.</td>
    </tr>
  <?php endif; ?>
</tbody>

        </table>
      </div>
    </div>

  </div>

  <!-- JQuery + DataTables JS (ADDED) -->
  <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

  <script>
    $(document).ready(function() {
      $('.books-table').DataTable({
        "paging": true,
        "searching": true,
        "ordering": true,
        "info": true,
        "lengthChange": true,
        "pageLength": 10
      });
    });

    // (YOUR EXISTING SCRIPTS ARE BELOW — untouched)
    const sidenav = document.getElementById("mySidenav");
    const overlay = document.getElementById("overlay");
    const mainContent = document.getElementById("main");
    const openBtn = document.getElementById("openSidenav");
    const closeBtn = document.getElementById("closeSidenav");

    openBtn.addEventListener("click", () => {
      sidenav.classList.add("open");
      overlay.classList.add("show");
      mainContent.classList.add("pushed");
    });
    if(closeBtn){
      closeBtn.addEventListener("click", closeNav);
    }
    overlay.addEventListener("click", closeNav);
    function closeNav() {
      sidenav.classList.remove("open");
      overlay.classList.remove("show");
      mainContent.classList.remove("pushed");
    }

    window.addEventListener('load', () => {
      const greeting = document.getElementById("greeting");
      setTimeout(() => {
        greeting.classList.add("animate");
      }, 300);
    });

    function printPage(orientation) {
      const table = document.querySelector('.books-table');
      const rows = table.querySelectorAll('tbody tr');
      let rowsHTML = '';
      let anySelected = false;
      
      rows.forEach(row => {
        const checkbox = row.querySelector('.rowCheckbox');
        if (checkbox && checkbox.checked) {
          anySelected = true;
          const clone = row.cloneNode(true);
          clone.removeChild(clone.firstElementChild);
          rowsHTML += clone.outerHTML;
        }
      });
      
      if (!anySelected) {
        rows.forEach(row => {
          const clone = row.cloneNode(true);
          clone.removeChild(clone.firstElementChild);
          rowsHTML += clone.outerHTML;
        });
      }
      
      const headerHTML = document.querySelector('.books-section > .section-title').outerHTML;
      
      const printWindow = window.open('', '', 'width=800,height=600');
      printWindow.document.write(`
        <html>
          <head>
            <title>Print Books Inventory</title>
            <style>
              @page {
                size: ${orientation};
                margin: 10mm;
              }
              body {
                margin: 100px;
                padding: 0;
                font-family: 'Poppins', sans-serif;
              }
              h2.section-title {
                text-align: center;
                margin-top: 10px;
              }
              table {
                width: 100%;
                border-collapse: collapse;
                table-layout: fixed;
                border: 2px solid black;
              }
              th, td {
                border: 1px solid black;
                padding: 8px;
                text-align: left;
                word-wrap: break-word;
              }
            </style>
          </head>
          <body>
            ${headerHTML}
            <table>
              <thead>
                <tr>
                  <th>Accession No</th>
                  <th>Call No</th>
                  <th>Author</th>
                  <th>Title</th>
                  <th>Publisher</th>
                  <th>Copies</th>
                  <th>Copyright</th>
                  <th>Course</th>
                </tr>
              </thead>
              <tbody>
                ${rowsHTML}
              </tbody>
            </table>
          </body>
        </html>
      `);
      printWindow.document.close();
      printWindow.focus();
      printWindow.print();
      printWindow.close();
    }

    function toggleSelectAll(source) {
      const checkboxes = document.querySelectorAll('.rowCheckbox');
      checkboxes.forEach(checkbox => checkbox.checked = source.checked);
    }

    const dropdownToggle = document.querySelector('.dropdown-toggle');
    dropdownToggle.addEventListener('click', function() {
      this.parentElement.classList.toggle('active');
    });

    const notificationIcon = document.getElementById('notificationIcon');
    const notificationDropdown = document.getElementById('notificationDropdown');

    notificationIcon.addEventListener('click', function(e) {
      e.stopPropagation();
      if (notificationDropdown.style.display === 'none' || notificationDropdown.style.display === '') {
        notificationDropdown.style.display = 'block';
      } else {
        notificationDropdown.style.display = 'none';
      }
    });

    window.addEventListener('click', function(e) {
      if (!notificationIcon.contains(e.target) && !notificationDropdown.contains(e.target)) {
        notificationDropdown.style.display = 'none';
      }
    });

  </script>
</body>
</html>
