<?php
require 'backend/book_management_process.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Library Admin Dashboard - Books Management</title>
  <link rel="icon" type="image/x-icon" href="img/LIBRARY LOGO.png">
  <!-- Google Font: Poppins -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap"
    rel="stylesheet"
  />
  <link rel="stylesheet" href="css/design_bm.css">

  <style>
    /* Pagination Styles with Prev/Next */
    .pagination a {
      margin: 0 5px;
      padding: 10px 16px;
      text-decoration: none;
      border: 1px solid transparent;
      color: #333;
      border-radius: 20px;
      transition: background 0.3s, color 0.3s, box-shadow 0.3s;
    }
    .pagination a:hover {
      background: #80292b;
      color: #fff;
      box-shadow: 0 3px 8px rgba(0,0,0,0.2);
    }
    .pagination a.active {
      background: var(--trimex-maroon);
      color: #fff;
      border: 1px solid var(--trimex-maroon);
      box-shadow: 0 3px 8px rgba(0,0,0,0.3);
    }

    /* Shelf Styles */
    .shelf {
      margin-bottom: 40px;
    }
    .shelf-title {
      font-size: 1.4rem;
      color: var(--trimex-maroon);
      margin: 20px 0 10px;
      padding-left: 20px;
    }
    .shelf-title:before {
      content: "Section: ";
    }

    /* Per-shelf pagination buttons */
    .shelf-pagination {
      text-align: center;
      margin-top: 10px;
    }
    .shelf-pagination button {
      margin: 0 5px;
      padding: 5px 12px;
      border: 1px solid #ccc;
      background: var(--trimex-maroon);
      color: #fff;
      cursor: pointer;
      border-radius: 4px;
      transition: background 0.3s;
    }
    .shelf-pagination button:disabled {
      background: #eee;
      color: #999;
      cursor: default;
    }
    .shelf-pagination button:hover:not(:disabled) {
      background: #80292b;
    }
    .shelf-pagination span {
      margin: 0 8px;
      font-size: 0.9rem;
    }

  
/* Modal Backdrop */
.modal {
  position: fixed;
  z-index: 1100;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  overflow: auto;
  background-color: rgba(0, 0, 0, 0.5);
  opacity: 0;
  visibility: hidden;
  transition: opacity 0.5s ease, visibility 0.5s ease;
}
.modal.show {
  opacity: 1;
  visibility: visible;
}

/* Modal Content */
.modal-content {
  background-color: #fff;
  position: fixed;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  padding: 32px;
  border-radius: 10px;
  width: 100%;
  max-width: 650px;
  box-sizing: border-box;
  animation: zoomIn 0.5s ease-out;
  font-family: var(--font);
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
}
@keyframes zoomIn {
  from {
    opacity: 0;
    transform: scale(0.8);
  }
  to {
    opacity: 1;
    transform: scale(1);
  }
}

/* Close Button */
.modal-close {
  position: absolute;
  top: 10px;
  right: 15px;
  font-size: 20px;
  font-weight: bold;
  color: #444;
  cursor: pointer;
}

/* Form Container */
.form-container {
  display: flex;
  flex-wrap: wrap;
  gap: 18px 15px;
  justify-content: space-between;
}
.form-container h2 {
  width: 100%;
  margin-bottom: 10px;
  color: #222;
  font-size: 1.6rem;
  font-weight: 600;
}

/* Input & Select Styling */
.form-container input[type="text"],
.form-container input[type="number"],
.form-container select {
  flex: 1 1 calc(50% - 10px);
  min-width: 240px;
  border: 1.5px solid #ccc;
  border-radius: 6px;
  background-color: #fff;
  padding: 12px 14px;
  font-size: 14px;
  font-family: var(--font);
  color: #333;
  transition: border-color 0.2s ease, box-shadow 0.2s ease;
  box-shadow: inset 0 0 0 1px transparent;
  appearance: none;
}
.form-container input:focus,
.form-container select:focus {
  border-color: var(--trimex-maroon);
  box-shadow: 0 0 0 2px rgba(123, 17, 19, 0.15);
  outline: none;
}

