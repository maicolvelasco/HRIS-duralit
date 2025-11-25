import '../css/app.css';
import './bootstrap';

/* ----------  MODO OSCURO  ---------- */
(() => {
  const key = 'dark-mode';
  const darkClass = 'dark';

  // Restaurar lo que el usuario eligió (o preferencia del SO)
  const saved = localStorage.getItem(key);
  const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
  const isDark = saved ? JSON.parse(saved) : prefersDark;

  if (isDark) {
    document.documentElement.classList.add(darkClass);
  } else {
    document.documentElement.classList.remove(darkClass);
  }

  // Escuchar cambios en otras pestañas
  window.addEventListener('storage', () => {
    const val = localStorage.getItem(key);
    if (val !== null) {
      document.documentElement.classList.toggle(darkClass, JSON.parse(val));
    }
  });
})();
/* ----------------------------------- */

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
