<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { computed } from 'vue';
import ProtocolContent from '@/Components/Protocols/ProtocolContent.vue';
import ProtocolHeader from '@/Components/Protocols/ProtocolHeader.vue';
import { trans } from '@/Composables/use-translations';

const props = defineProps<{
  protocol: {
    date: string;
    description: string | null;
    topics: string[];
    participants: string[];
  };
  html: string;
}>();

const title = computed(() =>
  trans('Protocol :date', {
    date: new Date(props.protocol.date).toLocaleDateString('de-DE', { timeZone: 'UTC' })
  })
);
</script>

<template>
  <Head :title="title">
    <meta name="robots" content="noindex, nofollow" />
  </Head>

  <div class="min-h-screen bg-slate-50 dark:bg-slate-900 text-gray-900 dark:text-gray-100">
    <main class="max-w-4xl mx-auto px-4 py-8 sm:py-12">
      <p class="mb-6 flex items-center gap-2 text-sm font-medium text-gray-500 dark:text-gray-400">
        <i class="pi pi-link" aria-hidden="true" />
        {{ $t('Shared protocol') }}
      </p>

      <article class="rounded-lg bg-default-background p-4 sm:p-8">
        <ProtocolHeader :protocol="protocol" />

        <ProtocolContent v-if="html.trim()" :html="html" />
        <p v-else class="text-center text-gray-500 dark:text-gray-400">
          {{ $t('This protocol has no content yet.') }}
        </p>
      </article>
    </main>
  </div>
</template>