/* File Input Styling (Wrapper) */
.file-wrapper {
  flex: 1 1 calc(50% - 10px);
  min-width: 240px;
  position: relative;
  display: flex;
  align-items: center;
  border: 1.5px solid #ccc;
  border-radius: 6px;
  background-color: #fff;
  padding: 6px 10px;
  height: 44px;
  box-sizing: border-box;
}
.file-wrapper input[type="file"] {
  width: 100%;
  padding-right: 10px;
  box-sizing: border-box;
  border: none;
  background: transparent;
  font-family: var(--font);
}

/* File Input Button */
input[type="file"]::-webkit-file-upload-button {
  background-color: var(--trimex-maroon);
  color: #fff;
  border: none;
  padding: 8px 14px;
  border-radius: 4px;
  font-size: 13px;
  cursor: pointer;
  transition: background-color 0.3s ease;
  margin-right: 10px;
}
input[type="file"]::-webkit-file-upload-button:hover {
  background-color: #5e0d0e;
}

/* Submit Button */
.form-container button[type="submit"] {
  width: 100%;
  padding: 14px;
  background-color: var(--trimex-maroon);
  color: #fff;
  border: none;
  border-radius: 6px;
  font-weight: 600;
  font-size: 16px;
  cursor: pointer;
  transition: background 0.3s ease;
}
.form-container button[type="submit"]:hover {
  background-color: #5e0d0e;
}

/* Modal Action Buttons */
.modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 20px;
}
.modal-actions button {
  padding: 8px 16px;
  font-size: 14px;
  font-weight: 600;
  border-radius: 6px;
  border: none;
  cursor: pointer;
  font-family: var(--font);
  transition: background-color 0.2s ease;
}
.modal-actions .btn-danger {
  background-color: #dc3545;
  color: #fff;
}
.modal-actions .btn-danger:hover {
  background-color: #c82333;
}
.modal-actions .btn-secondary {
  background-color: #6c757d;
  color: #fff;
}
.modal-actions .btn-secondary:hover {
  background-color: #545b62;
}

/* Responsive Layout */
@media (max-width: 768px) {
  .form-container input,
  .form-container select,
  .form-container .file-wrapper {
    flex: 1 1 100%;
  }
}


  </style>
</head>
<body>
  <!-- SIDENAV -->
  <div id="mySidenav" class="sidenav">
    <span class="close-btn" id="closeSidenav">&times;</span>
    <a href="admin_dashboard.php" class="nav-link">Dashboard</a>
    <a href="book_management.php" class="nav-link active">Books Management</a>
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
    <a href="backend/admin_logout.php" class="logout-link">Logout</a>
    </div>

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
        <img src="<?php echo !empty($admin_profile) ? 'uploads/' . htmlspecialchars($admin_profile) : 'https://via.placeholder.com/40'; ?>" alt="Admin Photo"/>
        <span><?php echo htmlspecialchars($adminName); ?></span>
      </div>
    </div>

    <!-- Greeting Message -->
    <div id="greeting" class="greeting">
      <h1>Welcome, <?php echo htmlspecialchars($adminName); ?>!</h1>
      <p>You are now logged in as administrator.</p>
    </div>

    <!-- Books Management Section -->
    <div id="books-section" class="dashboard-section active-section">
      <h2 class="section-title">Books Management (<?php echo ucfirst($view); ?> Books)</h2>

      <!-- Stats Cards -->
      <div class="stats-container">
        <?php
