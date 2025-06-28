<?php
require 'backend/admin_management_process.php';
// rest of your frontend code here
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Admin Management - Library Dashboard</title>
  <link rel="icon" type="image/x-icon" href="img/LIBRARY LOGO.png">
  <!-- Google Font: Poppins -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet" />
  <!-- Font Awesome for icons -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" />
  <!-- Bootstrap CSS (for modal design) -->
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
  <link rel="stylesheet" href="css/am.css">
</head>
<body>
  <!-- Error Modal (Bootstrap Style) -->
  <?php if(isset($_SESSION['error_message'])): ?>
  <div class="modal fade" id="errorModal" tabindex="-1" role="dialog" aria-labelledby="errorModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="errorModalLabel">Error</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="$('#errorModal').modal('hide');">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <?php 
            echo $_SESSION['error_message']; 
            unset($_SESSION['error_message']);
          ?>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal" onclick="$('#errorModal').modal('hide');">Close</button>
        </div>
      </div>
    </div>
  </div>
  <?php endif; ?>

  <!-- SIDENAV -->
  <div id="mySidenav" class="sidenav">
    <span class="close-btn" id="closeSidenav">&times;</span>
    <a href="admin_dashboard.php" class="nav-link">Dashboard</a>
    <a href="book_management.php" class="nav-link">Books Management</a>
    <a href="user_management.php" class="nav-link">User Management</a>
    <a href="transaction.php" class="nav-link">Transactions</a>
    <a href="admin_management.php" class="nav-link active">Admin Management</a>
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
    </div>

    <!-- Greeting with first name ng naka-login na admin -->
<div id="greeting" class="greeting">
  <h1>Welcome, <?php echo htmlspecialchars($admin_firstname); ?>!</h1>
  <p>You are now logged in as administrator.</p>
</div>


    <!-- Button to open Add Admin modal -->
    <button id="openAddModal" style="margin-bottom:20px; padding:10px 20px; background:var(--primary-color); color:#fff; border:none; border-radius:4px; cursor:pointer;">
      Add New Admin
    </button>

    <!-- Admin Table -->
    <h2>List of Admins</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>School ID</th>
          <th>First Name</th>
          <th>Last Name</th>
          <th>Email</th>
          <th>Profile Image</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($result_admins as $admin): ?>
  <tr>
    <td><?php echo htmlspecialchars($admin['admin_id']); ?></td>
    <td><?php echo htmlspecialchars($admin['school_id']); ?></td>
    <td><?php echo htmlspecialchars($admin['firstname']); ?></td>
    <td><?php echo htmlspecialchars($admin['lastname']); ?></td>
    <td><?php echo htmlspecialchars($admin['email']); ?></td>
    <td>
      <?php if (!empty($admin['profile_image'])): ?>
        <a 
          href="uploads/<?php echo htmlspecialchars($admin['profile_image']); ?>" 
          target="_blank" 
          title="Click to view full image"
        >
          <img 
            src="uploads/<?php echo htmlspecialchars($admin['profile_image']); ?>" 
            alt="Profile Image" 
            style="width:40px; height:40px; border-radius:50%; object-fit: cover;"
          >
        </a>
      <?php else: ?>
        N/A
      <?php endif; ?>
    </td>
    <td>
      <a 
        class="action-link edit-btn" 
        data-id="<?php echo $admin['admin_id']; ?>"
        data-school="<?php echo htmlspecialchars($admin['school_id']); ?>"
        data-firstname="<?php echo htmlspecialchars($admin['firstname']); ?>"
        data-lastname="<?php echo htmlspecialchars($admin['lastname']); ?>"
        data-email="<?php echo htmlspecialchars($admin['email']); ?>"
        href="javascript:void(0);"
      >
        Edit
      </a>
      <a 
        class="action-link delete-btn" 
        data-id="<?php echo $admin['admin_id']; ?>" 
        href="javascript:void(0);"
      >
        Delete
      </a>
    </td>
  </tr>
