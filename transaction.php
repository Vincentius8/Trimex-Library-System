<?php
session_start();
if (!isset($_SESSION['admin_id'])) {
    header("Location: admin_login.php");
    exit;
}
require 'db_connection.php'; // This should define $pdo

function displayError($message) {
    echo "<p style='color:red;'>$message</p>";
    exit;
}

// === Update due date ===
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['record_id'], $_POST['record_type'], $_POST['due_date'])) {
    $record_id = filter_input(INPUT_POST, 'record_id', FILTER_VALIDATE_INT);
    $record_type = $_POST['record_type'];
    $new_due_date = $_POST['due_date'];

    if (!$record_id || !$new_due_date || !in_array($record_type, ['Transaction', 'Reservation'])) {
        displayError("Invalid input.");
    }

    try {
        if ($record_type === 'Transaction') {
            $stmt = $pdo->prepare("UPDATE book_transactions SET due_date = ? WHERE transaction_id = ?");
        } else {
            $stmt = $pdo->prepare("UPDATE reservations SET due_date = ? WHERE reservation_id = ?");
        }

        $stmt->execute([$new_due_date, $record_id]);
        header("Location: transaction.php?message=Due date updated successfully.");
        exit;
    } catch (PDOException $e) {
        $error = "Error updating due date. Please try again.";
    }
}

