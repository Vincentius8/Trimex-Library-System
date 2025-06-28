<?php
require 'backend/student_dashboard_process.php';

/* ------------------------------------------------------------------ */
/* fallback definitions                                               */
/* ------------------------------------------------------------------ */
if (!isset($role))            $role = "Student";
if (!isset($firstName))       $firstName = isset($fullName) ? explode(" ", $fullName)[0] : "";
if (!isset($notifications) || !is_array($notifications)) $notifications = [];
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Student Dashboard</title>
  <link rel="icon" type="image/x-icon" href="img/LIBRARY LOGO.png">

  <!-- Google‑fonts + Font‑awesome -->
  <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" crossorigin="anonymous"/>
  <!-- CSS -->
  <link rel="stylesheet" href="css/sd.css">
</head>
<body>

<!-- ────────── NAVBAR ────────── -->
<header>
  <div class="header-left">
    <div class="logo-area">
      <img class="trimex-logo"  src="img/Trimexlogo1.png"  alt="Trimex Logo">
      <img class="library-logo" src="img/LIBRARY LOGO.png" alt="Library Logo">
    </div>
  </div>

  <div id="greetingMessage" class="greeting-message">
    Welcome, <?php echo htmlspecialchars($role).' '.htmlspecialchars($firstName); ?>.
  </div>

  <div class="header-right">
    <!-- profile -->
    <div class="profile-menu" id="profileMenu">
      <img class="profile-avatar" src="<?php echo htmlspecialchars($profilePic); ?>" alt="User Profile">
      <div class="profile-dropdown" id="profileDropdown">
        <a href="user_profile.php">Profile</a>
        <a href="backend/student_logout.php" class="logout-btn">Logout</a>
      </div>
    </div>

    <!-- notifications -->
    <div class="notification-area">
      <button class="notification-btn" id="notificationToggle"><i class="fas fa-bell"></i>
        <?php if (count($notifications)): ?><span id="notificationCount" class="badge"><?php echo count($notifications); ?></span><?php endif; ?>
      </button>

      <div id="notificationDropdown" class="notification-dropdown">
        <?php if (empty($notifications)): ?>
          <p style="padding:10px;">No notifications.</p>
        <?php else: foreach ($notifications as $n): ?>
          <div class="notification-item">
            <p><?php echo htmlspecialchars($n['message']); ?></p>
            <small><?php echo htmlspecialchars($n['created_at']); ?></small>
          </div>
        <?php endforeach; endif; ?>

        <form action="backend/clear_notifications.php" method="post" style="padding:10px;text-align:center;">
          <button id="clearNotifs" class="clear-notifs" type="submit">Clear notifications</button>
        </form>
      </div>
    </div>
  </div>
</header>

<!-- ────────── SEARCH BAR ────────── -->
<div class="search-container">
  <form id="searchForm" method="get" action="student_dashboard.php">
    <input id="searchInput" type="text" name="search" placeholder="Search Books" value="<?php echo htmlspecialchars($searchTerm); ?>">
    <button class="search-btn" type="submit"><i class="fas fa-search"></i><span class="btn-label">Search</span></button>
    <button id="refreshBtn" type="button"><i class="fas fa-sync-alt"></i></button>
  </form>
</div>

<!-- ────────── MAIN CONTENT ────────── -->
<main>
<?php if (empty($groupedBooks)): ?>
  <h2>No books found.</h2>
<?php else: foreach ($groupedBooks as $courseName => $booksArray): ?>
  <h2><?php echo htmlspecialchars($courseName); ?></h2>
  <div class="course-wrapper">
    <button class="scroll-btn left">&lt;</button>

    <div class="course-container">
      <?php foreach ($booksArray as $book): 
            /* a card is disabled only if someone else already borrowed it */
            $disabled = ($book['status']==='borrowed' && empty($book['user_status']));
      ?>
        <div class="course-card<?php echo $disabled?' disabled':'';?>"
             data-cover="<?php echo htmlspecialchars($book['book_image'] ?: 'img/book_cover.jpg'); ?>"
             data-title="<?php echo htmlspecialchars($book['title']); ?>"
             data-author="<?php echo htmlspecialchars($book['author']); ?>"
             data-accession="<?php echo htmlspecialchars($book['accession_no']); ?>"
             data-call="<?php echo htmlspecialchars($book['call_no']); ?>"
             data-copies="<?php echo htmlspecialchars($book['copies']); ?>"
             data-publisher="<?php echo htmlspecialchars($book['publisher']); ?>"
             data-publish-year="<?php echo htmlspecialchars($book['copyright']); ?>"
             data-course="<?php echo htmlspecialchars($book['course']); ?>"
             data-bookid="<?php echo htmlspecialchars($book['book_id']); ?>"
             data-mystatus="<?php echo htmlspecialchars($book['user_status']); ?>">
          <img src="<?php echo $book['book_image'] ?: 'img/book_cover.jpg'; ?>" alt="">
          <p><?php echo htmlspecialchars($book['title']); ?></p>
          <small><?php echo htmlspecialchars($book['author']); ?></small>
        </div>
      <?php endforeach; ?>
    </div>

    <button class="scroll-btn right">&gt;</button>
  </div>
<?php endforeach; endif; ?>
</main>

