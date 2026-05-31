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
    :class="`relative flex flex-col shadow-lg overflow-hidden bg-gray-300`"
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
    <TimeTableFooter
      :footerConfig="designConfig.footer"
      :addRegistrationNote="!designConfig.highlight.registrationNoteAsOverlay"
    />

    <div :class="`origin-top`" :style="designConfig.body">
      <div
        v-if="designConfig.overlay.hasOverlay"
        class="absolute top-0 left-0 w-full h-full z-0"
        :style="overlayStyle"
      />
      <div
        v-for="group in groupedCourses"
        :key="group.dateKey"
        class="mb-2 flex flex-row border-t border-white/30 z-10 relative"
      >
        <div class="w-[130px] text-center">
          <h2 class="text-3xl font-bold pr-12 uppercase">
            {{ group.day }}
          </h2>
          <h3 class="text-xl font-semibold relative -top-3">
            {{ group.dateLabel }}
          </h3>
        </div>

        <div class="flex-1">
          <div
            v-for="course in group.courses"
            :key="course.id"
            class="grid grid-cols-12 gap-x-2 py-0.2"
          >
            <div class="font-medium col-span-2">{{ course.startTime }} - {{ course.endTime }}</div>
            <div class="col-span-9 pl-2">
              <span class="font-bold mr-4 inline-block">{{ course.name }}</span>
              <span class="text-sm inline-block mr-4">{{ course.instructor }}</span>
              <span
                v-if="course.location"
                class="text-sm font-bold p-0.5 inline-block"
                :style="{ color: designConfig.highlight.color }"
              >
                {{ course.location }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
