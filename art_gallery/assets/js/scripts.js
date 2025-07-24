// Navbar scroll effect
window.addEventListener("scroll", () => {
  const navbar = document.getElementById("navbar");
  const navTitle = document.getElementById("navTitle");
  const navButtons = document.getElementById("navButtons");

  if (window.scrollY > 100) {
    navbar.classList.add("visible");
    navTitle.classList.remove("hidden");
    navButtons.classList.remove("hidden");
  } else {
    navbar.classList.remove("visible");
    navTitle.classList.add("hidden");
    navButtons.classList.add("hidden");
  }
});

// Toggle login dropdown menu
document.addEventListener("DOMContentLoaded", () => {
  const loginToggle = document.getElementById("loginToggle");
  const loginDropdown = document.getElementById("loginDropdown");

  if (loginToggle && loginDropdown) {
    loginToggle.addEventListener("click", () => {
      loginDropdown.classList.toggle("show");
    });

    window.addEventListener("click", (e) => {
      if (!loginToggle.contains(e.target) && !loginDropdown.contains(e.target)) {
        loginDropdown.classList.remove("show");
      }
    });
  }
});

// Optional: Open login modal if Buy button is clicked while not logged in
document.querySelectorAll(".buy-btn").forEach((button) => {
  button.addEventListener("click", (e) => {
    const isLoggedIn = false; // Replace this with your session check if needed
    if (!isLoggedIn) {
      e.preventDefault();
      document.getElementById("loginModal").classList.add("visible");
    }
  });
});