<!-- ────────── BOOK MODAL ────────── -->
<div id="bookModal" class="modal">
  <div class="modal-content">
    <span class="modal-close">&times;</span>
    <div class="modal-body">
      <img id="modalBookCover" src="img/book_cover.jpg" alt="">
      <h3 id="modalBookTitle"></h3>
      <p id="modalBookAuthor"></p>
      <p id="modalBookAccession"></p>
      <p id="modalBookCall"></p>
      <p id="modalBookCopies"></p>
      <p id="modalBookPublisher"></p>
      <p id="modalBookPublishYear"></p>
      <p id="modalBookCourse"></p>

      <div class="modal-actions">
        <!-- reserve -->
        <div id="reserveActions">
          <form id="reserveForm" method="post" action="backend/reserve_book.php">
            <input type="hidden" name="book_id" id="modalBookId">
            <input class="action-button reserve" type="submit" value="Reserve">
          </form>
        </div>
        <!-- cancel -->
        <div id="cancelActions" style="display:none;">
          <form id="cancelForm" method="post" action="backend/cancel_reservation.php">
            <input type="hidden" name="book_id" id="cancelBookId">
            <input class="action-button" type="submit" value="Cancel Reservation">
          </form>
        </div>
        <!-- return -->
        <div id="returnActions" style="display:none;">
          <form id="returnForm" method="post" action="backend/return_request.php">
            <input type="hidden" name="book_id" id="returnBookId">
            <input class="action-button" type="submit" value="Return">
          </form>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- ────────── FOOTER ────────── -->
<footer>
  <div class="footer-content">
    <p>&copy; <?php echo date('Y'); ?> Trimex Library. All rights reserved.</p>
    <nav><a href="#">Contact&nbsp;Us</a> | <a href="#">Privacy&nbsp;Policy</a></nav>
  </div>
</footer>

<!-- ────────── JS ────────── -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script>
/* refresh (always show full list) */
document.getElementById('refreshBtn').onclick = () =>{
  document.querySelector('#refreshBtn i').classList.add('spin');
  setTimeout(()=>location.href='student_dashboard.php',200);
};

/* notification dropdown */
const bell = document.getElementById('notificationToggle'),
      drop = document.getElementById('notificationDropdown');
bell.addEventListener('click',e=>{
  e.stopPropagation();
  drop.style.display = drop.style.display==='block'?'none':'block';
});
window.addEventListener('click',()=>drop.style.display='none');

/* profile dropdown */
document.getElementById('profileMenu').onclick = e=>{
  e.stopPropagation(); e.currentTarget.classList.toggle('show');
};

/* scroll arrows */
document.querySelectorAll('.course-wrapper').forEach(w=>{
  const c=w.querySelector('.course-container');
  w.querySelector('.scroll-btn.left').onclick = ()=>c.scrollBy({left:-300,behavior:'smooth'});
  w.querySelector('.scroll-btn.right').onclick= ()=>c.scrollBy({left: 300,behavior:'smooth'});
});

/* book modal */
const modal=document.getElementById('bookModal');
document.querySelectorAll('.course-card').forEach(card=>{
  card.addEventListener('click',()=>{
    if(card.classList.contains('disabled')) return;
    const d=card.dataset;
    // fill
    modal.querySelector('#modalBookCover').src           = d.cover;
    modal.querySelector('#modalBookTitle').textContent   = d.title;
    modal.querySelector('#modalBookAuthor').textContent  = 'Author: '+d.author;
    modal.querySelector('#modalBookAccession').textContent = 'Accession No: '+d.accession;
    modal.querySelector('#modalBookCall').textContent      = 'Call No: '+d.call;
    modal.querySelector('#modalBookCopies').textContent    = 'Copies Available: '+d.copies;
    modal.querySelector('#modalBookPublisher').textContent = 'Publisher: '+d.publisher;
    modal.querySelector('#modalBookPublishYear').textContent= 'Publish Year: '+d.publishYear;
    modal.querySelector('#modalBookCourse').textContent    = 'Section: '+d.course;
    modal.querySelectorAll('input[type=hidden]').forEach(i=>i.value=d.bookid);

    // toggle actions
    const mys=d.mystatus;
    document.getElementById('reserveActions').style.display = (!mys ? 'block':'none');
    document.getElementById('cancelActions' ).style.display = (mys==='pending' ? 'block':'none');
    document.getElementById('returnActions' ).style.display = (mys==='approved'? 'block':'none');

    modal.style.display='block';
  });
});
modal.querySelector('.modal-close').onclick=()=>modal.style.display='none';
window.onclick=e=>{ if(e.target===modal) modal.style.display='none'; };

/* ajax clear notifications */
document.getElementById('clearNotifs').addEventListener('click',e=>{
  e.preventDefault();
  fetch('backend/clear_notifications.php',{method:'POST'})
  .then(r=>{if(!r.ok)throw'';})
  .then(()=>{
    drop.querySelectorAll('.notification-item').forEach(n=>n.remove());
    drop.insertAdjacentHTML('afterbegin','<p style="padding:10px;">No notifications.</p>');
    const b=document.getElementById('notificationCount'); if(b) b.remove();
  })
  .catch(()=>alert('Unable to clear notifications right now.'));
});
</script>
</body>
</html>
