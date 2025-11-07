<script setup lang="ts">
import { Time } from '@/ValueObjects/time';
import { Value } from 'sass';
import { computed } from 'vue';

const emits = defineEmits(['update:modelValue']);

const modelValue = defineModel<Time>({ required: true });
</script>

<template>
  <div class="flex flex-row gap-2">
    <input
      class="w-16 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
      v-model="modelValue.hour"
    />
    <input
      class="w-16 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
      v-model="modelValue.minute"
    />
  </div>
</template>
