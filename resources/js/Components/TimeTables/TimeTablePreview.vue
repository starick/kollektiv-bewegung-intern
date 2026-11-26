<script setup lang="ts">
import TimeTableBubbleDisplay from '@/Components/TimeTables/TimeTableBubbleDisplay.vue';
import TimeTableDisplay from '@/Components/TimeTables/TimeTableDisplay.vue';
import { DEFAULT_DISPLAY_CONFIG } from '@/Constants/default-display-config';
import { Course } from '@/Types/course';
import { TimeTable } from '@/Types/time-table';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

const props = defineProps<{
  timeTable: TimeTable;
  courses: Course[];
}>();

// Rendered at the size the design was made for, then scaled to fit, so it looks like the download
const SIZE = 800;

const box = ref<HTMLElement | null>(null);
const width = ref(0);
let observer: ResizeObserver | undefined;

onMounted(() => {
  observer = new ResizeObserver(([entry]) => (width.value = entry.contentRect.width));
  if (box.value) observer.observe(box.value);
});
onBeforeUnmount(() => observer?.disconnect());

const config = computed(() => ({
  ...DEFAULT_DISPLAY_CONFIG,
  ...(props.timeTable.displayConfig ?? {})
}));
</script>

<template>
  <div
    ref="box"
    class="relative w-full overflow-hidden rounded-lg bg-gray-100 dark:bg-gray-800"
    :style="{ height: `${width}px` }"
    aria-hidden="true"
  >
    <div
      v-if="width"
      class="pointer-events-none absolute left-0 top-0 origin-top-left"
      :style="{ width: `${SIZE}px`, height: `${SIZE}px`, transform: `scale(${width / SIZE})` }"
    >
      <TimeTableBubbleDisplay
        v-if="config.layout === 'bubble'"
        :design-config="config"
        :courses="courses"
        :year="timeTable.year"
        :week="timeTable.week"
        :size="SIZE"
      />
      <TimeTableDisplay
        v-else
        :design-config="config"
        :courses="courses"
        :year="timeTable.year"
        :week="timeTable.week"
        :size="SIZE"
      />
    </div>
  </div>
</template>
