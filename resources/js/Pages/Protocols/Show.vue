<script setup lang="ts">
import Card from '@/Components/General/Card.vue';
import InfoField from '@/Components/General/InfoField.vue';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { route } from 'ziggy-js';
import { router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { Protocol } from '@/Types/protocol';
import { MenuItem, useConfirm } from 'primevue';

const props = defineProps<{
  protocol: Protocol;
  html: string;
  can: { delete: boolean };
}>();

const confirm = useConfirm();

const formattedDate = computed(() =>
  new Date(props.protocol.date).toLocaleDateString('de-DE', { timeZone: 'UTC' })
);

const confirmDelete = () => {
  confirm.require({
    header: 'Delete protocol',
    message: `Delete the protocol from ${formattedDate.value}? This cannot be undone.`,
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'Delete',
    rejectLabel: 'Cancel',
    acceptClass: 'p-button-danger',
    accept: () => router.delete(route('protocols.destroy', { protocol: props.protocol.id }))
  });
};

const menuItems: MenuItem[] = [
  {
    label: 'Back to Overview',
    icon: 'pi pi-arrow-left',
    command: () => router.get(route('protocols.index'))
  },
  {
    label: 'Delete',
    icon: 'pi pi-trash',
    visible: props.can.delete,
    command: confirmDelete
  }
];

const title = computed(() => `Protocol ${formattedDate.value}`);
</script>

<template>
  <AppLayout :title="title">
    <Card :menu-items="menuItems">
      <div class="mb-6">
        <h1 class="text-2xl font-semibold">{{ formattedDate }}</h1>
        <p v-if="protocol.description" class="text-gray-600 dark:text-gray-400">
          {{ protocol.description }}
        </p>
      </div>

      <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-6 text-sm">
        <InfoField title="Uploaded">
          {{ protocol.uploader?.name ?? '–' }} ({{ new Date(protocol.createdAt).toLocaleString('de-DE') }})
        </InfoField>
        <InfoField title="Last Edited">
          <template v-if="protocol.editor">
            {{ protocol.editor.name }} ({{ new Date(protocol.updatedAt).toLocaleString('de-DE') }})
          </template>
          <template v-else>–</template>
        </InfoField>
      </dl>

      <article class="prose dark:prose-invert max-w-none" v-html="html" />
    </Card>
  </AppLayout>
</template>
