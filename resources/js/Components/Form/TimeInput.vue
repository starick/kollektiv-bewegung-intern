<script setup lang="ts">
import { Time } from '@/ValueObjects/time';
import { Value } from 'sass';
import { computed } from 'vue';

const emits = defineEmits(['update:modelValue']);
const props = defineProps<{
  modelValue: string | Time;
}>();

const time = computed({
  get() {
    return typeof props.modelValue == 'string' ? Time.parse(props.modelValue) : props.modelValue;
  },
  set(value: Time) {
    emits('update:modelValue', typeof props.modelValue == 'string' ? value.toString() : value);
  }
});
</script>

<template>
  <div class="flex flex-row gap-2">
    <input
      class="w-16 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
      :value="time.hour"
    />
    <input
      class="w-16 border border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm"
      :value="time.minute"
    />
  </div>
</template>
