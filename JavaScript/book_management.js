// --- Sidebar Open/Close Animation ---
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

if (closeBtn) {
  closeBtn.addEventListener("click", closeNav);
}

overlay.addEventListener("click", closeNav);

function closeNav() {
  sidenav.classList.remove("open");
  overlay.classList.remove("show");
  mainContent.classList.remove("pushed");
}

// --- Greeting Message Animation on Page Load ---
window.addEventListener('load', () => {
  const greeting = document.getElementById("greeting");
  setTimeout(() => {
    greeting.classList.add("animate");
  }, 300);
});

// --- Section Switching with Animation ---
const navLinks = document.querySelectorAll(".nav-link");
const sections = document.querySelectorAll(".dashboard-section");

navLinks.forEach(link => {
  link.addEventListener("click", (e) => {
    const targetId = link.getAttribute("data-target");
    if (targetId) {
      e.preventDefault();
      navLinks.forEach(l => l.classList.remove("active"));
      link.classList.add("active");
      sections.forEach(sec => sec.classList.remove("active-section"));
      setTimeout(() => {
        document.getElementById(targetId).classList.add("active-section");
      }, 100);
      closeNav();
    }
  });
});

// --- Modal Edit Functionality ---
const editModal = document.getElementById("editModal");
const modalClose = document.getElementById("modalClose");

function openEditModal(bookData) {
  document.getElementById("edit-id").value = bookData.id;
  document.getElementById("edit-accession_no").value = bookData.accession_no;
  document.getElementById("edit-call_no").value = bookData.call_no;
  document.getElementById("edit-author").value = bookData.author;
  document.getElementById("edit-title").value = bookData.title;
  document.getElementById("edit-publisher").value = bookData.publisher;
  document.getElementById("edit-copies").value = bookData.copies;
  document.getElementById("edit-copyright").value = bookData.copyright;
  document.getElementById("edit-course").value = bookData.course;
  document.getElementById("edit-availability").value = bookData.availability;
  // Set existing cover image in a hidden field
  document.getElementById("existing_cover").value = bookData.cover_image;
  editModal.style.display = "block";
}

const editButtons = document.querySelectorAll(".edit-btn");
editButtons.forEach(button => {
  button.addEventListener("click", function() {
    const bookData = {
      id: this.getAttribute("data-id"),
      accession_no: this.getAttribute("data-accession_no"),
      call_no: this.getAttribute("data-call_no"),
      author: this.getAttribute("data-author"),
      title: this.getAttribute("data-title"),
      publisher: this.getAttribute("data-publisher"),
      copies: this.getAttribute("data-copies"),
      copyright: this.getAttribute("data-copyright"),
      course: this.getAttribute("data-course"),
      availability: this.getAttribute("data-availability"),
      cover_image: this.getAttribute("data-cover_image")
    };
    openEditModal(bookData);
  });
});

modalClose.addEventListener("click", () => {
  editModal.style.display = "none";
});

window.addEventListener("click", (e) => {
  if (e.target == editModal) {
    editModal.style.display = "none";
  }
});

// --- Modal Add Functionality ---
const addModal = document.getElementById("addModal");
const addBookBtn = document.getElementById("addBookBtn");
const addModalClose = document.getElementById("addModalClose");

addBookBtn.addEventListener("click", () => {
  addModal.style.display = "block";
});

if (addModalClose) {
  addModalClose.addEventListener("click", () => {
    addModal.style.display = "none";
  });
}

window.addEventListener("click", (e) => {
  if (e.target == addModal) {
    addModal.style.display = "none";
  }
});

// --- Refresh Button ---
const refreshBtn = document.getElementById("refreshBtn");
refreshBtn.addEventListener("click", () => {
  window.location.href = "book_management.php";
});

// --- Delete Confirmation ---
function confirmDelete(id) {
  if (confirm("Delete this book?")) {
    window.location.href = "book_management.php?delete=" + id;
  }
}
