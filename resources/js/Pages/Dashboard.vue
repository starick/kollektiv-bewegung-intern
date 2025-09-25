<script setup lang="ts">
import AppLayout from '@/Components/Layout/AppLayout.vue';
import Welcome from '@/Components/General/Welcome.vue';
</script>

<template>
  <AppLayout title="Dashboard">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        Dashboard
      </h2>
    </template>
    <Card>
      <Welcome />
    </Card>
  </AppLayout>
</template>
