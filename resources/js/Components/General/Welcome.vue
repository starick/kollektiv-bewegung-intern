<script setup lang="ts">
import ApplicationLogo from '@/Components/General/ApplicationLogo.vue';
</script>

<template>
  <div>
    <div
      class="p-6 lg:p-8 dark:bg-gray-800 dark:bg-gradient-to-bl dark:from-gray-700/50 dark:via-transparent border-b border-gray-200 dark:border-gray-700"
    >
      <ApplicationLogo class="block h-24 w-auto" />

      <h1 class="mt-8 text-2xl font-medium text-gray-900 dark:text-white">
        Welcome to Kollektiv Bewegung Intern!
      </h1>

      <p class="mt-6 text-gray-500 dark:text-gray-400 leading-relaxed">
        This tool is developed to help manage internal processes and communication within
        Kollektiv:Bewegung. It's a work in progress so please be kind with it. If you encounter any
        issues or have suggestions for improvements, feel free to reach out to the development team.
      </p>
    </div>
  </div>
</template>
