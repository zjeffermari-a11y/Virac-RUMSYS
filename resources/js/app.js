import "./bootstrap";
import Chart from "chart.js/auto";

// Make Chart available globally for Blade templates
window.Chart = Chart;

document.addEventListener("DOMContentLoaded", function () {
    // For the login page (single password field)
    const togglePassword = document.querySelector("#togglePassword");
    const password = document.querySelector("#password");

    if (togglePassword) {
        togglePassword.addEventListener("click", function (e) {
            // toggle the type attribute
            const type =
                password.getAttribute("type") === "password"
                    ? "text"
                    : "password";
            password.setAttribute("type", type);
            // toggle the eye slash icon
            this.classList.toggle("fa-eye-slash");
        });
    }

    // For pages with multiple password fields (like change password)
    const togglePasswordIcons = document.querySelectorAll(
        ".toggle-password-icon"
    );

    togglePasswordIcons.forEach((icon) => {
        icon.addEventListener("click", function () {
            const passwordInput =
                this.closest(".relative").querySelector("input");
            const type =
                passwordInput.getAttribute("type") === "password"
                    ? "text"
                    : "password";
            passwordInput.setAttribute("type", type);
            this.classList.toggle("fa-eye");
            this.classList.toggle("fa-eye-slash");
        });
    });

    const sidebar = document.getElementById("sidebar");
    const hamburger = document.getElementById("hamburgerButton");
    const mainContent = document.querySelector(".main-content");

    hamburger?.addEventListener("click", (e) => {
        e.stopPropagation(); // Prevent the main content click listener from firing
        sidebar?.classList.toggle("-translate-x-full");
    });

    // Close sidebar when clicking on the main content area
    mainContent?.addEventListener("click", () => {
        if (sidebar && !sidebar.classList.contains("-translate-x-full")) {
            sidebar.classList.add("-translate-x-full");
        }
    });
});
