<script setup lang="ts">
import { computed } from 'vue';
import { Course } from '@/Types/course';
import { endOfWeek, formatDate, startOfWeek } from '@/Helpers/date-time-helper';
import { groupCoursesByDay } from '@/Helpers/course-mapper';
import { TimeTableDesignConfig } from '@/Types/time-table';
import TimeTableFooter from '@/Components/TimeTables/TimeTableFooter.vue';

const props = defineProps<{
  week: number;
  year: number;
  designConfig: TimeTableDesignConfig;
  courses: Array<Course>;
  size: number;
}>();

const groupedCourses = computed(() => groupCoursesByDay(props.courses));

const columns = computed(() => {
  const groups = groupedCourses.value;
  const colCount = props.designConfig.columns ?? 4;
  const result: (typeof groups)[] = Array.from({ length: colCount }, () => []);
  if (groups.length === 0) return result;

  // Each group's visual height: 1 unit per course + 1 for the day header
  const heights = groups.map(g => g.courses.length + 1);
  const total = heights.reduce((a, b) => a + b, 0);

  // Binary search: find the minimum max-column-height that still fits in colCount columns
  const canFit = (limit: number) => {
    let usedCols = 1, current = 0;
    for (const h of heights) {
      if (current + h > limit) { usedCols++; current = h; }
      else current += h;
      if (usedCols > colCount) return false;
    }
    return true;
  };

  let lo = Math.max(...heights), hi = total;
  while (lo < hi) {
    const mid = Math.floor((lo + hi) / 2);
    if (canFit(mid)) hi = mid; else lo = mid + 1;
  }

  // Assign groups to columns using the found limit
  let currentCol = 0, currentH = 0;
  for (let i = 0; i < groups.length; i++) {
    if (currentH + heights[i] > lo && currentCol < colCount - 1) {
      currentCol++;
      currentH = 0;
    }
    result[currentCol].push(groups[i]);
    currentH += heights[i];
  }

  return result;
});

const backgroundStyle = computed(() => {
  const bg = props.designConfig.background ?? ({} as TimeTableDesignConfig['background']);
  return {
    backgroundColor: 'transparent',
    backgroundImage: bg.image ? `url(${bg.image})` : 'none',
    backgroundSize: 'cover',
    backgroundPosition: 'center',
    backgroundRepeat: 'no-repeat',
    width: `${props.size}px`,
    height: `${props.size}px`,
    fontFamily: props.designConfig.body.fontFamily
  };
});

const overlayStyle = computed(() => {
  return props.designConfig.overlay.hasOverlay
    ? {
        backgroundColor: props.designConfig.overlay.color,
        opacity: props.designConfig.overlay.opacity
      }
    : {};
});

const timeFrame = computed(
  () =>
    `${formatDate(startOfWeek(props.year, props.week))} - ${formatDate(endOfWeek(props.year, props.week))}`
);
</script>

<template>
  <div
    class="relative flex flex-col shadow-lg bg-gray-300 overflow-hidden"
    :style="backgroundStyle"
  >
    <h1
      class="text-center text-3xl font-bold tracking-wider"
      :style="{ ...designConfig.header, fontFamily: designConfig.header.fontFamily }"
    >
      KOLLEKTIV:BEWEGUNG
    </h1>
    <h2
      class="text-center text-xl font-bold mb-4 tracking-wider opacity-80"
      :style="{ color: designConfig.header.color, fontFamily: designConfig.header.fontFamily }"
    >
      {{ timeFrame }}
    </h2>

    <div
      v-if="designConfig.highlight.registrationNoteAsOverlay"
      class="absolute top-8 -right-4 rounded-2xl py-2 px-4 rotate-[30deg] opacity-90 z-20"
      :style="designConfig.highlight"
    >
      <p>Bitte vorher bei den</p>
      <p>Kursleitenden anmelden</p>
    </div>

    <TimeTableFooter :footerConfig="designConfig.footer" :addRegistrationNote="!designConfig.highlight.registrationNoteAsOverlay" />

    <div class="origin-top flex-1 min-h-0" :style="designConfig.body">
      <div
        v-if="designConfig.overlay.hasOverlay"
        class="absolute top-0 left-0 w-full h-full z-0"
        :style="overlayStyle"
      />

      <div class="flex flex-row gap-2 px-2 pt-2 z-10 relative h-full">
        <div
          v-for="(col, colIndex) in columns"
          :key="colIndex"
          class="flex-1 flex flex-col gap-1"
        >
          <div
            v-for="group in col"
            :key="group.dateKey"
            class="mb-1 flex flex-col gap-1"
          >
            <div
              class="rounded-xl py-1 px-2 text-center"
              :style="{ backgroundColor: designConfig.highlight.background, color: designConfig.highlight.color, lineHeight: 'normal' }"
            >
              <span class="font-bold uppercase">{{ group.day }}</span>
              <span class="opacity-80 ml-2">{{ group.dateLabel }}</span>
            </div>

            <div
              v-for="course in group.courses"
              :key="course.id"
              class="rounded-xl py-2 px-2"
              :style="{ backgroundColor: designConfig.highlight.background + '55' }"
            >
              <div class="font-bold">{{ course.name }}</div>
              <div class="opacity-80 mt-0.5">{{ course.startTime }} - {{ course.endTime }}</div>
              <div class="mt-0.5">{{ course.instructor }}</div>
              <div
                v-if="course.location"
                class="font-bold mt-0.5"
                :style="{ color: designConfig.highlight.color }"
              >
                {{ course.location }}
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