try {
    $sqlStats = "SELECT course, COUNT(*) AS count FROM books GROUP BY course";
    $stmtStats = $pdo->prepare($sqlStats);
    $stmtStats->execute();
    $resultStats = $stmtStats->fetchAll(PDO::FETCH_ASSOC);

    if (count($resultStats) > 0) {
        foreach ($resultStats as $row) {
            echo '<div class="stats-card">';
            echo '<h3>' . htmlspecialchars($row["course"]) . '</h3>';
            echo '<p>' . htmlspecialchars($row["count"]) . '</p>';
            echo '</div>';
        }
    } else {
        echo "<p>No stats available.</p>";
    }
} catch (PDOException $e) {
    echo "<p>Error fetching stats: " . htmlspecialchars($e->getMessage()) . "</p>";
}
?>

      </div>

      <!-- Search & View Buttons -->
      <div class="search-container">
        <div class="left-buttons">
          <button id="addBookBtn" class="add-book-btn">Add Book</button>
          <a href="?view=active" class="view-btn">Active Books</a>
          <a href="?view=missing" class="view-btn">Missing Books</a>
          <a href="?view=discarded" class="view-btn">Discarded Books</a>
          <a href="?view=borrowed" class="view-btn">Borrowed Books</a>
        </div>
        <form method="GET" action="" style="display: flex; gap: 10px; align-items: center;">
          <input type="hidden" name="view" value="<?php echo htmlspecialchars($view); ?>">
          <input type="text" name="search" placeholder="Search Books" value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
          <button type="submit">Search</button>
          <button type="button" id="refreshBtn" class="refresh-btn" onclick="window.location.href='?view=<?php echo htmlspecialchars($view); ?>'">Refresh</button>
        </form>
      </div>

      <!-- Books Listing -->
      <div class="books-listing">
        <?php
          if (!empty($currentShelves)) {
            foreach ($currentShelves as $shelf) {
              $course          = $shelf['course'];
              $books           = $shelf['books'];
              $shelfId         = 'shelf-' . md5($course);
              $totalPagesShelf = ceil(count($books) / 8);

              echo '<div class="shelf" data-shelf-id="' . $shelfId . '">';
              echo '<h3 class="shelf-title">' . htmlspecialchars($course) . '</h3>';
              echo '<div id="' . $shelfId . '" class="books-container">'; 
              foreach ($books as $idx => $book) {
                $cover = $book['book_image'] ?: 'https://via.placeholder.com/150';
                echo '<div class="book-card" data-id="' . $book['book_id'] . '" data-index="' . $idx . '">';
                echo '  <div class="img-container"><img src="' . $cover . '" alt="Book Cover"></div>';
                echo '  <div class="book-info">';
                echo '    <h4>' . htmlspecialchars($book['title']) . '</h4>';
                echo '    <p class="details">Accession No: ' . htmlspecialchars($book['accession_no']) . '</p>';
                echo '    <p class="details">Copies: ' . htmlspecialchars($book['copies']) . '</p>';
                echo '    <p class="details">Author: ' . htmlspecialchars($book['author']) . '</p>';
                echo '    <p class="details">Publish Year: ' . htmlspecialchars($book['copyright']) . '</p>';
                echo '    <p class="details">Section: ' . htmlspecialchars($book['course']) . '</p>';
                if ($view === 'borrowed') {
                  echo '<p class="details">Borrowed By: ' . htmlspecialchars($book['firstname']) . ' ' . htmlspecialchars($book['lastname']) . '</p>';
                  echo '<p class="details">Library ID: ' . htmlspecialchars($book['library_id']) . '</p>';
                }
                echo '  </div>';
                echo '  <div class="actions">';
                echo '    <button class="edit-btn edit"'
                   . ' data-id="' . $book['book_id'] . '"'
                   . ' data-accession_no="' . htmlspecialchars($book['accession_no']) . '"'
                   . ' data-call_no="'      . htmlspecialchars($book['call_no']) . '"'
                   . ' data-author="'       . htmlspecialchars($book['author']) . '"'
                   . ' data-title="'        . htmlspecialchars($book['title']) . '"'
                   . ' data-publisher="'    . htmlspecialchars($book['publisher']) . '"'
                   . ' data-copies="'       . $book['copies'] . '"'
                   . ' data-copyright="'    . htmlspecialchars($book['copyright']) . '"'
                   . ' data-course="'       . htmlspecialchars($book['course']) . '"'
                   . ' data-availability="' . htmlspecialchars($book['status']) . '"'
                   . ' data-cover_image="'  . htmlspecialchars($book['book_image']) . '">Edit</button>';
                echo '    <button class="delete" onclick="confirmDelete(' . $book['book_id'] . ')">Delete</button>';
                if ($view === 'active') {
                  echo '<button class="discard" onclick="confirmDiscard(' . $book['book_id'] . ')">Discard</button>';
                }
                echo '  </div>';
                echo '</div>';
                
              }
              echo '</div>'; // end books-container

              // shelf pagination controls
              echo '<div class="shelf-pagination" data-target="' . $shelfId . '">';
              echo '  <button class="shelf-prev" disabled>Prev</button>';
              echo '  <span class="shelf-page">1/' . $totalPagesShelf . '</span>';
              echo '  <button class="shelf-next"' . ($totalPagesShelf > 1 ? '' : ' disabled') . '>Next</button>';
              echo '</div>';

              echo '</div>'; // end shelf
            }
          } else {
            echo '<p>No books found.</p>';
          }
        ?>
      </div>

      <!-- Pagination -->
      <div class="pagination" style="text-align:center; margin-top:20px;">
        <?php if ($currentPage > 1): ?>
          <a href="?view=<?php echo htmlspecialchars($view); ?>&page=<?php echo $currentPage - 1; ?>">Previous</a>
        <?php endif; ?>
        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
          <a href="?view=<?php echo htmlspecialchars($view); ?>&page=<?php echo $i; ?>"
             <?php if ($i == $currentPage) echo 'class="active"'; ?>>
            <?php echo $i; ?>
          </a>
        <?php endfor; ?>
        <?php if ($currentPage < $totalPages): ?>
          <a href="?view=<?php echo htmlspecialchars($view); ?>&page=<?php echo $currentPage + 1; ?>">Next</a>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- ADD MODAL -->
