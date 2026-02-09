function login() {
    document.getElementById('').classList.toggle('');
}

function options() {
    document.getElementById('option-menu').classList.toggle('hidden');
    document.getElementById('backdrop1').classList.toggle('backdrop0');
}

const themeSelector = document.querySelector('[data-theme-selector]');
const htmlElement = document.documentElement;
const STORAGE_KEY = 'user-theme';

function setTheme(themeName) {
  htmlElement.setAttribute('data-theme', themeName);
  localStorage.setItem(STORAGE_KEY, themeName);
}

function handleThemeChange(event) {
  setTheme(event.target.value);
}

themeSelector.addEventListener('change', handleThemeChange);

document.addEventListener('DOMContentLoaded', () => {
  const savedTheme = localStorage.getItem(STORAGE_KEY);
  if (savedTheme) {
    setTheme(savedTheme);
    themeSelector.value = savedTheme;} 
    else {

    const prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
    const initialTheme = prefersDark ? 'dark' : 'light';
    setTheme(initialTheme);
    themeSelector.value = initialTheme;
  }
});