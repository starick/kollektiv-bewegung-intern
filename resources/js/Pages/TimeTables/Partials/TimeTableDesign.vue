<script setup lang="ts">
import { TimeTable } from '@/Types/time-table';
import TimeTableDisplay from '@/Components/TimeTables/TimeTableDisplay.vue';
import { computed, reactive, ref, watch } from 'vue';
import html2canvas from 'html2canvas';
import Card from '@/Components/General/Card.vue';
import { Course } from '@/Types/course';
import TimeTableDesignControls from './TimeTableDesignControls.vue';
import useTimeTableAtions from '@/Composables/use-timetable-actions';
import useAlert from '@/Composables/use-alerts';
import { DEFAULT_DISPLAY_CONFIG } from '@/Constants/default-display-config';
import { useConfirm } from 'primevue';

const props = defineProps<{
  timeTable: TimeTable;
  courses: Array<Course>;
  isLoading: boolean;
}>();

const timeTableActions = useTimeTableAtions();
const alert = useAlert();
const confirm = useConfirm();

const size = ref(800);
const timetableRef = ref<HTMLElement | null>(null);
const displayConfig = reactive(DEFAULT_DISPLAY_CONFIG);
const savedSnapshot = ref(JSON.stringify(DEFAULT_DISPLAY_CONFIG));

const hasUnsavedChanges = computed(() => JSON.stringify(displayConfig) !== savedSnapshot.value);

const doDownload = async () => {
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

const onSave = () => {
  timeTableActions
    .update(props.timeTable.id, { display_config: JSON.stringify(displayConfig) })
    .then(() => {
      savedSnapshot.value = JSON.stringify(displayConfig);
      alert.add('Design saved successfully', 'success');
    })
    .catch((e) => {
      alert.error('Error saving design', e);
    });
};

const onDownload = () => {
  if (!hasUnsavedChanges.value) {
    doDownload();
    return;
  }
  confirm.require({
    header: 'Unsaved Changes',
    message: 'You have unsaved changes. Save before downloading?',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'Save & Download',
    rejectLabel: 'Download Anyway',
    accept: () => {
      timeTableActions
        .update(props.timeTable.id, { display_config: JSON.stringify(displayConfig) })
        .then(() => {
          savedSnapshot.value = JSON.stringify(displayConfig);
          doDownload();
        })
        .catch((e) => alert.error('Error saving design', e));
    },
    reject: () => doDownload()
  });
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
      savedSnapshot.value = JSON.stringify(displayConfig);
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
