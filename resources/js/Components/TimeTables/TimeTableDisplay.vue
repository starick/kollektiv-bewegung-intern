<script setup lang="ts">
import { defineProps, computed, toRef } from 'vue';
import { Course } from '@/Types/course';
import { endOfWeek, formatDate, formatTime, startOfWeek } from '@/Helpers/date-time-helper';
import { groupCoursesByDay } from '@/Helpers/course-mapper';
import { TimeTableDesignConfig } from '@/Types/time-table';

const props = defineProps<{
  week: number;
  year: number;
  designConfig: TimeTableDesignConfig;
  courses: Array<Course>;
}>();

const groupedCourses = computed(() => groupCoursesByDay(props.courses));

const backgroundStyle = computed(() => {
  const bg = props.designConfig.background ?? {};
  return {
    backgroundColor: bg.color ?? 'transparent',
    backgroundImage: bg.image ? `url(${bg.image})` : 'none',
    backgroundSize: bg.size ?? 'cover',
    backgroundPosition: bg.position ?? 'center',
    backgroundRepeat: bg.repeat ?? 'no-repeat'
  };
});

const timeFrame = computed(
  () =>
    `${formatDate(startOfWeek(props.year, props.week))} - ${formatDate(endOfWeek(props.year, props.week))}`
);
</script>

<template>
  <div
    class="relative flex flex-col w-[800px] h-[800px] shadow-lg overflow-hidden bg-gray-300"
    :style="backgroundStyle"
  >
    <h1 class="text-center text-3xl font-bold tracking-wider" :style="designConfig.header ?? {}">
      KOLLEKTIV:BEWEGUNG
    </h1>
    <h2
      class="text-center text-xl font-bold mb-4 tracking-wider opacity-80"
      :style="{ color: designConfig.header.color }"
    >
      {{ timeFrame }}
    </h2>

    <div
      class="absolute top-8 -right-4 rounded-2xl py-2 px-4 rotate-[30deg] opacity-90"
      :style="designConfig.highlight"
    >
      <p>Bitte vorher bei den</p>
      <p>Kursleitenden anmelden</p>
    </div>
    <div
      class="absolute bottom-2 left-2 text-sm opacity-90"
      :style="{ color: designConfig.highlight.color }"
    >
      <p>
        Sofern nicht anders angegeben, finden die Kurse im Bewegungsraum der B-Side (Am Mittelhafen
        42) statt.
      </p>
      <p>Alle Angebote sind auf Pay-What-You-Feel-Basis</p>
    </div>

    <div :class="`origin-top`" :style="designConfig.body">
      <div
        v-for="group in groupedCourses"
        :key="group.dateKey"
        class="mb-2 flex flex-row border-t border-white/30"
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
            <div class="font-medium col-span-2">
              {{ formatTime(course.startTime) }} - {{ formatTime(course.endTime) }}
            </div>
            <div class="col-span-9 pl-2">
              <span class="font-bold mr-4 inline-block">{{ course.name }}</span>
              <span class="text-sm inline-block mr-4">{{ course.instructor }}</span>
              <span
                v-if="course.location"
                class="text-sm p-0.5 inline-block"
                :style="designConfig.highlight"
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
