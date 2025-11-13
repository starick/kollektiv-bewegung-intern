<script setup lang="ts">
import { Time } from '@/ValueObjects/time';
import { computed, watch } from 'vue';

const modelValue = defineModel<Time>({ required: true });
</script>

<template>
  <div class="flex items-center">
    <InputNumber
      v-model="modelValue.hour"
      :min="0"
      :max="23"
      inputClass="w-12 text-center"
      showButtons
      button-layout="vertical"
    />
    <span class="text-gray-500 select-none mx-1">:</span>
    <InputNumber
      v-model="modelValue.minute"
      :min="0"
      :max="59"
      :step="15"
      :prefix="modelValue.minute < 10 ? '0' : ''"
      inputClass="w-12 text-center mx-0"
      showButtons
      button-layout="vertical"
    />
  </div>
</template>
