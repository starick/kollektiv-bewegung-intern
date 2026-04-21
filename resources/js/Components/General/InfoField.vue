<script setup lang="ts">
defineProps<{
  title: string;
}>();
</script>

<template>
  <div>
    <dt class="font-medium text-gray-500 dark:text-gray-400">{{ title }}</dt>
    <dd>
      <slot />
    </dd>
  </div>
</template>
