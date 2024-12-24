document.addEventListener("DOMContentLoaded", function () {
    const dropdownToggle = document.getElementById("active-lang");
    const dropdownMenu = document.querySelector(".dropdown-menu-2");
    const langLinks = document.querySelectorAll('.dropdown-menu-2 a');
    
    // Load and apply stored language
    const lang = localStorage.getItem('lang') || 'en';
    loadTranslations(lang);
    setActiveLanguage(lang);

    // Toggle dropdown visibility
    dropdownToggle.addEventListener("click", function () {
        dropdownMenu.classList.toggle("show");
        // Update aria-expanded dynamically based on dropdown visibility
        dropdownToggle.setAttribute("aria-expanded", dropdownMenu.classList.contains("show"));
    });

    // Handle language selection
    langLinks.forEach(link => {
        link.addEventListener('click', function () {
            const selectedLang = this.getAttribute('data-lang');
            localStorage.setItem('lang', selectedLang);
            setActiveLanguage(selectedLang);
            loadTranslations(selectedLang);
            dropdownMenu.classList.remove("show");
            dropdownToggle.setAttribute("aria-expanded", "false"); // Close the dropdown
        });
    });

    // Close dropdown if clicked outside
    document.addEventListener('click', function (event) {
        if (!dropdownToggle.contains(event.target) && !dropdownMenu.contains(event.target)) {
            dropdownMenu.classList.remove("show");
            dropdownToggle.setAttribute("aria-expanded", "false");
        }
    });

    function setActiveLanguage(lang) {
        // Update dropdown button text and image
        const selectedLink = Array.from(langLinks).find(link => link.getAttribute('data-lang') === lang);
        if (selectedLink) {
            dropdownToggle.innerHTML = selectedLink.innerHTML; // Set button content to the selected language
        }

        // Set active class on the selected language
        langLinks.forEach(link => link.classList.remove('active-language'));
        if (selectedLink) {
            selectedLink.classList.add('active-language');
        }
    }

    function loadTranslations(lang) {
        fetch(`/translations/${lang}.json`)
            .then(response => response.json())
            .then(translations => {
                document.querySelectorAll('[data-translate]').forEach(element => {
                    const key = element.getAttribute('data-translate');
                    element.textContent = translations[key] || key;
                });
            })
            .catch(error => console.error('Error loading translations:', error));
    }
});
