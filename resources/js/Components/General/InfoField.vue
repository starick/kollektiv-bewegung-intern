<script setup lang="ts">
defineProps<{
  title: string;
}>();
</script>

<template>
  <div>
    <dt class="font-medium text-gray-300 dark:text-gray-600">{{ title }}</dt>
    <dd>
      <slot />
    </dd>
  </div>
</template>
