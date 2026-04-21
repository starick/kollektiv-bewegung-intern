import { ref } from 'vue';

const isDark = ref(false);

if (typeof window !== 'undefined') {
  const stored = localStorage.getItem('theme');
  isDark.value = stored ? stored === 'dark' : window.matchMedia('(prefers-color-scheme: dark)').matches;
  document.documentElement.classList.toggle('dark', isDark.value);
}

export function useDarkMode() {
  const toggle = () => {
    isDark.value = !isDark.value;
    document.documentElement.classList.toggle('dark', isDark.value);
    localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
  };

  return { isDark, toggle };
}
