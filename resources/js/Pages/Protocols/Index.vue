<script setup lang="ts">
import Card from '@/Components/General/Card.vue';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { router } from '@inertiajs/vue3';
import { toRef } from 'vue';
import { Protocol } from '@/Types/protocol';
import useAlert from '@/Composables/use-alerts';
import { MenuItem } from 'primevue';

const props = defineProps<{
  protocols: { data: Protocol[] };
}>();
const protocols = toRef(props, 'protocols');

const alert = useAlert();

const reload = async () => {
  router.reload({
    only: ['protocols'],
    onError: (e) => alert.error('Reload error:', e),
    onSuccess: () => alert.add('reload successful', 'success')
  });
};

const menuItems: MenuItem[] = [
  {
    label: 'Refresh',
    icon: 'pi pi-refresh',
    command: reload
  }
];

const title = 'Protocols Overview';
</script>

<template>
  <AppLayout :title="title">
    <Card :menu-items="menuItems">
      <DataTable
        :value="protocols.data"
        responsiveLayout="scroll"
        sortField="date"
        :sort-order="-1"
        stripedRows
      >
        <template #empty>No protocols yet.</template>
        <Column field="date" header="Protocol" sortable>
          <template #body="{ data }: { data: Protocol }">
            <div class="font-semibold">
              {{ new Date(data.date).toLocaleDateString('de-DE', { timeZone: 'UTC' }) }}
            </div>
            <div v-if="data.description" class="line-clamp-1 text-sm text-gray-600 dark:text-gray-400">
              {{ data.description }}
            </div>
          </template>
        </Column>
        <Column field="uploader" header="Uploaded By" sortable sortField="uploader.name">
          <template #body="{ data }: { data: Protocol }">
            {{ data.uploader?.name }}
          </template>
        </Column>
        <Column field="editor" header="Last Edited By" sortable sortField="editor.name">
          <template #body="{ data }: { data: Protocol }">
            {{ data.editor?.name }}
          </template>
        </Column>
        <Column field="updatedAt" header="Updated At" sortable>
          <template #body="{ data }: { data: Protocol }">
            {{ new Date(data.updatedAt).toLocaleDateString('de-DE') }}
          </template>
        </Column>
      </DataTable>
    </Card>
  </AppLayout>
</template>
