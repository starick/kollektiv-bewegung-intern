<script setup lang="ts">
import ParticipantChips from '@/Components/Protocols/ParticipantChips.vue';
import { Tag } from 'primevue';
import { computed } from 'vue';

const props = defineProps<{
  protocol: {
    date: string;
    description: string | null;
    topics: string[];
    participants: string[];
  };
}>();

const longDate = computed(() =>
  new Date(props.protocol.date).toLocaleDateString('de-DE', {
    timeZone: 'UTC',
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
);
</script>

<template>
  <header class="mb-6">
    <p class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400">
      {{ $t('date') }}
    </p>
    <h1 class="mt-1 text-3xl font-bold tracking-tight">{{ longDate }}</h1>
    <p v-if="protocol.description" class="mt-1 text-lg text-gray-600 dark:text-gray-300">
      {{ protocol.description }}
    </p>
  </header>

  <div
    v-if="protocol.participants.length || protocol.topics.length"
    class="mb-8 rounded-xl border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800/50 divide-y divide-gray-200 dark:divide-gray-700"
  >
    <section v-if="protocol.participants.length" class="p-4 sm:p-5">
      <h2 class="flex items-center gap-2 text-lg font-semibold mb-3">
        <i class="pi pi-users" aria-hidden="true" />
        {{ $t('Participants') }}
        <span
          class="rounded-full bg-gray-200 dark:bg-gray-700 px-2 py-0.5 text-xs font-medium text-gray-700 dark:text-gray-200"
        >
          {{ protocol.participants.length }}
        </span>
      </h2>
      <ParticipantChips :participants="protocol.participants" />
    </section>

    <section v-if="protocol.topics.length" class="p-4 sm:p-5">
      <h2 class="flex items-center gap-2 text-lg font-semibold mb-3">
        <i class="pi pi-tags" aria-hidden="true" />
        {{ $t('Topics') }}
      </h2>
      <div class="flex flex-wrap gap-1.5">
        <Tag v-for="topic in protocol.topics" :key="topic" :value="topic" severity="secondary" />
      </div>
    </section>
  </div>
</template>
