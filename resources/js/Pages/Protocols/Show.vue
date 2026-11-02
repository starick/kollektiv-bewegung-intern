<script setup lang="ts">
import Card from '@/Components/General/Card.vue';
import InfoField from '@/Components/General/InfoField.vue';
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

      <div class="mb-6">
        <h1 class="text-2xl font-semibold">{{ formattedDate }}</h1>
        <p v-if="protocol.description" class="text-gray-600 dark:text-gray-400">
          {{ protocol.description }}
        </p>
        <div v-if="protocol.topics.length" class="flex flex-wrap gap-1 mt-2">
          <Tag v-for="topic in protocol.topics" :key="topic" :value="topic" severity="secondary" />
        </div>
      </div>

      <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6 text-sm">
        <InfoField :title="$t('Uploaded')">
          {{ protocol.uploader?.name ?? '–' }} ({{
            new Date(protocol.createdAt).toLocaleString('de-DE')
          }})
        </InfoField>
        <InfoField :title="$t('Last Edited')">
          <template v-if="protocol.editor">
            {{ protocol.editor.name }} ({{ new Date(protocol.updatedAt).toLocaleString('de-DE') }})
          </template>
          <template v-else>–</template>
        </InfoField>
      </dl>

      <section v-if="protocol.participants.length" class="mb-6">
        <h2 class="text-sm font-semibold text-gray-600 dark:text-gray-400 mb-1">
          {{ $t('Participants') }} ({{ protocol.participants.length }})
        </h2>
        <p>{{ protocol.participants.join(', ') }}</p>
      </section>

      <article class="prose dark:prose-invert max-w-none" v-html="html" />
    </Card>
  </AppLayout>
</template>
