<script setup lang="ts">
import { TimeTable } from '@/Types/time-table';
import TimeTableDisplay from '@/Components/TimeTables/TimeTableDisplay.vue';
import TimeTableBubbleDisplay from '@/Components/TimeTables/TimeTableBubbleDisplay.vue';
import { computed, nextTick, onBeforeUnmount, onMounted, reactive, ref, watch } from 'vue';
import { toJpeg } from 'html-to-image';
import { exportTimetablePptx } from '@/Helpers/timetable-pptx-export';
import Card from '@/Components/General/Card.vue';
import { Course } from '@/Types/course';
import TimeTableDesignControls from './TimeTableDesignControls.vue';
import useTimeTableActions from '@/Composables/use-timetable-actions';
import useAlert from '@/Composables/use-alerts';
import { DEFAULT_DISPLAY_CONFIG } from '@/Constants/default-display-config';
import { SplitButton, useConfirm } from 'primevue';
import { trans } from '@/Composables/use-translations';

const props = defineProps<{
  timeTable: TimeTable;
  courses: Array<Course>;
  isLoading: boolean;
}>();

const timeTableActions = useTimeTableActions();
const alert = useAlert();
const confirm = useConfirm();

const size = ref(800);
const timetableRef = ref<HTMLElement | null>(null);

// The timetable is always rendered at `size` (that is what gets exported) and scaled down to fit narrow screens.
const previewBox = ref<HTMLElement | null>(null);
const previewWidth = ref(0);
const exporting = ref(false);
const scale = computed(() =>
  exporting.value || !previewWidth.value ? 1 : Math.min(1, previewWidth.value / size.value)
);
let resizeObserver: ResizeObserver | undefined;

onMounted(() => {
  resizeObserver = new ResizeObserver(([entry]) => (previewWidth.value = entry.contentRect.width));
  if (previewBox.value) resizeObserver.observe(previewBox.value);
});
onBeforeUnmount(() => resizeObserver?.disconnect());
const displayConfig = reactive(DEFAULT_DISPLAY_CONFIG);
const savedSnapshot = ref(JSON.stringify(DEFAULT_DISPLAY_CONFIG));

const hasUnsavedChanges = computed(() => JSON.stringify(displayConfig) !== savedSnapshot.value);

const doDownload = async () => {
  if (!timetableRef.value) return;

  await document.fonts.ready;
  await new Promise(requestAnimationFrame);

  const dataUrl = await toJpeg(timetableRef.value, {
    quality: 0.95,
    backgroundColor: '#f7c88f',
    pixelRatio: 2,
    cacheBust: true
  });

  const link = document.createElement('a');
  link.download = `timetable_${props.timeTable.year}-${props.timeTable.week}.jpg`;
  link.href = dataUrl;
  link.click();
};

const doDownloadPptx = async () => {
  // The ref is the flex wrapper; the slide must match the timetable itself (size × size).
  const timetable = timetableRef.value?.firstElementChild;
  if (!(timetable instanceof HTMLElement)) return;
  // Layers are measured with getBoundingClientRect, so the preview must be unscaled while exporting.
  exporting.value = true;
  try {
    await nextTick();
    await new Promise(requestAnimationFrame);
    await exportTimetablePptx(
      timetable,
      `timetable_${props.timeTable.year}-${props.timeTable.week}.pptx`
    );
  } catch (e) {
    alert.error(trans('Error exporting file'), e);
  } finally {
    exporting.value = false;
  }
};

const onSave = () => {
  timeTableActions
    .update(props.timeTable.id, { display_config: JSON.stringify(displayConfig) })
    .then(() => {
      savedSnapshot.value = JSON.stringify(displayConfig);
      alert.add(trans('Design saved successfully'), 'success');
    })
    .catch((e) => {
      alert.error(trans('Error saving design'), e);
    });
};

const onDownload = (kind: 'jpeg' | 'pptx' = 'jpeg') => {
  const download = kind === 'pptx' ? doDownloadPptx : doDownload;
  if (!hasUnsavedChanges.value) {
    download();
    return;
  }
  confirm.require({
    header: trans('Unsaved Changes'),
    message: trans('You have unsaved changes. Save before downloading?'),
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: trans('Save & Download'),
    rejectLabel: trans('Download Anyway'),
    accept: () => {
      timeTableActions
        .update(props.timeTable.id, { display_config: JSON.stringify(displayConfig) })
        .then(() => {
          savedSnapshot.value = JSON.stringify(displayConfig);
          download();
        })
        .catch((e) => alert.error(trans('Error saving design'), e));
    },
    reject: () => download()
  });
};

const downloadOptions = [
  {
    label: trans('Image (JPG)'),
    icon: 'pi pi-image',
    command: () => onDownload('jpeg')
  },
  {
    label: trans('For Canva (PPTX)'),
    icon: 'pi pi-file-export',
    command: () => onDownload('pptx')
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
  <div
    class="flex flex-col xl:flex-row gap-4 xl:gap-0"
    :style="{ letterSpacing: '0.01px', '--tt-size': `${size}px` }"
  >
    <Card
      class="flex flex-col min-w-0 border border-border xl:flex-1 xl:h-[var(--tt-size)] xl:overflow-y-auto xl:border-r-0 xl:rounded-r-none"
    >
      <template #header>
        <div
          class="flex flex-wrap items-center justify-between gap-2 px-4 py-3 border-b border-border"
        >
          <Button :label="$t('Save')" icon="pi pi-save" @click="onSave" />
          <SplitButton
            :label="$t('Download')"
            icon="pi pi-download"
            :model="downloadOptions"
            severity="secondary"
            @click="onDownload('jpeg')"
          />
        </div>
      </template>
      <TimeTableDesignControls
        v-model="displayConfig"
        @onSave="onSave"
        @onDownload="onDownload()"
      />
    </Card>
    <div
      ref="previewBox"
      class="w-full max-w-[var(--tt-size)] mx-auto xl:mx-0 shrink-0 overflow-hidden"
      :style="{ height: `${size * scale}px` }"
    >
      <div
        class="origin-top-left"
        :style="{
          width: `${size}px`,
          transform: scale < 1 ? `scale(${scale})` : undefined
        }"
      >
        <div ref="timetableRef">
          <div
            v-if="isLoading"
            class="flex flex-col justify-center bg-gray-100 dark:bg-gray-800 items-center"
            :style="{ width: `${size}px`, height: `${size}px` }"
          >
            <ProgressSpinner />
          </div>

          <TimeTableBubbleDisplay
            v-else-if="displayConfig.layout === 'bubble'"
            :designConfig="displayConfig"
            :courses="courses"
            :year="timeTable.year"
            :week="timeTable.week"
            :size="size"
          />
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
  </div>
</template>
