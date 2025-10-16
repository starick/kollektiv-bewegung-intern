<script setup lang="ts">
import { TimeTable, TimeTableDesignConfig } from '@/Types/time-table';
import { defineProps, computed, toRef } from 'vue';
import { Course } from '@/Types/course';
import {
  formatDayAndMonth,
  formatInternationalDate,
  formatTime,
  formatWeekday
} from '@/Helpers/date-time-helper';
import PrimaryButton from '../General/PrimaryButton.vue';
import html2canvas from 'html2canvas';

const props = defineProps<{ courses: Array<Course>; config: TimeTableDesignConfig }>();

const config = toRef(props, 'config');

const groupedCourses = computed(() => {
  const map = new Map<
    string,
    {
      day: string;
      dateLabel: string;
      dateKey: string; // YYYY-MM-DD for sorting
      courses: Array<Course>;
    }
  >();

  for (const course of props.courses ?? []) {
    const dateKey = formatInternationalDate(course.date);

    if (!map.has(dateKey)) {
      map.set(dateKey, {
        day: formatWeekday(course.date),
        dateLabel: formatDayAndMonth(course.date),
        dateKey,
        courses: [course]
      });
    } else {
      map.get(dateKey).courses.push(course);
    }
  }

  return Array.from(map.values()).sort((a, b) =>
    a.dateKey < b.dateKey ? -1 : a.dateKey > b.dateKey ? 1 : 0
  );
});

const backgroundStyle = computed(() => {
  const bg = config.value?.background ?? {};
  return {
    backgroundColor: bg.color ?? 'transparent',
    backgroundImage: bg.image ? `url(${bg.image})` : 'none',
    backgroundSize: bg.size ?? 'cover',
    backgroundPosition: bg.position ?? 'center',
    backgroundRepeat: bg.repeat ?? 'no-repeat'
  };
});
</script>

<template>
  <div
    class="relative flex flex-col w-[800px] h-[800px] shadow-lg overflow-hidden bg-gray-300"
    :style="backgroundStyle"
  >
    <h1 class="text-center text-3xl font-bold mb-4 tracking-wider">YOGUERILLAS</h1>

    <div class="absolute top-8 -right-4 rounded-2xl py-2 px-4 rotate-[30deg]">
      <p>Bitte vorher bei den</p>
      <p>Kursleitenden anmelden</p>
    </div>
    <div class="absolute bottom-2 left-2 text-sm opacity-70">
      <p>
        Sofern nicht anders angegeben, finden die Kurse im Bewegungsraum der B-Side (Am Mittelhafen
        42) statt.
      </p>
      <p>Alle Angebote sind auf Pay-What-You-Feel-Basis</p>
      {{ config.background.image }}
    </div>

    <div class="scale-[86%] origin-top">
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
            <div class="text-[14px] font-medium col-span-2">
              {{ formatTime(course.startTime) }} - {{ formatTime(course.endTime) }}
            </div>
            <div class="col-span-9 pl-2">
              <span class="text-[14px] font-bold mr-4">{{ course.name }}</span>
              <span class="text-[14px] text-sm">{{ course.instructor }}</span>
              <span v-if="course.location" class="text-sm ml-4">
                {{ course.location }}
              </span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
