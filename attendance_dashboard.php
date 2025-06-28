<?php
require 'backend/attendance_dashboard_process.php';
// rest of your frontend code here
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Attendance Dashboard</title>
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
  <!-- Chart.js -->
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="css/atd.css">
</head>
<body>
  <!-- SIDENAV -->
  <div id="mySidenav" class="sidenav">
    <span class="close-btn" id="closeSidenav">&times;</span>
    <a href="admin_dashboard.php" class="nav-link">Dashboard</a>
    <a href="book_management.php" class="nav-link">Books Management</a>
    <a href="user_management.php" class="nav-link">User Management</a>
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
    <a href="backend/admin_logout.php" class="logout-link">Logout</a>
  </div>
  <div id="overlay" class="overlay"></div>

  <!-- MAIN CONTENT -->
  <div id="main">
    <div class="topbar">
      <span class="menu-icon" id="openSidenav">&#9776;</span>
      <div class="page-title">
        <img class="logo" src="img/TRIMEX LOGO.png" alt="Trimex Logo" />
        <img class="logo" src="img/LIBRARY LOGO.png" alt="Library Logo" />
      </div>
    </div>

    <div id="greeting" class="greeting">
      <h1>Welcome, <?php echo htmlspecialchars($admin_firstname); ?>!</h1>
      <p>You are now logged in as administrator.</p>
    </div>

    <!-- ATTENDANCE SECTION -->
    <div class="attendance-section">
      <div class="attendance-header">
        <div class="logo-container">
          <img class="logo" src="img/TRIMEX LOGO.png" alt="Trimex Logo" />
          <img class="logo" src="img/LIBRARY LOGO.png" alt="Library Logo" />
        </div>
        <div class="header-text">
          <h2>Attendance Dashboard</h2>
          <p>Monthly breakdown of time-in/time-out records by user role (Student, Faculty, Staff).</p>
        </div>
      </div>

      <div class="report-layout">
        <!-- TABLE CONTAINER (Left) -->
        <div class="table-container">
          <div class="table-header-row">
            <div class="checkbox-row">
              <label>
                <input type="checkbox" class="role-filter" value="student" checked>
                Student
              </label>
              <label>
                <input type="checkbox" class="role-filter" value="teacher" checked>
                Faculty
              </label>
              <label>
                <input type="checkbox" class="role-filter" value="faculty" checked>
                Staff
              </label>
            </div>
          </div>

          <h3>Monthly Totals</h3>
          <table class="monthly-table">
            <thead>
              <tr>
                <th>Month</th>
                <th>Total Attendance</th>
              </tr>
            </thead>
            <tbody>
              <?php foreach ($monthlyTotals as $monthNum => $total): ?>
                <tr>
                  <td><?php echo $monthLabels[$monthNum]; ?></td>
                  <td><?php echo $total; ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>

          <p class="prepared-by">
            Prepared by: <?php echo htmlspecialchars($admin_firstname . " " . $admin_lastname); ?>
          </p>

          <!-- Pie Chart next to table -->
          <div class="table-pie-row">
            <div class="pie-chart-wrapper">
              <h4 style="text-align:center;"><?php echo $selectedYear; ?> Role Distribution</h4>
              <canvas id="pieChart"></canvas>
            </div>
          </div>
        </div>

        <!-- BAR CHART CONTAINER (Right) -->
        <div class="chart-container">
          <div class="chart-controls">
            <!-- EXISTING BUTTON: Report & Stats -->
            <button class="back-button-top" onclick="window.location.href='reports_stats.php'">
               Report &amp; Stats
            </button>

            <!-- UPDATED BUTTON: Goes to a new file "attendance_records.php" -->
            <button class="back-button-top" onclick="window.location.href='attendance_records.php'">
               Attendance Records
            </button>

            <!-- EXISTING BUTTON: Print -->
            <button class="print-button-top" onclick="window.print()">
              Print Chart &amp; Table
            </button>

            <form method="GET" action="">
              <label for="yearSelect" style="margin-right:5px;">Year:</label>
              <select name="year" id="yearSelect" onchange="this.form.submit()">
                <?php 
                  $currentYear = date('Y');
                  for($y = $currentYear; $y >= $currentYear - 10; $y--):
                ?>
                  <option value="<?php echo $y; ?>" <?php if($selectedYear == $y) echo 'selected'; ?>>
                    <?php echo $y; ?>
                  </option>
                <?php endfor; ?>
              </select>
            </form>
          </div>
          <canvas id="attendanceChart"></canvas>
        </div>
      </div>
    </div>
  </div>

  <script>
    // Convert PHP data to JS variables
    const attendanceData = <?php echo json_encode($attendanceData); ?>;
    const roles = ["student", "teacher", "faculty"];
    // Updated roleLabels with teacher as FACULTY and faculty as STAFF
    const roleLabels = { student: "Student", teacher: "Faculty", faculty: "Staff" };
    const monthLabels = [
      "January","February","March","April","May","June",
      "July","August","September","October","November","December"
    ];

    // Colors for each role (bar chart)
    const roleColors = {
      student: 'rgba(54, 162, 235, 1)',
      teacher: 'rgba(75, 192, 192, 1)',
      faculty: 'rgba(255, 99, 132, 1)'
    };

    // Totals for the Pie Chart
    const roleTotals = {
      student: <?php echo $roleTotals['student']; ?>,
      teacher: <?php echo $roleTotals['teacher']; ?>,
      faculty: <?php echo $roleTotals['faculty']; ?>
    };

    // Create datasets for bar chart
    function createDatasets(dataObj) {
      return roles.map(role => {
        const dataArr = [];
        for (let i = 1; i <= 12; i++) {
          dataArr.push(dataObj[role][i] || 0);
        }
        return {
          label: roleLabels[role],
          data: dataArr,
          backgroundColor: roleColors[role],
          borderColor: roleColors[role],
          borderWidth: 1
        };
      });
    }

    // BAR CHART
    const attendanceCtx = document.getElementById('attendanceChart').getContext('2d');
    const attendanceChart = new Chart(attendanceCtx, {
      type: 'bar',
      data: {
        labels: monthLabels,
        datasets: createDatasets(attendanceData)
      },
      options: {
        responsive: true,
        plugins: {
          legend: { position: 'top' },
          title: { display: true, text: 'Monthly Attendance (Time-in Count) by Role' }
        },
        scales: {
          x: {
            ticks: { color: 'red' },
            grid: { color: 'black' }
          },
          y: {
            beginAtZero: true,
            ticks: { stepSize: 1, color: 'red' },
            grid: { color: 'black' }
          },
          yRight: {
            position: 'right',
            grid: { drawOnChartArea: false, drawBorder: true, borderColor: 'black', borderWidth: 1 },
            ticks: { display: false }
          }
        }
      }
    });

    // Role filter for bar chart
    function updateChart() {
      const checkedRoles = Array.from(document.querySelectorAll('.role-filter:checked'))
                                .map(cb => cb.value.toLowerCase());
      attendanceChart.data.datasets.forEach(dataset => {
        const lowerLabel = dataset.label.toLowerCase();
        dataset.hidden = !checkedRoles.includes(lowerLabel);
      });
      attendanceChart.update();
    }
    document.querySelectorAll('.role-filter').forEach(box => {
      box.addEventListener('change', updateChart);
    });

    // PIE CHART
    const pieCtx = document.getElementById('pieChart').getContext('2d');
    const pieChart = new Chart(pieCtx, {
      type: 'pie',
      data: {
        labels: [ roleLabels.student, roleLabels.teacher, roleLabels.faculty ],
        datasets: [{
          label: 'Role Distribution',
          data: [ roleTotals.student, roleTotals.teacher, roleTotals.faculty ],
          backgroundColor: [
            'rgba(54, 162, 235, 0.8)',
            'rgba(75, 192, 192, 0.8)',
            'rgba(255, 99, 132, 0.8)'
          ],
          borderColor: [
            'rgba(54, 162, 235, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(255, 99, 132, 1)'
          ],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        plugins: {
          legend: { position: 'bottom' },
          title: { display: false }
        }
      }
    });

    // SIDENAV functions
    const sidenav = document.getElementById("mySidenav");
    const overlay = document.getElementById("overlay");
    const mainContent = document.getElementById("main");
    const openBtn = document.getElementById("openSidenav");
    const closeSidenavBtn = document.getElementById("closeSidenav");
    openBtn.addEventListener("click", () => {
      sidenav.classList.add("open");
      overlay.classList.add("show");
      mainContent.classList.add("pushed");
    });
    if (closeSidenavBtn) {
      closeSidenavBtn.addEventListener("click", closeNav);
    }
    overlay.addEventListener("click", closeNav);
    function closeNav() {
      sidenav.classList.remove("open");
      overlay.classList.remove("show");
      mainContent.classList.remove("pushed");
    }
  </script>
</body>
</html>
