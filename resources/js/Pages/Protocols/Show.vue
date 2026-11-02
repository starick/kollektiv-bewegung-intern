<script setup lang="ts">
import Card from '@/Components/General/Card.vue';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { route } from 'ziggy-js';
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Protocol } from '@/Types/protocol';
import { MenuItem, Tag, useConfirm } from 'primevue';
import { trans } from '@/Composables/use-translations';

const props = defineProps<{
  protocol: Protocol;
  html: string;
  can: { update: boolean; delete: boolean };
}>();

const confirm = useConfirm();

const longDate = computed(() =>
  new Date(props.protocol.date).toLocaleDateString('de-DE', {
    timeZone: 'UTC',
    weekday: 'long',
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
);

const formatDateTime = (value: Date | string) =>
  new Date(value).toLocaleString('de-DE', { dateStyle: 'medium', timeStyle: 'short' });

// Array.from keeps letters outside the BMP (e.g. emoji) intact
const initial = (name: string) => (Array.from(name.trim())[0] ?? '').toUpperCase();

const formattedDate = computed(() =>
  new Date(props.protocol.date).toLocaleDateString('de-DE', { timeZone: 'UTC' })
);

const confirmDelete = () => {
  confirm.require({
    header: trans('Delete protocol'),
    message: trans('Delete the protocol from :date? This cannot be undone.', {
      date: formattedDate.value
    }),
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: trans('Delete'),
    rejectLabel: trans('Cancel'),
    acceptClass: 'p-button-danger',
    accept: () => router.delete(route('protocols.destroy', { protocol: props.protocol.id }))
  });
};

const menuItems: MenuItem[] = [
  {
    label: trans('Back to Overview'),
    icon: 'pi pi-arrow-left',
    command: () => router.get(route('protocols.index'))
  },
  {
    label: trans('Download'),
    icon: 'pi pi-download',
    // Plain link instead of an Inertia visit, so the browser handles the file download
    url: route('protocols.download', { protocol: props.protocol.id })
  }
];

const title = computed(() => trans('Protocol :date', { date: formattedDate.value }));
</script>

<template>
  <AppLayout :title="title">
    <Card :menu-items="menuItems">
      <template #menu-end>
        <Button
          v-if="can.update"
          :label="$t('Edit')"
          icon="pi pi-pencil"
          text
          @click="router.get(route('protocols.edit', { protocol: protocol.id }))"
        />
        <Button
          v-if="can.delete"
          :label="$t('Delete')"
          icon="pi pi-trash"
          severity="danger"
          text
          @click="confirmDelete"
        />
      </template>

      <div class="max-w-4xl mx-auto py-2 sm:py-4">
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
              <span class="font-medium text-gray-700 dark:text-gray-200">{{
                protocol.editor.name
              }}</span>
              · {{ formatDateTime(protocol.updatedAt) }}
            </dd>
            <dd v-else>–</dd>
          </div>
        </dl>

        <header class="mb-6">
          <p
            class="text-xs font-semibold uppercase tracking-wider text-gray-500 dark:text-gray-400"
          >
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
            <ul class="flex flex-wrap gap-2">
              <li
                v-for="participant in protocol.participants"
                :key="participant"
                class="flex items-center gap-2 rounded-full bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-700 py-1 pl-1 pr-3 shadow-sm"
              >
                <span
                  class="flex size-7 items-center justify-center rounded-full bg-emerald-100 dark:bg-emerald-900 text-xs font-semibold text-emerald-700 dark:text-emerald-200"
                  aria-hidden="true"
                >
                  {{ initial(participant) }}
                </span>
                <span class="text-sm font-medium">{{ participant }}</span>
              </li>
            </ul>
          </section>

          <section v-if="protocol.topics.length" class="p-4 sm:p-5">
            <h2 class="flex items-center gap-2 text-lg font-semibold mb-3">
              <i class="pi pi-tags" aria-hidden="true" />
              {{ $t('Topics') }}
            </h2>
            <div class="flex flex-wrap gap-1.5">
              <Tag
                v-for="topic in protocol.topics"
                :key="topic"
                :value="topic"
                severity="secondary"
              />
            </div>
          </section>
        </div>

        <article
          class="prose dark:prose-invert max-w-none prose-h2:border-b prose-h2:border-gray-200 dark:prose-h2:border-gray-700 prose-h2:pb-1"
          v-html="html"
        />
      </div>
    </Card>
  </AppLayout>
</template>
