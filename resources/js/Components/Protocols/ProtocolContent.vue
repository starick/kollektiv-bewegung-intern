<script setup lang="ts">
defineProps<{
  // Rendered and sanitised on the server, see App\Markdown\ProtocolMarkdown
  html: string;
}>();
</script>

<template>
  <article
    class="prose dark:prose-invert max-w-none prose-h2:border-b prose-h2:border-gray-200 dark:prose-h2:border-gray-700 prose-h2:pb-1"
    v-html="html"
  />
</template>
