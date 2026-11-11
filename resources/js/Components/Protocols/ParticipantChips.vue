<script setup lang="ts">
defineProps<{
  participants: string[];
}>();

// Array.from keeps letters outside the BMP (e.g. emoji) intact
const initial = (name: string) => (Array.from(name.trim())[0] ?? '').toUpperCase();
</script>

<template>
  <ul class="flex flex-wrap gap-2">
    <li
      v-for="participant in participants"
      :key="participant"
      class="flex items-center gap-2 rounded-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 py-1 pl-1 pr-3 shadow-sm"
    >
      <span
        class="flex size-7 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900 text-xs font-semibold text-emerald-700 dark:text-emerald-200"
        aria-hidden="true"
      >
        {{ initial(participant) }}
      </span>
      <span class="text-sm font-medium">{{ participant }}</span>
    </li>
  </ul>
</template>
