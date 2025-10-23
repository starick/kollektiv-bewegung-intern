<script setup lang="ts">
import { TimeTable } from '@/Types/time-table';
import TimeTableDisplay from '@/Components/TimeTables/TimeTableDisplay.vue';
import { reactive, ref } from 'vue';
import html2canvas from 'html2canvas';
import Card from '@/Components/General/Card.vue';
import { Course } from '@/Types/course';
import ColorPickerWrapper from '@/Components/Form/ColorPickerWrapper.vue';
import InputTextSize from '@/Components/Form/InputTextSize.vue';
import TimeTableDesignControls from './TimeTableDesignControls.vue';

const props = defineProps<{
  timeTable: TimeTable;
  courses: Array<Course>;
}>();

const timetableRef = ref<HTMLElement | null>(null);

const designConfig = reactive({
  background: {
    image: '/img/background-01.png'
  },
  body: {
    fontSize: '1em',
    color: '#c1c1c1',
    scale: 1,
    'line-height': 1.2,
    'margin-top': '0.5em'
  },
  highlight: {
    color: '#f0f0ff',
    background: '#679'
  },
  header: {
    color: '#b0e0ff',
    scale: 1.2
  },
  padding: 20
});

const onSave = () => {
  alert('Save functionality is not implemented yet.');
};

const onDownload = async () => {
  if (!timetableRef.value) return;

  const canvas = await html2canvas(timetableRef.value, {
    useCORS: true,
    backgroundColor: null,
    scale: 2,
    logging: false
  });

  const link = document.createElement('a');
  link.download = `timetable_${props.timeTable.year}-${props.timeTable.week}.jpg`;
  link.href = canvas.toDataURL('image/jpeg', 0.95);
  link.click();
};

const menuItems = [
  {
    label: 'Save Changes',
    icon: 'pi pi-save',
    command: onSave
  },
  {
    label: 'Download Image',
    icon: 'pi pi-download',
    command: onDownload
  }
];
</script>

<template>
  <div class="flex flex-row">
    <Card
      class="flex-1 flex-col rounded-r-none overflow-y-scroll h-[800px]"
      border
      :menuItems="menuItems"
    >
      <TimeTableDesignControls v-model="designConfig" @onSave="onSave" @onDownload="onDownload" />
      <pre class="mt-4 bg-gray-100 p-2 text-xs">{{ designConfig }}</pre>
    </Card>
    <div ref="timetableRef" class="flex-1">
      <TimeTableDisplay
        :designConfig="designConfig"
        :courses="courses"
        :year="timeTable.year"
        :week="timeTable.week"
      />
    </div>
  </div>
</template>
