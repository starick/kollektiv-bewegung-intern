<script setup lang="ts">
import { AVAILABLE_FONTS } from '@/Constants/fonts';

const model = defineModel<string>({ required: true });
</script>

<template>
  <Dropdown
    v-model="model"
    :options="AVAILABLE_FONTS"
    optionLabel="label"
    optionValue="family"
    class="w-full"
  >
    <template #option="{ option }">
      <span :style="{ fontFamily: option.family }">{{ option.label }}</span>
    </template>
    <template #value="{ value }">
      <span :style="{ fontFamily: value }">{{ AVAILABLE_FONTS.find(f => f.family === value)?.label ?? value }}</span>
    </template>
  </Dropdown>
</template>
