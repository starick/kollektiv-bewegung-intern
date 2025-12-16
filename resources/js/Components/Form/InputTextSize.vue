<script setup lang="ts">
import { computed } from 'vue';

const modelValue = defineModel<string>({ required: true });

const normalizedValue = computed({
  get: () => +modelValue.value.replace('em', ''),
  set: (value) => {
    modelValue.value = `${value}em`;
  }
});
</script>

<template>
  <InputNumber v-model="normalizedValue" suffix=" em" showButtons :step="0.1" />
</template>
