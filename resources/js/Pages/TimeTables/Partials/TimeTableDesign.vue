<script setup lang="ts">
import { TimeTable } from '@/Types/time-table';
import TimeTableDisplay from '@/Components/TimeTables/TimeTableDisplay.vue';
import { reactive, ref, watch } from 'vue';
import html2canvas from 'html2canvas';
import Card from '@/Components/General/Card.vue';
import { Course } from '@/Types/course';
import ColorPickerWrapper from '@/Components/Form/ColorPickerWrapper.vue';
import InputTextSize from '@/Components/Form/InputTextSize.vue';
import TimeTableDesignControls from './TimeTableDesignControls.vue';
import useTimeTableAtions from '@/Composables/use-timetable-actions';
import useAlert from '@/Composables/use-alerts';

const props = defineProps<{
  timeTable: TimeTable;
  courses: Array<Course>;
  isLoading: boolean;
}>();

const timeTableActions = useTimeTableAtions();
const alert = useAlert();

const size = ref(800);

const timetableRef = ref<HTMLElement | null>(null);

const displayConfig = reactive({
  background: {
    image: '/img/abstract-1.png'
  },
  body: {
    fontSize: '1em',
    color: '#424242',
    scale: 1,
    'line-height': 1.6,
    'margin-top': '0.5em'
  },
  highlight: {
    color: '#B8611A',
    background: '#F5DCB6'
  },
  header: {
    color: '#A14800',
    scale: 1.15
  }
});

const onSave = () => {
  timeTableActions
    .update(props.timeTable.id, { display_config: JSON.stringify(displayConfig) })
    .then((result) => {
      alert.add('Design saved successfully', 'success');
    })
    .catch((e) => {
      alert.error('Error saving design', e);
    });
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

watch(
  () => props.timeTable.displayConfig,
  (value) => {
    if (value) {
      Object.assign(displayConfig, { ...displayConfig, ...value });
    }
  },
  { immediate: true }
);
</script>

<template>
  <div>
    <div class="flex flex-row" style="letter-spacing: 0.01px">
      <Card
        :class="`flex-1 flex-col rounded-r-none overflow-y-scroll h-[${size}px]`"
        border
        :menuItems="menuItems"
      >
        <TimeTableDesignControls
          v-model="displayConfig"
          @onSave="onSave"
          @onDownload="onDownload"
        />
      </Card>
      <div ref="timetableRef" class="flex-1">
        <div
          v-if="isLoading"
          :class="`flex flex-1 flex-col justify-center bg-gray-100 items-center h-[${size}px] w-[${size}px]`"
        >
          <ProgressSpinner />
        </div>

        <TimeTableDisplay
          v-else
          :designConfig="displayConfig"
          :courses="courses"
          :year="timeTable.year"
          :week="timeTable.week"
          :size="size"
        />
      </div>
    </div>
  </div>
</template>
