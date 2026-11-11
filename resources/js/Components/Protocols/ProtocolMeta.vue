<script setup lang="ts">
import { Protocol } from '@/Types/protocol';

defineProps<{
  protocol: Protocol;
}>();

const formatDateTime = (value: Date | string) =>
  new Date(value).toLocaleString('de-DE', { dateStyle: 'medium', timeStyle: 'short' });
</script>

<template>
  <dl
    class="flex flex-col sm:flex-row sm:flex-wrap gap-x-6 gap-y-1 pb-4 mb-6 border-b border-gray-200 dark:border-gray-700 text-sm text-gray-500 dark:text-gray-400"
  >
    <div class="flex items-center gap-2">
      <i class="pi pi-upload text-xs" aria-hidden="true" />
      <dt>{{ $t('Uploaded') }}:</dt>
      <dd>
        <span class="font-medium text-gray-700 dark:text-gray-200">{{
          protocol.uploader?.name ?? '–'
        }}</span>
        · {{ formatDateTime(protocol.createdAt) }}
      </dd>
    </div>
    <div class="flex items-center gap-2">
      <i class="pi pi-pencil text-xs" aria-hidden="true" />
      <dt>{{ $t('Last Edited') }}:</dt>
      <dd v-if="protocol.editor">
        <span class="font-medium text-gray-700 dark:text-gray-200">{{ protocol.editor.name }}</span>
        · {{ formatDateTime(protocol.updatedAt) }}
      </dd>
      <dd v-else>–</dd>
    </div>
  </dl>
</template>
