<script setup lang="ts">
import { DESIGN_TEMPLATES, DesignTemplate } from '@/Constants/design-templates';

const model = defineModel<DesignTemplate>({ required: true });
</script>

<template>
  <div class="grid grid-cols-3 gap-3 w-full">
    <button
      v-for="template in DESIGN_TEMPLATES"
      :key="template.name"
      type="button"
      class="rounded-lg overflow-hidden border-2 transition-all text-left focus:outline-none"
      :class="model.name === template.name
        ? 'border-indigo-500 ring-2 ring-indigo-300'
        : 'border-transparent hover:border-gray-300'"
      @click="model = template"
    >
      <img :src="template.preview" :alt="template.name" class="w-full h-20 object-cover" />
    </button>
  </div>
</template>
