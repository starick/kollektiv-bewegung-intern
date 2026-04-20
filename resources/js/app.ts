import './bootstrap';
import '../css/app.css';
import { createApp, DefineComponent, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import PrimeVue, { defaultOptions } from 'primevue/config';
import ToastService from 'primevue/toastservice';
import { ZiggyVue } from 'ziggy-js';
import Aura from '@primevue/themes/aura';
import '../css/styles.scss';
import { addComponents } from '@/Plugins/app-setup';
import { ConfirmationService } from 'primevue';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
  title: (title) => `${title} - ${appName}`,
  resolve: (name) =>
    resolvePageComponent(
      `./Pages/${name}.vue`,
      import.meta.glob('./Pages/**/*.vue')
    ) as Promise<DefineComponent>,
  setup({ el, App, props, plugin }) {
    const inertiaApp = createApp({ render: () => h(App, props) });

    inertiaApp.use(plugin);
    inertiaApp.use(ZiggyVue);
    inertiaApp.use(PrimeVue, {
      theme: {
        preset: Aura
      },
      options: {
        cssLayer: {
          name: 'primevue',
          order: 'tailwind-base, primevue, tailwind-utilities'
        }
      },
      locale: {
        ...defaultOptions.locale,
        firstDayOfWeek: 1
      }
    });
    inertiaApp.use(ToastService);
    addComponents(inertiaApp);
    inertiaApp.use(ConfirmationService);
    inertiaApp.mount(el);

    return inertiaApp;
  },
  progress: {
    color: '#4B5563'
  }
});
