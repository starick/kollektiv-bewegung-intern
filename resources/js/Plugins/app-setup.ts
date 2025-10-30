import { App } from 'vue';
import InputText from 'primevue/inputtext';
import Textarea from 'primevue/textarea';
import Dropdown from 'primevue/dropdown';
import Toast from 'primevue/toast';
import {
  Accordion,
  AccordionContent,
  AccordionHeader,
  AccordionPanel,
  AccordionTab,
  Button,
  Calendar,
  ColorPicker,
  Column,
  DataTable,
  FileUpload,
  InputNumber,
  ProgressSpinner,
  Row,
  Select,
  Slider,
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
  app.component('Slider', Slider);
  app.component('ColorPicker', ColorPicker);
  app.component('Row', Row);
  app.component('Tab', Tab);
  app.component('TabList', TabList);
  app.component('TabPanel', TabPanel);
  app.component('TabPanels', TabPanels);
  app.component('Tabs', Tabs);
  app.component('Textarea', Textarea);
  app.component('Toast', Toast);
  app.component('Calendar', Calendar);
  app.component('Select', Select);
  app.component('Accordion', Accordion);
  app.component('AccordionPanel', AccordionPanel);
  app.component('AccordionHeader', AccordionHeader);
  app.component('AccordionContent', AccordionContent);
  app.component('ProgressSpinner', ProgressSpinner);

  // import Dropdown from 'primevue/dropdown';
  // import InputText from 'primevue/inputtext';
  // import InputNumber from 'primevue/inputnumber';
  // import Slider from 'primevue/slider';
  // import ColorPicker from 'primevue/colorpicker';
  return app;
}
