import { ref, watch, onMounted } from 'vue';

export function useDarkMode() {
  const isDark = ref(false);

  const toggleDarkMode = () => {
    isDark.value = !isDark.value;
  };

  const updateHtmlClass = () => {
    if (isDark.value) {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }
  };

  onMounted(() => {
    const saved = localStorage.getItem('dark-mode');
    isDark.value = saved ? JSON.parse(saved) : false;
    updateHtmlClass();
  });

  watch(isDark, (val) => {
    localStorage.setItem('dark-mode', JSON.stringify(val));
    updateHtmlClass();
  });

  return { isDark, toggleDarkMode };
}