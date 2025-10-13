<script setup lang="ts">
import { TimeTable } from '@/Types/time-table';
import TimeTableDisplay from '@/Components/TimeTables/TimeTableDisplay.vue';

defineProps<{
  timeTable: TimeTable;
}>();
</script>

<template>
  <section class="space-y-6">
    <TimeTableDisplay :timeTable="timeTable" />
  </section>
</template>
