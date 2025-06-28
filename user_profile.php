<?php
require 'backend/user_profile_process.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>User Profile</title>
  <link rel="icon" type="image/x-icon" href="img/LIBRARY LOGO.png">
  <!-- Google Fonts -->
  <link 
    href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" 
    rel="stylesheet"
  />
  <!-- Font Awesome for icons -->
  <link 
    rel="stylesheet" 
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" 
    integrity="sha512-Fo3rlrZj/k7ujTnH2N2c7z8z6THbS0NChCtNqOR91I25ZU2CEvZ/b06k9aWTxWf8JZfCILEN09v7Ph0hASrXiA==" 
    crossorigin="anonymous" 
    referrerpolicy="no-referrer" 
  />
  <link rel="stylesheet" href="css/user_profile.css">
  <!-- Font Awesome JS -->
  <script 
    src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/js/all.min.js" 
    integrity="sha512-KG8iTxprbSCKNlh7OI0vvqh1DiHeyELbqsZ+0TFYzN4lkIbtX/+JAthcEhaY3oAHFvrqo/K0g0Ow6Z2N8AUIzw==" 
    crossorigin="anonymous" 
    referrerpolicy="no-referrer">
  </script>
</head>
<body>
  <!-- TOP NAVIGATION BAR without Notification Icon -->
  <div class="topbar">
    <a href="student_dashboard.php" class="back-btn">
      <i class="fas fa-arrow-left"></i> Back
    </a>
  </div>

  <!-- COVER PHOTO -->
  <div class="cover-photo"></div>

  <!-- PROFILE SECTION -->
  <div class="profile-section">
    <div class="profile-info">
      <img 
        src="<?php echo htmlspecialchars($user['profile_image'] ? $user['profile_image'] : 'img/default_profile.jpg'); ?>" 
        alt="Profile Image" 
        class="avatar"
      >
      <div class="profile-details">
        <h1><?php echo htmlspecialchars($user['firstname'] . ' ' . $user['lastname']); ?></h1>
        <p>Library ID: <?php echo htmlspecialchars($user['library_id']); ?> | Email: <?php echo htmlspecialchars($user['email']); ?></p>
        <!-- Container for side-by-side buttons -->
        <div class="button-container">
          <button class="edit-profile-btn" id="openEditModal">Edit Profile</button>
          <button class="edit-profile-btn" id="openChangePasswordModal">Change Password</button>
        </div>
      </div>
    </div>
  </div>

  <!-- MAIN CONTENT -->
  <div class="main-content">
    <!-- CURRENT BORROWED & RESERVED -->
    <div class="card">
      <h2>Currently Borrowed & Reserved</h2>
      <div class="scroll-container">
        <?php if (empty($currentRecords)): ?>
          <p>No current records found.</p>
        <?php else: ?>
          <ul class="book-list">
            <?php foreach ($currentRecords as $item): ?>
              <li class="book-item">
                <img 
                  src="<?php echo htmlspecialchars($item['book_image'] ? $item['book_image'] : 'img/book_cover.jpg'); ?>" 
                  alt="Book Cover"
                >
                <div class="book-info">
                  <strong><?php echo htmlspecialchars($item['title']); ?></strong>
                  <small><?php echo htmlspecialchars($item['author']); ?></small>
                  <?php if ($item['entry_type'] === 'borrowed'): ?>
                    <small>Borrowed on: <?php echo htmlspecialchars(date("M d, Y", strtotime($item['borrow_date']))); ?></small>
                    <?php if (!empty($item['fine_amount']) && $item['fine_amount'] > 0): ?>
                      <small class="fine">Fine: ₱<?php echo number_format($item['fine_amount'], 2); ?></small>
                    <?php endif; ?>
                  <?php else: ?>
                    <small>Reserved on: <?php echo htmlspecialchars(date("M d, Y", strtotime($item['reservation_date']))); ?></small>
                    <small>Status: <?php echo htmlspecialchars($item['status']); ?></small>
                    <?php if (!empty($item['fine_amount']) && $item['fine_amount'] > 0): ?>
                      <small class="fine">Fine: ₱<?php echo number_format($item['fine_amount'], 2); ?></small>
                    <?php endif; ?>
                  <?php endif; ?>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>

    <!-- HISTORY -->
    <div class="card">
      <h2>History</h2>
      <div class="scroll-container">
        <?php if (empty($historyRecords)): ?>
          <p>No history records found.</p>
        <?php else: ?>
          <ul class="book-list">
            <?php foreach ($historyRecords as $hitem): ?>
              <li class="book-item">
                <img 
                  src="<?php echo htmlspecialchars($hitem['book_image'] ? $hitem['book_image'] : 'img/book_cover.jpg'); ?>" 
                  alt="Book Cover"
                >
                <div class="book-info">
                  <strong><?php echo htmlspecialchars($hitem['title']); ?></strong>
                  <small><?php echo htmlspecialchars($hitem['author']); ?></small>
                  <?php if ($hitem['entry_type'] === 'borrowed_history'): ?>
                    <small>Borrowed on: <?php echo htmlspecialchars(date("M d, Y", strtotime($hitem['borrow_date']))); ?></small>
                    <small>Returned on: <?php echo htmlspecialchars(date("M d, Y", strtotime($hitem['return_date']))); ?></small>
                    <?php if (!empty($hitem['fine_amount']) && $hitem['fine_amount'] > 0): ?>
                      <small class="fine">Fine: ₱<?php echo number_format($hitem['fine_amount'], 2); ?></small>
                    <?php endif; ?>
                  <?php else: ?>
                    <small>Reserved on: <?php echo htmlspecialchars(date("M d, Y", strtotime($hitem['reservation_date']))); ?></small>
                    <small>Returned on: <?php echo htmlspecialchars(date("M d, Y", strtotime($hitem['return_date']))); ?></small>
                    <?php if (!empty($hitem['fine_amount']) && $hitem['fine_amount'] > 0): ?>
                      <small class="fine">Fine: ₱<?php echo number_format($hitem['fine_amount'], 2); ?></small>
                    <?php endif; ?>
                  <?php endif; ?>
                </div>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <!-- EDIT PROFILE MODAL -->
  <div class="modal" id="editModal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Edit Profile</h3>
        <span class="modal-close" id="closeEditModal">&times;</span>
      </div>
      <form method="post" action="edit_profile_process.php" enctype="multipart/form-data">
        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
        
        <label for="firstname">First Name:</label>
        <input type="text" id="firstname" name="firstname" value="<?php echo htmlspecialchars($user['firstname']); ?>" required>
        
        <label for="lastname">Last Name:</label>
        <input type="text" id="lastname" name="lastname" value="<?php echo htmlspecialchars($user['lastname']); ?>" required>
        
        <label for="email">Email:</label>
        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        
        <label for="profile_image">Profile Image:</label>
        <input type="file" id="profile_image" name="profile_image" accept="image/*">

        <input type="submit" name="update_profile" value="Update Profile">
      </form>
    </div>
  </div>

  <!-- CHANGE PASSWORD MODAL -->
  <div class="modal" id="changePasswordModal">
    <div class="modal-content">
      <div class="modal-header">
        <h3>Change Password</h3>
        <span class="modal-close" id="closeChangePasswordModal">&times;</span>
      </div>
      <form method="post" action="change_password_process.php">
        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_id); ?>">
        
        <label for="current_password">Current Password:</label>
        <div class="password-container">
          <input type="password" id="current_password" name="current_password" required>
          <i class="fas fa-eye toggle-password" onclick="togglePassword('current_password', this)"></i>
        </div>
        
        <label for="new_password">New Password:</label>
        <div class="password-container">
          <input type="password" id="new_password" name="new_password" required>
          <i class="fas fa-eye toggle-password" onclick="togglePassword('new_password', this)"></i>
        </div>
        
        <label for="confirm_password">Confirm New Password:</label>
        <div class="password-container">
          <input type="password" id="confirm_password" name="confirm_password" required>
          <i class="fas fa-eye toggle-password" onclick="togglePassword('confirm_password', this)"></i>
        </div>

        <input type="submit" name="change_password" value="Change Password">
      </form>
    </div>
  </div>

  <!-- IMAGE PREVIEW MODAL -->
  <div id="imagePreviewModal">
    <span class="modal-close" id="imagePreviewClose">&times;</span>
    <img class="modal-image" id="imageModalContent" alt="Full Profile Preview">
  </div>

  <!-- Notification dropdown toggle script -->
  <script>
    // Removed notification code as per instructions.
  </script>

  <script>
   function togglePassword(inputId, iconElement) {
    const inputField = document.getElementById(inputId);
    if (inputField.type === "password") {
      inputField.type = "text";
      iconElement.classList.remove("fa-eye");
      iconElement.classList.add("fa-eye-slash");
    } else {
      inputField.type = "password";
      iconElement.classList.remove("fa-eye-slash");
      iconElement.classList.add("fa-eye");
    }
  }

    // -------------------- EDIT PROFILE MODAL --------------------
    const editModal = document.getElementById('editModal');
    const openEditModal = document.getElementById('openEditModal');
    const closeEditModal = document.getElementById('closeEditModal');

    openEditModal.addEventListener('click', () => {
      editModal.style.display = 'block';
    });
    closeEditModal.addEventListener('click', () => {
      editModal.style.display = 'none';
    });
    window.addEventListener('click', (e) => {
      if (e.target === editModal) {
        editModal.style.display = 'none';
      }
    });

    // -------------------- CHANGE PASSWORD MODAL --------------------
    const changePasswordModal = document.getElementById('changePasswordModal');
    const openChangePasswordModal = document.getElementById('openChangePasswordModal');
    const closeChangePasswordModal = document.getElementById('closeChangePasswordModal');

    openChangePasswordModal.addEventListener('click', () => {
      changePasswordModal.style.display = 'block';
    });
    closeChangePasswordModal.addEventListener('click', () => {
      changePasswordModal.style.display = 'none';
    });
    window.addEventListener('click', (e) => {
      if (e.target === changePasswordModal) {
        changePasswordModal.style.display = 'none';
      }
    });

    // -------------------- IMAGE PREVIEW MODAL --------------------
    const profileImage = document.querySelector('.avatar');
    const imagePreviewModal = document.getElementById('imagePreviewModal');
    const imageModalContent = document.getElementById('imageModalContent');
    const closeImagePreview = document.getElementById('imagePreviewClose');

    profileImage.addEventListener('click', () => {
      const imgSrc = profileImage.getAttribute('src');
      imageModalContent.setAttribute('src', imgSrc);
      imagePreviewModal.style.display = 'block';
    });
    closeImagePreview.addEventListener('click', () => {
      imagePreviewModal.style.display = 'none';
    });
    window.addEventListener('click', (e) => {
      if (e.target === imagePreviewModal) {
        imagePreviewModal.style.display = 'none';
      }
    });
  </script>
</body>
</html>