<?php endforeach; ?>

      </tbody>
    </table>

    <!-- Modal for Add/Edit Admin -->
    <div id="adminModal" class="modal">
      <div class="modal-content">
        <span class="close-modal" id="closeAdminModal">&times;</span>
        <h2 id="modalTitle">Add New Admin</h2>
        <form class="admin-form" id="adminForm" method="post" action="admin_management.php" enctype="multipart/form-data">
          <input type="hidden" name="admin_id" id="admin_id" value="">
          
          <label for="school_id_modal">School ID:</label>
          <input type="text" name="school_id" id="school_id_modal" required>
          
          <label for="firstname_modal">First Name:</label>
          <input type="text" name="firstname" id="firstname_modal" required>
          
          <label for="lastname_modal">Last Name:</label>
          <input type="text" name="lastname" id="lastname_modal" required>
          
          <label for="email_modal">Email:</label>
          <input type="email" name="email" id="email_modal" required>
          
          <label for="password_modal" id="passwordLabel">Password:</label>
          <input type="password" name="password" id="password_modal" required>
          
          <label for="profile_image_modal">Profile Image:</label>
          <input type="file" name="profile_image" id="profile_image_modal" accept="image/*">
          <small>Will be auto-compressed to ≤10 KB before upload.</small>
          
          <input type="submit" id="adminFormSubmit" name="add_admin" value="Add Admin">
        </form>
      </div>
    </div>

    <!-- Modal for Delete Confirmation -->
    <div id="deleteModal" class="modal">
      <div class="modal-content">
        <span class="close-modal" id="closeDeleteModal">&times;</span>
        <h2>Confirm Delete</h2>
        <p>Are you sure you want to delete this admin?</p>
        <form method="post" action="admin_management.php">
          <input type="hidden" name="delete_admin_id" id="delete_admin_id" value="">
          <input type="submit" name="confirm_delete" value="Yes, Delete" style="background: var(--primary-color); color:#fff; border:none; padding:10px 20px; border-radius:4px; cursor:pointer;">
        </form>
      </div>
    </div>
  </div>

  <!-- jQuery and Bootstrap JS (for modal functionality) -->
  <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
  
  <script>
    // SIDENAV Toggle
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
    if(closeSidenavBtn) {
      closeSidenavBtn.addEventListener("click", closeNav);
    }
    overlay.addEventListener("click", closeNav);
    function closeNav() {
      sidenav.classList.remove("open");
      overlay.classList.remove("show");
      mainContent.classList.remove("pushed");
    }

    // Modal Handling for Add/Edit Admin
    const adminModal = document.getElementById("adminModal");
    const closeAdminModalBtn = document.getElementById("closeAdminModal");
    const openAddModalBtn = document.getElementById("openAddModal");
    const modalTitle = document.getElementById("modalTitle");
    const adminForm = document.getElementById("adminForm");
    const adminFormSubmit = document.getElementById("adminFormSubmit");
    const password_modal = document.getElementById("password_modal");
    const passwordLabel = document.getElementById("passwordLabel");
    const admin_id_field = document.getElementById("admin_id");
    const school_id_modal = document.getElementById("school_id_modal");
    const firstname_modal = document.getElementById("firstname_modal");
    const lastname_modal = document.getElementById("lastname_modal");
    const email_modal = document.getElementById("email_modal");

    // Open Add Modal
    openAddModalBtn.addEventListener("click", () => {
      adminForm.reset();
      modalTitle.textContent = "Add New Admin";
      adminFormSubmit.name = "add_admin";
      adminFormSubmit.value = "Add Admin";
      password_modal.required = true;
      password_modal.value = "";
      passwordLabel.textContent = "Password:";
      adminModal.style.display = "block";
      overlay.classList.add("show");
    });

    // Open Edit Modal
    const editButtons = document.querySelectorAll(".edit-btn");
    editButtons.forEach(btn => {
      btn.addEventListener("click", () => {
        const id = btn.getAttribute("data-id");
        const school = btn.getAttribute("data-school");
        const firstname = btn.getAttribute("data-firstname");
        const lastname = btn.getAttribute("data-lastname");
        const email = btn.getAttribute("data-email");

        admin_id_field.value = id;
        school_id_modal.value = school;
        firstname_modal.value = firstname;
        lastname_modal.value = lastname;
        email_modal.value = email;

        modalTitle.textContent = "Edit Admin";
        adminFormSubmit.name = "update_admin";
        adminFormSubmit.value = "Update Admin";
        password_modal.required = false;
        password_modal.value = "";
        passwordLabel.textContent = "New Password (leave blank to keep unchanged)";

        adminModal.style.display = "block";
        overlay.classList.add("show");
      });
    });

    // Delete Modal Handling
    const deleteModal = document.getElementById("deleteModal");
    const closeDeleteModalBtn = document.getElementById("closeDeleteModal");
    const delete_admin_id_field = document.getElementById("delete_admin_id");

    const deleteButtons = document.querySelectorAll(".delete-btn");
    deleteButtons.forEach(btn => {
      btn.addEventListener("click", () => {
        const id = btn.getAttribute("data-id");
        delete_admin_id_field.value = id;
        deleteModal.style.display = "block";
        overlay.classList.add("show");
      });
    });

    // Close Modals on clicking close buttons or overlay
    closeAdminModalBtn.addEventListener("click", () => {
      adminModal.style.display = "none";
      overlay.classList.remove("show");
    });
    closeDeleteModalBtn.addEventListener("click", () => {
      deleteModal.style.display = "none";
      overlay.classList.remove("show");
    });
    window.addEventListener("click", (e) => {
      if (e.target == adminModal) {
        adminModal.style.display = "none";
        overlay.classList.remove("show");
      }
      if (e.target == deleteModal) {
        deleteModal.style.display = "none";
        overlay.classList.remove("show");
      }
    });

    // Dropdown Toggle for Statistical Reports in Sidebar
    const dropdownToggle = document.querySelector('.dropdown-toggle');
    if (dropdownToggle) {
      dropdownToggle.addEventListener('click', function() {
        this.parentElement.classList.toggle('active');
      });
    }

    // Trigger error modal (Bootstrap) if error exists in the DOM
    $(document).ready(function(){
      if ($('#errorModal').length) {
        $('#errorModal').modal('show');
      }
    });
  </script>

  <!-- Client-side compression script -->
  <script>
    // compressImage(): draw to canvas, binary-search JPEG quality to ≤10 KB
    async function compressImage(file) {
      const img = new Image();
      img.src = URL.createObjectURL(file);
      await img.decode();
      // Scale down if too large
      const maxDim = Math.max(img.width, img.height);
      const scale  = maxDim > 512 ? 512 / maxDim : 1;
      const w = img.width * scale, h = img.height * scale;
      const canvas = Object.assign(document.createElement('canvas'), { width: w, height: h });
      canvas.getContext('2d').drawImage(img, 0, 0, w, h);

      let minQ = 0.1, maxQ = 1, blob;
      for (let i = 0; i < 6; i++) {
        const q = (minQ + maxQ) / 2;
        blob = await new Promise(r => canvas.toBlob(r, 'image/jpeg', q));
        if (blob.size > 10240) maxQ = q;
        else minQ = q;
      }
      URL.revokeObjectURL(img.src);
      return blob;
    }

    // Replace file input with compressed blob
    document.getElementById('profile_image_modal')
      .addEventListener('change', async function() {
        if (!this.files.length) return;
        const original = this.files[0];
        const compressedBlob = await compressImage(original);
        const newFile = new File([compressedBlob], original.name, { type: 'image/jpeg' });
        const dt = new DataTransfer();
        dt.items.add(newFile);
        this.files = dt.files;
    });
  </script>
</body>
</html>
