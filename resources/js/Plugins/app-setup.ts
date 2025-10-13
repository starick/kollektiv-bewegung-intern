import { App } from 'vue';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Dropdown from 'primevue/dropdown';
import Toast from 'primevue/toast';
import {
  Button,
  Calendar,
  Column,
  DataTable,
  FileUpload,
  InputNumber,
  Row,
  Tab,
  TabList,
  TabPanel,
  TabPanels,
  Tabs
} from 'primevue';

export function addComponents(app: App): App {
  app.component('Button', Button);
  app.component('Column', Column);
  app.component('DataTable', DataTable);
  app.component('Dropdown', Dropdown);
  app.component('FileUpload', FileUpload);
  app.component('InputNumber', InputNumber);
  app.component('InputText', InputText);
  app.component('Row', Row);
  app.component('Tab', Tab);
  app.component('TabList', TabList);
  app.component('TabPanel', TabPanel);
  app.component('TabPanels', TabPanels);
  app.component('Tabs', Tabs);
  app.component('Textarea', Textarea);
  app.component('Toast', Toast);
  app.component('Calendar', Calendar);

  return app;
}
