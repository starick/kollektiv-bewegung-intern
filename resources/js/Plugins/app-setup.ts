import { App } from 'vue';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Dropdown from 'primevue/dropdown';
import Toast from 'primevue/toast';
import { Column, FileUpload, InputNumber, Row } from 'primevue';

export function addComponents(app: App): App {
  app.component('InputText', InputText);
  app.component('InputNumber', InputNumber);
  app.component('Textarea', Textarea);
  app.component('Dropdown', Dropdown);
  app.component('Toast', Toast);
  app.component('Row', Row);
  app.component('Column', Column);
  app.component('FileUpload', FileUpload);

  return app;
}
