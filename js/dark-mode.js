// DOM Elemanı
const darkModeToggle = document.getElementById('dark-mode-toggle');

// Koyu modu kontrol et
function isDarkMode() {
  return localStorage.getItem('fit40_dark_mode') === 'true' || 
         (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches);
}

// Koyu modu ayarla
function setDarkMode(isDark) {
  if (isDark) {
    document.documentElement.setAttribute('data-theme', 'dark');
    localStorage.setItem('fit40_dark_mode', 'true');
  } else {
    document.documentElement.removeAttribute('data-theme');
    localStorage.setItem('fit40_dark_mode', 'false');
  }
}

// Toggle butonu
darkModeToggle.addEventListener('click', (e) => {
  e.preventDefault();
  setDarkMode(!isDarkMode());
});

// Sayfa yüklendiğinde
document.addEventListener('DOMContentLoaded', () => {
  setDarkMode(isDarkMode());
});