<div id="addModal" class="modal">
  <div class="modal-content">
    <span id="addModalClose" class="modal-close">&times;</span>
    <div class="form-container">
      <h2>Add New Book</h2>
      <form id="addForm" method="POST" action="" enctype="multipart/form-data">
        <input type="hidden" name="action" value="add">
        <input type="text" name="accession_no" placeholder="Accession No" required>
        <input type="text" name="call_no" placeholder="Call No" required>
        <input type="number" name="copies" placeholder="Copies" value="1" required>
        <input type="text" name="author" placeholder="Author">
        <input type="text" name="title" placeholder="Title" required>
        <input type="text" name="publisher" placeholder="Publisher">
        <input type="text" name="copyright" placeholder="Publish Year">
        <input type="text" name="course" placeholder="Section">
        <select name="availability">
          <option value="available">Available</option>
          <option value="borrowed">Borrowed</option>
          <option value="missing">Missing</option>
        </select>
        <div class="file-wrapper">
          <input type="file" name="cover_image" accept="image/*">
        </div>
        <button type="submit">Add Book</button>
      </form>
    </div>
  </div>
</div>

<!-- EDIT MODAL -->
<div id="editModal" class="modal">
  <div class="modal-content">
    <span id="modalClose" class="modal-close">&times;</span>
    <div class="form-container">
      <h2>Edit Book</h2>
      <form id="editForm"
            method="POST"
            action="?view=<?php echo htmlspecialchars($view); ?>&page=<?php echo $currentPage; ?>"
            enctype="multipart/form-data">
        <input type="hidden" name="action" value="edit">
        <input type="hidden" name="id" id="edit-id">
        <input type="hidden" name="existing_cover" id="existing_cover" value="">
        <input type="text" name="accession_no" placeholder="Accession No" id="edit-accession_no" required>
        <input type="text" name="call_no" placeholder="Call No" id="edit-call_no" required>
        <input type="number" name="copies" placeholder="Copies" id="edit-copies" required>
        <input type="text" name="author" placeholder="Author" id="edit-author">
        <input type="text" name="title" placeholder="Title" id="edit-title" required>
        <input type="text" name="publisher" placeholder="Publisher" id="edit-publisher">
        <input type="text" name="copyright" placeholder="Publish Year" id="edit-copyright">
        <input type="text" name="course" placeholder="Section" id="edit-course">
        <select name="availability" id="edit-availability">
          <option value="available">Available</option>
          <option value="borrowed">Borrowed</option>
          <option value="missing">Missing</option>
        </select>
        <div class="file-wrapper">
          <input type="file" name="cover_image" accept="image/*">
        </div>
        <button type="submit">Update Book</button>
      </form>
    </div>
  </div>
