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

// --- Delete Confirmation for User Deactivation ---
function deactivateUser(userId) {
  if (confirm("Are you sure you want to deactivate this user?")) {
    window.location.href = "user_management.php?deactivate_user=" + userId;
  }
}
