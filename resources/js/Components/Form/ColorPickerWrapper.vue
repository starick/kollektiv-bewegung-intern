<script setup lang="ts">
import { computed } from 'vue';
import ColorPicker from 'primevue/colorpicker';

const modelValue = defineModel<string>({ required: true });

const normalizedValue = computed({
  get: () => modelValue.value.replace(/^#/, ''),
  set: (val) => {
    const normalized = val.startsWith('#') ? val : `#${val}`;
    modelValue.value = normalized;
  }
});
</script>

<template>
  <div class="flex items-center gap-2">
    <ColorPicker v-model="normalizedValue" format="hex" />
    <input
      type="text"
      class="border rounded px-2 py-1 text-sm w-24"
      v-model="modelValue"
      placeholder="#rrggbb"
    />
  </div>
</template>