</div>

  <!-- DELETE CONFIRMATION MODAL -->
  <div id="deleteModal" class="modal">
    <div class="modal-content">
      <span id="deleteModalClose" class="modal-close">&times;</span>
      <h2>Confirm Deletion</h2>
      <p>Are you sure you want to delete this book?</p>
      <div class="modal-actions">
        <button id="deleteConfirmBtn">Delete</button>
        <button id="deleteCancelBtn">Cancel</button>
      </div>
    </div>
  </div> 

  <!-- DISCARD CONFIRMATION MODAL -->
  <div id="discardModal" class="modal">
    <div class="modal-content">
      <span id="discardModalClose" class="modal-close">&times;</span>
      <h2>Confirm Discard</h2>
      <p>Are you sure you want to discard this book?</p>
      <div class="modal-actions">
        <button id="discardConfirmBtn">Discard</button>
        <button id="discardCancelBtn">Cancel</button>
      </div>
    </div>
  </div>

  <!-- ERROR MODAL -->
  <div id="errorModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        Error
        <span id="errorModalClose" class="modal-close">&times;</span>
      </div>
      <div class="modal-body">Duplicate Accession Number!</div>
      <div class="modal-actions"><button id="errorOkBtn">OK</button></div>
    </div>
  </div>

  <!-- SUCCESS MODAL -->
  <div id="successModal" class="modal">
    <div class="modal-content">
      <div class="modal-header">
        Success
        <span id="successModalClose" class="modal-close">&times;</span>
      </div>
      <div class="modal-body" id="successMessage">The Book Successfully Added!</div>
      <div class="modal-actions"><button id="successOkBtn">OK</button></div>
    </div>
  </div>

  
  <!-- SCRIPTS -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  <script>
    // Build per-shelf pagination controllers
    const shelfControllers = {};
    document.querySelectorAll('.shelf-pagination').forEach(ctrl => {
      const shelfId       = ctrl.dataset.target;
      const container     = document.getElementById(shelfId);
      const cards         = Array.from(container.children);
      const prevBtn       = ctrl.querySelector('.shelf-prev');
      const nextBtn       = ctrl.querySelector('.shelf-next');
      const pageIndicator = ctrl.querySelector('.shelf-page');

      const controller = {
        page: 0,
        perPage: 8,
        cards,
        prevBtn,
        nextBtn,
        pageIndicator,
        renderPage() {
          const total = Math.ceil(this.cards.length / this.perPage);
          this.cards.forEach((c,i) => {
            c.style.display = (i >= this.page*this.perPage && i < (this.page+1)*this.perPage) ? '' : 'none';
          });
          this.prevBtn.disabled = this.page === 0;
          this.nextBtn.disabled = this.page === total - 1;
          this.pageIndicator.textContent = (this.page + 1) + '/' + total;
        }
      };

      prevBtn.addEventListener('click', () => {
        if (controller.page > 0) {
          controller.page--;
          controller.renderPage();
        }
      });
      nextBtn.addEventListener('click', () => {
        const total = Math.ceil(controller.cards.length / controller.perPage);
        if (controller.page < total - 1) {
          controller.page++;
          controller.renderPage();
        }
      });

      controller.renderPage();
      shelfControllers[shelfId] = controller;
    });

    // Existing ID-based close handlers (unchanged)
    ['addModalClose','modalClose','errorModalClose','errorOkBtn','validationErrorCloseBtn','successModalClose','successOkBtn']
      .forEach(id => document.getElementById(id)?.addEventListener('click', () => {
        const m = document.getElementById(id.replace(/CloseBtn|Close/,'Modal'));
        m?.classList.remove('show');
      }));

    // ** NEW: generic “×” handler for _any_ modal-close span **
    document.querySelectorAll('.modal-close').forEach(x => {
      x.addEventListener('click', () => {
        const modal = x.closest('.modal, .modal-validation');
        if (modal) modal.classList.remove('show');
      });
    });

    // Delete/Discard handlers
    let selectedDeleteBookId = null, selectedDiscardBookId = null;
    function confirmDelete(id) {
      selectedDeleteBookId = id;
      document.getElementById('deleteModal').classList.add('show');
    }
    function confirmDiscard(id) {
      selectedDiscardBookId = id;
      document.getElementById('discardModal').classList.add('show');
    }
    document.getElementById('deleteConfirmBtn').addEventListener('click', () => {
      window.location.href = `?view=<?php echo htmlspecialchars($view); ?>&page=<?php echo $currentPage; ?>&delete=${selectedDeleteBookId}`;
    });
    document.getElementById('deleteCancelBtn').addEventListener('click', () => {
      document.getElementById('deleteModal').classList.remove('show');
    });
    document.getElementById('discardConfirmBtn').addEventListener('click', () => {
      const f = document.createElement('form');
      f.method = 'POST'; f.action = `?view=<?php echo htmlspecialchars($view); ?>&page=<?php echo $currentPage; ?>`;
      f.innerHTML = '<input type="hidden" name="action" value="discard">'
                  + `<input type="hidden" name="id" value="${selectedDiscardBookId}">`;
      document.body.appendChild(f); f.submit();
    });
    ['discardCancelBtn','discardModalClose'].forEach(id => {
      document.getElementById(id)?.addEventListener('click', () => {
        document.getElementById('discardModal').classList.remove('show');
      });
    });

    // Add/Edit form triggers & validation
    document.querySelectorAll('.edit-btn').forEach(btn => {
      btn.addEventListener('click', function() {
        ['id','accession_no','call_no','author','title','publisher','copies','copyright','course','availability']
          .forEach(f => {
            const el = document.getElementById('edit-' + f);
            if (el) el.value = this.getAttribute('data-'+f) || '';
          });
        const existing = document.getElementById('existing_cover');
        if (existing) existing.value = this.getAttribute('data-cover_image') || '';
        document.getElementById('editModal').classList.add('show');
      });
    });
    document.getElementById('addBookBtn').addEventListener('click', () =>
      document.getElementById('addModal').classList.add('show')
    );
    document.getElementById('addForm').addEventListener('submit', function(e){
      for (let inp of this.querySelectorAll('input[type="text"]')) {
        if (inp.value.trim() !== inp.value) {
          e.preventDefault();
          document.getElementById('validationErrorMessage').textContent =
            "Remove extra spaces in: " + inp.placeholder;
          document.getElementById('validationErrorModal').classList.add('show');
          return false;
        }
      }
    });

    // Sidenav toggle
    const sidenav = document.getElementById("mySidenav"),
          overlay = document.getElementById("overlay"),
          main    = document.getElementById("main");
    document.getElementById("openSidenav").addEventListener("click", () => {
      sidenav.classList.add("open");
      overlay.classList.add("show");
      main.classList.add("pushed");
    });
    ['closeSidenav','overlay'].forEach(el => {
      document.getElementById(el)?.addEventListener('click', () => {
        sidenav.classList.remove("open");
        overlay.classList.remove("show");
        main.classList.remove("pushed");
      });
    });

    /* dropdown inside side‑nav */
document.querySelectorAll('.dropdown-toggle').forEach(btn=>{
  btn.addEventListener('click', ()=> btn.parentElement.classList.toggle('active'));
});

    // Highlight newly added/edited card
    document.addEventListener('DOMContentLoaded', () => {
      const params   = new URLSearchParams(window.location.search);
      const targetId = params.get('added') || params.get('edited');
      if (!targetId) return;
      const card = document.querySelector(`.book-card[data-id="${targetId}"]`);
      if (!card) return;
      const container = card.closest('.books-container');
      const ctl       = shelfControllers[container.id];
      const idx       = Array.from(container.children).indexOf(card);
      ctl.page        = Math.floor(idx / ctl.perPage);
      ctl.renderPage();
      card.style.boxShadow = '0 0 8px rgba(0,128,0,0.7)';
      card.style.border    = '2px solid #28a745';
    });
  </script>
</body>
</html>
