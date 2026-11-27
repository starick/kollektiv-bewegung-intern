<script setup lang="ts">
import { WikiChangelogLink } from '@/Types/wiki';
import { Link } from '@inertiajs/vue3';
import { route } from 'ziggy-js';

withDefaults(
  defineProps<{
    changelog: WikiChangelogLink;
    // Highlighted while the changelog is open
    current?: boolean;
  }>(),
  { current: false }
);
</script>

<template>
  <div class="mt-3 pt-3 border-t border-gray-200 dark:border-gray-700">
    <Link
      :href="route('wiki.changelog')"
      class="flex items-center gap-2 rounded-md py-1.5 pl-6 pr-2"
      :class="
        current
          ? 'bg-gray-100 font-semibold dark:bg-gray-800'
          : 'hover:bg-gray-100 dark:hover:bg-gray-800'
      "
      :aria-current="current ? 'page' : undefined"
    >
      <i class="pi pi-sparkles text-gray-500" aria-hidden="true" />
      <span class="truncate">{{ $t('Changelog') }}</span>
      <span
        v-if="changelog.version"
        class="ml-auto shrink-0 text-xs text-gray-500 dark:text-gray-400 font-normal"
      >
        {{ changelog.version }}
      </span>
    </Link>
  </div>
</template>
