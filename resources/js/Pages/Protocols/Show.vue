<script setup lang="ts">
import Card from '@/Components/General/Card.vue';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import ParticipantChips from '@/Components/Protocols/ParticipantChips.vue';
import ProtocolContent from '@/Components/Protocols/ProtocolContent.vue';
import ProtocolMeta from '@/Components/Protocols/ProtocolMeta.vue';
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
        <ProtocolMeta :protocol="protocol" />

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
            <ParticipantChips :participants="protocol.participants" />
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

        <ProtocolContent :html="html" />
      </div>
    </Card>
  </AppLayout>
</template>
