import { onMounted, ref } from 'vue';

export const THEME_STORAGE_KEY = 'hire-wire-theme';

export function useTheme() {
    const isDark = ref(false);

    onMounted(() => {
        isDark.value = document.documentElement.classList.contains('dark');
    });

    function setDark(dark) {
        document.documentElement.classList.toggle('dark', dark);
        localStorage.setItem(THEME_STORAGE_KEY, dark ? 'dark' : 'light');
        isDark.value = dark;
    }

    function toggle() {
        setDark(!document.documentElement.classList.contains('dark'));
    }

    return { isDark, setDark, toggle };
}
