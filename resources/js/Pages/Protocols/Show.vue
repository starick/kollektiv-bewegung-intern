<script setup lang="ts">
import Card from '@/Components/General/Card.vue';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import ProtocolContent from '@/Components/Protocols/ProtocolContent.vue';
import ProtocolHeader from '@/Components/Protocols/ProtocolHeader.vue';
import ProtocolMeta from '@/Components/Protocols/ProtocolMeta.vue';
import ShareDialog from '@/Components/Protocols/ShareDialog.vue';
import { route } from 'ziggy-js';
import { router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import { Protocol } from '@/Types/protocol';
import { Audit } from '@/Types/audit';
import AuditTrail from '@/Components/Audits/AuditTrail.vue';
import { MenuItem, useConfirm } from 'primevue';
import { trans } from '@/Composables/use-translations';

const props = defineProps<{
  protocol: Protocol;
  html: string;
  audits: Audit[];
  shareUrl: string | null;
  can: { update: boolean; delete: boolean };
}>();

const confirm = useConfirm();
const shareVisible = ref(false);

const formattedDate = computed(() =>
  new Date(props.protocol.date).toLocaleDateString('de-DE', { timeZone: 'UTC' })
);

const auditLabels = {
  date: trans('Date'),
  description: trans('Description'),
  participants: trans('Participants'),
  topics: trans('Topics'),
  text: trans('Content')
};

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
          v-if="shareUrl || can.update"
          :label="shareUrl ? $t('Shared') : $t('Share')"
          icon="pi pi-share-alt"
          :severity="shareUrl ? 'success' : undefined"
          text
          @click="shareVisible = true"
        />
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

        <ProtocolHeader :protocol="protocol" />

        <ProtocolContent v-if="html.trim()" :html="html" />
        <div
          v-else
          class="rounded-xl border border-dashed border-gray-300 dark:border-gray-600 p-8 text-center text-gray-500 dark:text-gray-400"
        >
          <i class="pi pi-file-edit text-2xl mb-2" aria-hidden="true" />
          <p>{{ $t('This protocol has no content yet.') }}</p>
          <Button
            v-if="can.update"
            class="mt-3"
            :label="$t('Write protocol')"
            icon="pi pi-pencil"
            text
            @click="router.get(route('protocols.edit', { protocol: protocol.id }))"
          />
        </div>

        <details
          v-if="audits.length"
          class="group mt-10 rounded-xl border border-gray-200 dark:border-gray-700"
        >
          <summary
            class="flex cursor-pointer list-none items-center gap-2 p-4 sm:px-5 text-lg font-semibold"
          >
            <i class="pi pi-history" aria-hidden="true" />
            {{ $t('History') }}
            <span
              class="rounded-full bg-gray-200 dark:bg-gray-700 px-2 py-0.5 text-xs font-medium text-gray-700 dark:text-gray-200"
            >
              {{ audits.length }}
            </span>
            <i
              class="pi pi-chevron-down ml-auto text-sm transition-transform group-open:rotate-180"
              aria-hidden="true"
            />
          </summary>
          <div class="px-4 pb-5 sm:px-5 pt-2">
            <AuditTrail :audits="audits" :labels="auditLabels" />
          </div>
        </details>
      </div>
    </Card>

    <ShareDialog
      v-model:visible="shareVisible"
      :protocol-id="protocol.id"
      :share-url="shareUrl"
      :can-manage="can.update"
    />
  </AppLayout>
</template>
