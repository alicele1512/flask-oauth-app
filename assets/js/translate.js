document.addEventListener("DOMContentLoaded", function() {
    const lang = localStorage.getItem('lang') || 'en';
    loadTranslations(lang);

    document.querySelectorAll('.language-switcher button').forEach(button => {
        button.addEventListener('click', function() {
            const selectedLang = this.getAttribute('data-lang');
            localStorage.setItem('lang', selectedLang);
            loadTranslations(selectedLang);
        });
    });
});

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

function translateText(text, targetLang) {
    return fetch('https://libretranslate.de/translate', {
        method: 'POST',
        body: JSON.stringify({
            q: text,
            source: 'en',
            target: targetLang,
            format: 'text'
        }),
        headers: { 'Content-Type': 'application/json' }
    })
    .then(response => response.json())
    .then(data => data.translatedText)
    .catch(error => {
        console.error('Error translating text:', error);
        return text;
    });
}
