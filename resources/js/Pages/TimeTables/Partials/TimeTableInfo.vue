<script setup lang="ts">
import { TimeTable } from '@/Types/time-table';
import { endOfWeek, formatDate, formatDateTime, startOfWeek } from '@/Helpers/date-time-helper';
import InfoField from '@/Components/General/InfoField.vue';

defineProps<{
  timeTable: TimeTable;
}>();
</script>

<template>
  <section class="space-y-6">
    <h2 class="text-lg font-semibold">Timetable</h2>

    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
      <InfoField title="Year / Week"> {{ timeTable.year }} / {{ timeTable.week }} </InfoField>
      <InfoField title="Created At">
        {{ formatDateTime(timeTable.createdAt) }}
      </InfoField>
      <InfoField title="Time Span">
        {{ formatDate(startOfWeek(timeTable.year, timeTable.week)) }} -
        {{ formatDate(endOfWeek(timeTable.year, timeTable.week)) }}
      </InfoField>
      <InfoField title="Updated At">
        {{ formatDateTime(timeTable.updatedAt) }}
      </InfoField>
      <InfoField title="Number Of Courses">
        {{ timeTable.coursesCount }}
      </InfoField>
      <InfoField title="Created By">
        <span>{{ timeTable.creator.name }}</span>
        <span class="text-gray-500 ml-2">({{ timeTable.creator.email }})</span>
      </InfoField>
    </dl>
  </section>
</template>
