import { App } from 'vue';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Dropdown from 'primevue/dropdown';
import Toast from 'primevue/toast';
export function addComponents(app: App): App {
  app.component('InputText', InputText);
  app.component('Textarea', Textarea);
  app.component('Dropdown', Dropdown);
  app.component('Toast', Toast);

  return app;
}
