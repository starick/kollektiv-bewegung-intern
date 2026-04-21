<script setup lang="ts">
import Card from '@/Components/General/Card.vue';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { route } from 'ziggy-js';
import { router } from '@inertiajs/vue3';
import { toRef } from 'vue';
import { TimeTable } from '@/Types/time-table';
import useAlert from '@/Composables/use-alerts';
import { MenuItem } from 'primevue';

const props = defineProps<{ timeTables: { data: TimeTable[] } }>();
const timeTables = toRef(props, 'timeTables');

const alert = useAlert();

const reload = async () => {
  router.reload({
    only: ['timeTables'],
    onError: (e) => alert.error('Reload error:', e),
    onSuccess: () => alert.add('reload successful', 'success')
  });
};

const rowClick = async (e: any) => {
  router.get(route('time-tables.show', { time_table: e.data.id }));
};

const menuItems: MenuItem[] = [
  {
    label: 'Create New',
    icon: 'pi pi-plus',
    command: () => router.get(route('time-tables.create'))
  },
  {
    label: 'Refresh',
    icon: 'pi pi-refresh',
    command: reload
  }
];

const title = 'Timetables Overview';
</script>

<template>
  <AppLayout :title="title">
    <Card :menu-items="menuItems">
      <DataTable
        :value="timeTables.data"
        responsiveLayout="scroll"
        sortField="createdAt"
        :sort-order="-1"
        selectionMode="single"
        stripedRows
        @row-click="rowClick"
      >
        <Column field="createdAt" header="Created At" sortable>
          <template #body="{ data }: { data: TimeTable }">
            {{ new Date(data.createdAt).toLocaleDateString('de-DE', { timeZone: 'UTC' }) }}
          </template>
        </Column>
        <Column field="year" header="Year/Week" sortable>
          <template #body="{ data }: { data: TimeTable }">
            {{ data.year }} / {{ data.week }}
          </template>
        </Column>
        <Column field="coursesCount" header="Courses" sortable>
          <template #body="{ data }: { data: TimeTable }">
            {{ data.coursesCount }}
          </template>
        </Column>
        <Column field="creator" header="Author" sortable>
          <template #body="{ data }: { data: TimeTable }">
            {{ data.creator?.name }}
          </template>
        </Column>
      </DataTable>
    </Card>
  </AppLayout>
</template>
