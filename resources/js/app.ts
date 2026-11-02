import './bootstrap';
import '../css/app.css';
import { createApp, DefineComponent, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import PrimeVue from 'primevue/config';
import ToastService from 'primevue/toastservice';
import { ZiggyVue } from 'ziggy-js';
import Aura from '@primeuix/themes/aura';
import '../css/styles.scss';
import { addComponents } from '@/Plugins/app-setup';
import { ConfirmationService } from 'primevue';
import { TranslationPlugin } from '@/Composables/use-translations';
import { primeVueLocale } from '@/Constants/primevue-locales';

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
        preset: Aura,
        options: {
          darkModeSelector: '.dark'
        }
      },
      options: {
        cssLayer: {
          name: 'primevue',
          order: 'tailwind-base, primevue, tailwind-utilities'
        }
      },
      // Switching the locale reloads the page, so the initial locale is sufficient
      locale: primeVueLocale(props.initialPage.props.locale as string)
    });
    inertiaApp.use(TranslationPlugin);
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
