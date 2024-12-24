// navbar.js
document.addEventListener('DOMContentLoaded', function () {
  // Cache frequently accessed DOM elements
  const elements = {
    notificationButton: document.getElementById("notification-button"),
    messageBox: document.getElementById("message-box"),
    darkModeToggle: document.querySelector("#dark-mode-toggle"),
    html: document.documentElement,
    darkModeIcon: document.getElementById("dark-mode-icon"),
    chatButton: document.getElementById("chat-button"),
    chatElement: document.getElementById("chat"),
    menuButton: document.getElementById("menu-button"),
    menuDropdown: document.getElementById("menu-dropdown"),
    shareButton: document.getElementById("share-button"),
    socialIcons: document.getElementById("social-icons"),
    submitButton: document.getElementById("submit-btn"),
    emailOrPhoneInput: document.getElementById("email_or_phone"),
    loginModal: document.getElementById('loginModal'),
    avatarImg: document.getElementById("avatar-img")
  };

  // Check if required elements exist before adding event listeners
  if (elements.notificationButton) {
    elements.notificationButton.addEventListener("click", toggleNotificationBox);
  }

  if (elements.darkModeToggle) {
    elements.darkModeToggle.addEventListener("change", toggleDarkMode);
  }

  if (elements.chatButton) {
    elements.chatButton.addEventListener("click", toggleChat);
  }

  if (elements.menuButton) {
    elements.menuButton.addEventListener("click", toggleMenu);
  }

  if (elements.shareButton) {
    elements.shareButton.addEventListener("click", toggleSocialIcons);
  }

  if (elements.emailOrPhoneInput) {
    elements.emailOrPhoneInput.addEventListener("input", validateInput);
  }

  // Change avatar dynamically
  if (elements.avatarImg && window.myAvatarData) {
    const avatarUrl = myAvatarData.avatarUrl; // Ensure myAvatarData is available in the global scope
    elements.avatarImg.src = avatarUrl;
  }

  function toggleNotificationBox(e) {
    e.preventDefault();
    if (elements.messageBox) {
      elements.messageBox.style.display = (elements.messageBox.style.display === "block") ? "none" : "block";
    }
    document.addEventListener("click", function (e) {
      if (elements.messageBox && !e.target.closest(".notification-box")) {
        elements.messageBox.style.display = "none";
        document.removeEventListener("click", arguments.callee);
      }
    });
  }

  // Dark mode toggle
  const sunIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#000000" viewBox="0 0 256 256"><path d="M120,40V16a8,8,0,0,1,16,0V40a8,8,0,0,1-16,0Zm72,88a64,64,0,1,1-64-64A64.07,64.07,0,0,1,192,128Zm-16,0a48,48,0,1,0-48,48A48.05,48.05,0,0,0,176,128ZM58.34,69.66A8,8,0,0,0,69.66,58.34l-16-16A8,8,0,0,0,42.34,53.66Zm0,116.68-16,16a8,8,0,0,0,11.32,11.32l16-16a8,8,0,0,0-11.32-11.32ZM192,72a8,8,0,0,0,5.66-2.34l16-16a8,8,0,0,0-11.32-11.32l-16,16A8,8,0,0,0,192,72Zm5.66,114.34a8,8,0,0,0-11.32,11.32l16,16a8,8,0,0,0,11.32-11.32ZM48,128a8,8,0,0,0-8-8H16a8,8,0,0,0,0,16H40A8,8,0,0,0,48,128Zm80,80a8,8,0,0,0-8,8v24a8,8,0,0,0,16,0V216A8,8,0,0,0,128,208Zm112-88H216a8,8,0,0,0,0,16h24a8,8,0,0,0,0-16Z"></path></svg>`;
  const moonIcon = `<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="#000000" viewBox="0 0 256 256"><path d="M155.64,199.28a80,80,0,0,1,0-142.56,8,8,0,0,0,0-14.25A94.93,94.93,0,0,0,112,32a96,96,0,0,0,0,192,94.93,94.93,0,0,0,43.64-10.47,8,8,0,0,0,0-14.25ZM112,208A80,80,0,1,1,134.4,51.16a96.08,96.08,0,0,0,0,153.68A79.82,79.82,0,0,1,112,208Zm139.17-87.35-26.5-11.43-2.31-29.84a8,8,0,0,0-14.14-4.47L189.63,97.42l-27.71-6.85a8,8,0,0,0-8.81,11.82L168.18,128l-15.07,25.61a8,8,0,0,0,8.81,11.82l27.71-6.85,18.59,22.51a8,8,0,0,0,14.14-4.47l2.31-29.84,26.5-11.43a8,8,0,0,0,0-14.7ZM213.89,134a8,8,0,0,0-4.8,6.73l-1.15,14.89-9.18-11.11a8,8,0,0,0-6.17-2.91,8.4,8.4,0,0,0-1.92.23l-14.12,3.5,7.81-13.27a8,8,0,0,0,0-8.12l-7.81-13.27,14.12,3.5a8,8,0,0,0,8.09-2.68l9.18-11.11,1.15,14.89a8,8,0,0,0,4.8,6.73l13.92,6Z"></path></svg>`;

  function toggleDarkMode(e) {
    const isDarkMode = e.target.checked;
    elements.html.setAttribute("data-theme", isDarkMode ? "dark" : "light");
    if (elements.darkModeIcon) {
      elements.darkModeIcon.innerHTML = isDarkMode ? moonIcon : sunIcon;
    }
  }
  function toggleChat() {
    if (elements.chatElement) {
      elements.chatElement.style.display = (elements.chatElement.style.display === "none") ? "block" : "none";
    }
  }
  // Toggle menu visibility
  function toggleMenu(e) {
    e.preventDefault();
    if (elements.menuDropdown) {
      elements.menuDropdown.classList.toggle("active");
    }
  }
if (elements.socialIcons) elements.socialIcons.style.display = "none"; 
function toggleSocialIcons(e) {
    if (elements.socialIcons) {
        elements.socialIcons.style.display = (elements.socialIcons.style.display === "none") ? "flex" : "none";
        if (elements.socialIcons.style.display === "flex") {
            const hideIcons = (event) => {
                if (!elements.socialIcons.contains(event.target) && !elements.shareButton.contains(event.target)) {
                    elements.socialIcons.style.display = "none";
                    document.removeEventListener("click", hideIcons);
                }
            };
            document.addEventListener("click", hideIcons);
        }
    }
}
  function validateInput() {
    const emailOrPhone = elements.emailOrPhoneInput.value;
    elements.submitButton.disabled = !(emailOrPhone && (emailOrPhone.includes("@") || /^\d+$/.test(emailOrPhone)));
  }
});