// === Show edit form ===
if (isset($_GET['action'], $_GET['id'], $_GET['type']) && $_GET['action'] === 'edit') {
    $record_id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
    $record_type = $_GET['type'];

    if (!$record_id || !in_array($record_type, ['Transaction', 'Reservation'])) {
        displayError("Invalid record.");
    }

    try {
        if ($record_type === 'Transaction') {
            $sql = "SELECT 
                        t.transaction_id AS record_id,
                        'Transaction' AS record_type,
                        t.due_date,
                        CASE WHEN t.return_date IS NULL THEN 'borrowed' ELSE 'returned' END AS status,
                        CONCAT(u.firstname, ' ', u.lastname) AS student_name,
                        b.title
                    FROM book_transactions t
                    JOIN users u ON t.user_id = u.user_id
                    JOIN books b ON t.book_id = b.book_id
                    WHERE t.transaction_id = ?";
        } else {
            $sql = "SELECT 
                        r.reservation_id AS record_id,
                        'Reservation' AS record_type,
                        r.due_date,
                        r.status,
                        CONCAT(u.firstname, ' ', u.lastname) AS student_name,
                        b.title
                    FROM reservations r
                    JOIN users u ON r.user_id = u.user_id
                    JOIN books b ON r.book_id = b.book_id
                    WHERE r.reservation_id = ?";
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$record_id]);
        $fetched_record = $stmt->fetch();

        if (!$fetched_record) {
            displayError("Record not found.");
        }

        $currentStatus = strtolower($fetched_record['status']);
        if (in_array($currentStatus, ['returned', 'cancelled'])) {
            echo "Due date cannot be edited. This record is already " . htmlspecialchars($fetched_record['status']) . ".";
            echo '<p><a href="transaction.php">Back to Records</a></p>';
            exit;
        }

        // === HTML FORM for editing ===
        ?>
        <!DOCTYPE html>
        <html lang="en">
        <head>
            <meta charset="UTF-8" />
            <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
            <title>Edit Due Date - Library Admin Dashboard</title>
            <link rel="icon" type="image/x-icon" href="img/LIBRARY LOGO.png">
            <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
            <style>
                body { font-family: 'Roboto', sans-serif; background-color: #f9f9fb; color: #333; padding: 20px; }
                .container { max-width: 500px; margin: 0 auto; background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
                h2 { text-align: center; margin-bottom: 20px; }
                form label { display: block; margin-bottom: 8px; font-weight: 500; }
                form input[type="date"] { width: 100%; padding: 10px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; }
                form button { width: 100%; padding: 10px; background-color: #e74c3c; border: none; border-radius: 4px; color: #fff; font-size: 1rem; cursor: pointer; transition: background-color 0.3s; }
                form button:hover { background-color: #7B1113; }
                .error { color: red; text-align: center; margin-bottom: 10px; }
            </style>
        </head>
        <body>
        <div class="container">
            <h2>Edit <?php echo htmlspecialchars($fetched_record['record_type']); ?> Due Date</h2>
            <?php if (isset($error)) echo '<div class="error">'.htmlspecialchars($error).'</div>'; ?>
            <p><strong>Student Name:</strong> <?php echo htmlspecialchars($fetched_record['student_name']); ?></p>
            <p><strong>Book Title:</strong> <?php echo htmlspecialchars($fetched_record['title']); ?></p>
            <form action="transaction.php" method="post">
                <input type="hidden" name="record_id" value="<?php echo htmlspecialchars($fetched_record['record_id']); ?>">
                <input type="hidden" name="record_type" value="<?php echo htmlspecialchars($fetched_record['record_type']); ?>">
                <label for="due_date">Due Date:</label>
                <input type="date" id="due_date" name="due_date" value="<?php echo htmlspecialchars($fetched_record['due_date']); ?>" required>
                <button type="submit">Update Due Date</button>
            </form>
            <p style="text-align:center; margin-top:15px;"><a href="transaction.php">Back to Records</a></p>
        </div>
        </body>
        </html>
        <?php
        exit;
    } catch (PDOException $e) {
        displayError("Database error: " . $e->getMessage());
    }
}

// === Display listing ===
$searchTerm = isset($_GET['search']) ? trim($_GET['search']) : '';
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

try {
    $baseQuery = "SELECT id, record_type, status, start_date, due_date, return_date, fine_amount,
                         library_id, student_name, title
                  FROM (
                    SELECT t.transaction_id AS id,
                           'Transaction' AS record_type,
                           CASE WHEN t.return_date IS NULL THEN 'Borrowed' ELSE 'Returned' END AS status,
                           t.borrow_date AS start_date,
                           t.due_date,
                           t.return_date,
                           t.fine_amount,
                           u.library_id,
                           CONCAT(u.firstname, ' ', u.lastname) AS student_name,
                           b.title
                    FROM book_transactions t
                    JOIN users u ON t.user_id = u.user_id
                    JOIN books b ON t.book_id = b.book_id
                    UNION ALL
                    SELECT r.reservation_id AS id,
                           'Reservation' AS record_type,
                           r.status,
                           r.reservation_date AS start_date,
                           r.due_date,
                           r.return_date,
                           r.fine_amount,
                           u.library_id,
                           CONCAT(u.firstname, ' ', u.lastname) AS student_name,
                           b.title
                    FROM reservations r
                    JOIN users u ON r.user_id = u.user_id
                    JOIN books b ON r.book_id = b.book_id
                  ) AS combined";

    $whereClauses = [];
    $params = [];

    if ($searchTerm !== '') {
        $whereClauses[] = "(library_id LIKE ? OR student_name LIKE ? OR title LIKE ?)";
        $like = "%" . $searchTerm . "%";
        $params[] = $like;
        $params[] = $like;
        $params[] = $like;
    }

    if ($filter === 'transaction') {
        $whereClauses[] = "record_type = ?";
        $params[] = "Transaction";
    } elseif ($filter === 'reservation') {
        $whereClauses[] = "record_type = ?";
        $params[] = "Reservation";
    }

    if (!empty($whereClauses)) {
        $baseQuery .= " WHERE " . implode(" AND ", $whereClauses);
    }

    $baseQuery .= " ORDER BY start_date DESC";
    $stmt = $pdo->prepare($baseQuery);
    $stmt->execute($params);
    $result = $stmt->fetchAll();

    
} catch (PDOException $e) {
    displayError("Error fetching records: " . $e->getMessage());
}
?>




<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Transaction & Reservation Records - Library Admin Dashboard</title>
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet">
  <script src="https://kit.fontawesome.com/your-kit.js" crossorigin="anonymous"></script>
  <style>
    :root {
      --trimex-maroon: #7B1113;
      --dark-bg: #1f1f1f;
      --light-bg: #f9f9fb;
      --accent-color: #e74c3c;
      --text-color: #333;
    }
    * { margin: 0; padding: 0; box-sizing: border-box; }
    body { font-family: 'Roboto', sans-serif; background-color: var(--light-bg); color: var(--text-color); line-height: 1.6; }
    .sidenav { position: fixed; top: 0; left: 0; height: 100%; width: 0; background: linear-gradient(180deg, var(--dark-bg), #333333); box-shadow: 2px 0 10px rgba(0,0,0,0.3); overflow-x: hidden; transition: width 0.4s ease; z-index: 100; padding-top: 60px; }
    .sidenav.open { width: 260px; }
    .sidenav a { display: block; padding: 15px 20px; text-decoration: none; color: #ccc; font-size: 1rem; transition: background 0.3s, color 0.3s, transform 0.3s; border-bottom: 1px solid rgba(255,255,255,0.1); }
    .sidenav a:hover, .sidenav a.active { background-color: rgba(123, 17, 19, 0.8); color: #fff; transform: scale(1.03); }
    .sidenav .close-btn { position: absolute; top: 10px; right: 15px; font-size: 25px; color: #bbb; cursor: pointer; transition: color 0.3s; }
    .sidenav .close-btn:hover { color: #fff; }
    .logout-link { display: block; text-align: center; background-color: #2a2a2a; color: #fff; margin: 20px 15px 0 15px; padding: 12px 0; font-weight: 500; border-radius: 4px; transition: background-color 0.3s; }
    .logout-link:hover { background-color: var(--trimex-maroon); }
    .dropdown { position: relative; }
    .dropdown-toggle { cursor: pointer; }
    .dropdown-content { display: none; background-color: #333; position: relative; margin-left: 15px; border-left: 4px solid var(--trimex-maroon); }
    .dropdown-content .dropdown-item { display: block; padding: 10px 20px; color: #ccc; text-decoration: none; font-size: 0.95rem; border-bottom: 1px solid rgba(255,255,255,0.1); transition: background 0.3s, color 0.3s; }
    .dropdown-content .dropdown-item:hover { background-color: rgba(123,17,19,0.8); color: #fff; }
    .dropdown.active .dropdown-content { display: block; }
    .overlay { position: fixed; top: 0; left: 0; right: 0; bottom: 0; background: rgba(0,0,0,0.4); opacity: 0; pointer-events: none; transition: opacity 0.4s; z-index: 50; }
    .overlay.show { opacity: 1; pointer-events: all; }
    .main-content { transition: margin-left 0.4s ease; margin-left: 0; padding: 20px; }
    header.main-header { background: #fff; border-bottom: 1px solid #ccc; padding: 0.5rem 1rem; display: flex; align-items: center; justify-content: space-between; }
    .menu-icon { font-size: 30px; cursor: pointer; }
    .top-bar { display: flex; justify-content: flex-end; align-items: center; gap: 1rem; flex-wrap: wrap; margin-top: 1rem; }
    .search-container { display: flex; align-items: center; position: relative; }
    .search-container input { padding: 0.6rem 2.5rem 0.6rem 1rem; border: 1px solid #ccc; border-radius: 50px; font-size: 1rem; outline: none; }
    .search-container input:focus { box-shadow: 0 0 8px rgba(0,0,0,0.1); }
    .search-container button { position: absolute; right: 10px; top: 50%; transform: translateY(-50%); background: none; border: none; color: #333; font-size: 1.2rem; cursor: pointer; }
    .filter-container { display: flex; align-items: center; gap: 0.5rem; }
    .filter-container select { padding: 0.5rem; border-radius: 5px; border: 1px solid #ccc; font-size: 1rem; outline: none; cursor: pointer; }
    .filter-container .btn-filter { background-color: var(--accent-color); color: #fff; border: none; border-radius: 5px; padding: 0.5rem 1rem; font-size: 1rem; cursor: pointer; transition: background-color 0.3s; }
    .filter-container .btn-filter:hover { background-color: var(--trimex-maroon); }
    .table-container { background: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); margin-top: 1rem; }
    table { width: 100%; border-collapse: collapse; }
    th, td { padding: 12px 15px; border-bottom: 1px solid #ddd; text-align: left; font-size: 0.95rem; }
    th { background-color: #f9f9fb; font-weight: 600; }
    tr:hover { background-color: #f1f1f1; }
    .edit-btn { background-color: var(--accent-color); color: #fff; padding: 5px 10px; text-decoration: none; border-radius: 4px; font-size: 0.9rem; transition: background-color 0.3s; }
    .edit-btn:hover { background-color: var(--trimex-maroon); }
    .edit-disabled { background-color: #ccc !important; cursor: not-allowed; pointer-events: none; color: #666; }
    @media (max-width: 768px) {
      .top-bar { flex-direction: column; justify-content: flex-start; }
      .search-container, .filter-container { width: 100%; }
      .main-content { margin-left: 0; }
      .sidenav { width: 0; }
    }
    .logo-area img { height: 40px; width: auto; }
  </style>
</head>
<body>
  <div id="mySidenav" class="sidenav">
    <span class="close-btn" id="closeSidenav">&times;</span>
    <a href="admin_dashboard.php" class="nav-link">Dashboard</a>
    <a href="book_management.php" class="nav-link">Books Management</a>
    <a href="user_management.php" class="nav-link">User Management</a>
    <a href="transaction.php" class="nav-link active">Transactions</a>
    <a href="admin_management.php" class="nav-link">Admin Management</a>
    <div class="dropdown">
      <a href="javascript:void(0)" class="nav-link dropdown-toggle">Statistical Reports</a>
      <div class="dropdown-content">
        <a href="attendance_dashboard.php" class="dropdown-item">Attendance Dashboard</a>
        <a href="reports_stats.php" class="dropdown-item">Report and Stats</a>
      </div>
    </div>
    <a href="backend/admin_logout.php" class="logout-link">Log Out</a>
  </div>
  <div id="overlay" class="overlay"></div>
  <div id="mainContent" class="main-content">
    <header class="main-header">
      <span class="menu-icon" id="openSidenav">&#9776;</span>
      <div class="logo-area">
        <img src="img/Trimexlogo1.png" alt="Trimex Logo"/>
        <img src="img/LIBRARY LOGO.png" alt="Library Logo"/>
      </div>
    </header>
    <div class="top-bar">
      <form method="GET" action="transaction.php" style="display: flex; align-items: center; gap: 1rem;">
        <div class="search-container">
          <input type="text" name="search" placeholder="Search by ID, name, or title" value="<?php echo htmlspecialchars($searchTerm); ?>">
          <button type="submit" aria-label="Search"><i class="fas fa-search"></i></button>
        </div>
        <div class="filter-container">
          <label for="filterSelect">Filter:</label>
          <select name="filter" id="filterSelect">
            <option value="all" <?php if($filter === 'all') echo 'selected'; ?>>All Records</option>
            <option value="transaction" <?php if($filter === 'transaction') echo 'selected'; ?>>Transactions</option>
            <option value="reservation" <?php if($filter === 'reservation') echo 'selected'; ?>>Reservations</option>
          </select>
          <button type="submit" class="btn-filter" aria-label="Apply Filter">Filter</button>
        </div>
      </form>
    </div>
    <div class="table-container">
      <table>
        <thead>
          <tr>
            <th>ID</th>
            <th>Type</th>
            <th>Library ID</th>
            <th>Student Name</th>
            <th>Book Title</th>
            <th>Start Date</th>
            <th>Due Date</th>
            <th>Return Date</th>
            <th>Status</th>
            <th>Fine (₱)</th>
            <th>Action</th>
          </tr>
        </thead>
        <tbody>
          <?php if (!empty($result)): ?>
  <?php foreach ($result as $row): 
    $statusLower = strtolower($row['status']);
    $blockEdit = ($statusLower === 'returned' || $statusLower === 'cancelled');
  ?>
    <tr>
      <td><?php echo htmlspecialchars($row['id']); ?></td>
      <td><?php echo htmlspecialchars($row['record_type']); ?></td>
      <td><?php echo htmlspecialchars($row['library_id']); ?></td>
      <td><?php echo htmlspecialchars($row['student_name']); ?></td>
      <td><?php echo htmlspecialchars($row['title']); ?></td>
      <td><?php echo htmlspecialchars($row['start_date']); ?></td>
      <td><?php echo htmlspecialchars($row['due_date']); ?></td>
      <td><?php echo $row['return_date'] ? htmlspecialchars($row['return_date']) : "N/A"; ?></td>
      <td><?php echo htmlspecialchars($row['status']); ?></td>
      <td><?php echo number_format($row['fine_amount'], 2); ?></td>
      <td>
        <?php if ($blockEdit): ?>
          <a href="#" class="edit-btn edit-disabled">Edit</a>
        <?php else: ?>
          <a href="transaction.php?action=edit&id=<?php echo htmlspecialchars($row['id']); ?>&type=<?php echo htmlspecialchars($row['record_type']); ?>" class="edit-btn">Edit</a>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
<?php else: ?>
  <tr>
    <td colspan="11" style="text-align: center;">No records found.</td>
  </tr>
<?php endif; ?>

        </tbody>
      </table>
    </div>
  </div>
  <script>
    function openNav() {
      document.getElementById("mySidenav").style.width = "260px";
      document.getElementById("mainContent").style.marginLeft = "260px";
      document.getElementById("overlay").classList.add("show");
    }
    function closeNav() {
      document.getElementById("mySidenav").style.width = "0";
      document.getElementById("mainContent").style.marginLeft = "0";
      document.getElementById("overlay").classList.remove("show");
    }
    document.getElementById("openSidenav").addEventListener("click", openNav);
    document.getElementById("closeSidenav").addEventListener("click", closeNav);
    document.getElementById("overlay").addEventListener("click", closeNav);
    
    const dropdownToggle = document.querySelector('.dropdown-toggle');
    if (dropdownToggle) {
      dropdownToggle.addEventListener('click', function() {
        this.parentElement.classList.toggle('active');
      });
    }
  </script>
</body>
</html>
