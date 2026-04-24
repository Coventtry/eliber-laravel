import { ref } from 'vue'

// Singleton — estado reactivo compartido entre componentes
const darkMode = ref(false)
let storageKey  = 'eliber-dark-mode'

function applyTheme(dark) {
    document.documentElement.setAttribute('data-theme', dark ? 'dark' : 'light')
}

/**
 * Debe llamarse una vez al montar el primer componente que conoce el userId.
 * Si userId es null (página pública) usa la clave genérica.
 */
export function initDarkMode(userId = null) {
    storageKey  = userId ? `eliber-dark-mode-${userId}` : 'eliber-dark-mode'
    const saved = localStorage.getItem(storageKey) === 'true'
    darkMode.value = saved
    applyTheme(saved)
}

export function useDarkMode() {
    function toggleDark() {
        darkMode.value = !darkMode.value
        localStorage.setItem(storageKey, darkMode.value)
        applyTheme(darkMode.value)
    }

    return { darkMode, toggleDark }
}
