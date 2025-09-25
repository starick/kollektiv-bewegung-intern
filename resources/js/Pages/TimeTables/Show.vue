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

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg"></div>
      </div>
    </div>
  </AppLayout>
</template>
