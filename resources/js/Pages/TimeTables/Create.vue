<script setup lang="ts">
import { defineProps, defineEmits } from 'vue';

const props = defineProps({});

const emit = defineEmits([]);

const title = 'New Page';
</script>

<template>
  <AppLayout :title="title">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ title }}]
      </h2>
    </template>
  </AppLayout>
</template>
