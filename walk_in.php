<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Walk-In Borrow / Return - Library System</title>
  <link rel="stylesheet" href="css/walk_in.css">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600&display=swap" rel="stylesheet" />
  <style>
    input[readonly] {
      background-color: #f7f7f7;
      color: #444;
    }
    .um-message {
      color: green;
      font-weight: 500;
      margin-bottom: 10px;
    }
    nav a {
      text-decoration: none;
      font-weight: bold;
      color:rgb(255, 255, 255);
    }
  </style>
</head>
<body>
  <nav><a href="user_management.php">Back</a></nav>

  <div class="section-container">
    <h2>Walk-In Borrow / Return</h2>

    <?php 
      if (!empty($_GET['borrow_msg'])) echo '<p class="um-message">' . htmlspecialchars($_GET['borrow_msg']) . '</p>';
      if (!empty($_GET['return_msg'])) echo '<p class="um-message">' . htmlspecialchars($_GET['return_msg']) . '</p>';
    ?>

    <div class="form-container">
      <!-- Walk-In Borrow -->
      <div class="form-card">
        <h3>Walk-In Borrow</h3>
        <form method="post" action="backend/walk_in_process.php">
          <label>Library ID:</label>
          <input type="text" name="walk_in_user_library" required>

          <label>First Name:</label>
          <input type="text" name="walk_in_firstname" required readonly>

          <label>Last Name:</label>
          <input type="text" name="walk_in_lastname" required readonly>

          <label>Book Accession No:</label>
          <input type="text" name="walk_in_book_accession" required>

          <label>Book Title:</label>
          <input type="text" name="walk_in_book_title" required readonly>

          <label>Borrow Date:</label>
          <input type="date" name="walk_in_borrow_date" value="<?= date('Y-m-d') ?>" required>

          <label>Due Date:</label>
          <input type="date" name="walk_in_due_date" required>

          <input type="submit" name="walk_in_borrow" value="Record Borrow">
        </form>
      </div>

      <!-- Walk-In Return -->
      <div class="form-card">
        <h3>Walk-In Return</h3>
        <form method="post" action="backend/walk_in_process.php">
          <label>Library ID:</label>
          <input type="text" name="walk_in_user_library_return" required>

          <label>First Name:</label>
          <input type="text" name="walk_in_firstname_return" required readonly>

          <label>Last Name:</label>
          <input type="text" name="walk_in_lastname_return" required readonly>

          <label>Book Accession No:</label>
          <input type="text" name="walk_in_book_accession_return" required>

          <label>Book Title:</label>
          <input type="text" name="walk_in_book_title_return" required readonly>

          <label>Return Date:</label>
          <input type="date" name="walk_in_borrow_date_return" value="<?= date('Y-m-d') ?>" required>

          <input type="submit" name="walk_in_return" value="Record Return">
        </form>
      </div>
    </div>
  </div>

  <script>
    function debounce(func, delay) {
      let timeout;
      return function (...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), delay);
      };
    }

    const fetchUser = debounce(function () {
      const libId = document.querySelector('[name="walk_in_user_library"]').value;
      if (libId.trim() !== "") {
        fetch('backend/fetch_user_data.php?library_id=' + encodeURIComponent(libId))
          .then(res => res.json())
          .then(data => {
            if (data.firstname && data.lastname) {
              document.querySelector('[name="walk_in_firstname"]').value = data.firstname;
              document.querySelector('[name="walk_in_lastname"]').value = data.lastname;
            }
          })
          .catch(err => console.error('User fetch error:', err));
      }
    }, 500);

    const fetchBook = debounce(function () {
      const accessionNo = document.querySelector('[name="walk_in_book_accession"]').value;
      if (accessionNo.trim() !== "") {
        fetch('backend/fetch_book_data.php?accession_no=' + encodeURIComponent(accessionNo))
          .then(res => res.json())
          .then(data => {
            if (data.title) {
              document.querySelector('[name="walk_in_book_title"]').value = data.title;
            }
          })
          .catch(err => console.error('Book fetch error:', err));
      }
    }, 500);

    const fetchReturnUser = debounce(function () {
      const libId = document.querySelector('[name="walk_in_user_library_return"]').value;
      if (libId.trim() !== "") {
        fetch('backend/fetch_user_data.php?library_id=' + encodeURIComponent(libId))
          .then(res => res.json())
          .then(data => {
            if (data.firstname && data.lastname) {
              document.querySelector('[name="walk_in_firstname_return"]').value = data.firstname;
              document.querySelector('[name="walk_in_lastname_return"]').value = data.lastname;
            }
          })
          .catch(err => console.error('Return User fetch error:', err));
      }
    }, 500);

    const fetchReturnBook = debounce(function () {
      const accessionNo = document.querySelector('[name="walk_in_book_accession_return"]').value;
      if (accessionNo.trim() !== "") {
        fetch('backend/fetch_book_data.php?accession_no=' + encodeURIComponent(accessionNo))
          .then(res => res.json())
          .then(data => {
            if (data.title) {
              document.querySelector('[name="walk_in_book_title_return"]').value = data.title;
            }
          })
          .catch(err => console.error('Return Book fetch error:', err));
      }
    }, 500);

    // Attach all event listeners
    document.querySelector('[name="walk_in_user_library"]').addEventListener('input', fetchUser);
    document.querySelector('[name="walk_in_book_accession"]').addEventListener('input', fetchBook);
    document.querySelector('[name="walk_in_user_library_return"]').addEventListener('input', fetchReturnUser);
    document.querySelector('[name="walk_in_book_accession_return"]').addEventListener('input', fetchReturnBook);
  </script>

</body>
</html>
