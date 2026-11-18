import './bootstrap';

import * as bootstrap from 'bootstrap';

window.bootstrap = bootstrap;

/*
 * Preferensi tema (PROPOSED, REC-10): cookie + atribut data-theme.
 * Tidak menyimpan data aplikasi dan tidak memengaruhi otorisasi.
 */
const themeStorageKey = 'theme';

function currentTheme() {
    return document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
}

function syncThemeToggles(theme) {
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        const isDark = theme === 'dark';
        const icon = button.querySelector('[data-theme-icon]');

        button.setAttribute('aria-pressed', isDark ? 'true' : 'false');
        button.setAttribute('aria-label', isDark ? 'Aktifkan mode terang' : 'Aktifkan mode gelap');
        button.setAttribute('title', isDark ? 'Mode terang' : 'Mode gelap');

        if (icon) {
            icon.classList.toggle('bi-moon-stars', !isDark);
            icon.classList.toggle('bi-sun', isDark);
        }
    });
}

function setTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    document.cookie = `${themeStorageKey}=${theme}; path=/; max-age=31536000; samesite=lax`;
    syncThemeToggles(theme);
}

document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
    button.addEventListener('click', () => {
        setTheme(currentTheme() === 'dark' ? 'light' : 'dark');
    });
});

syncThemeToggles(currentTheme());
