<script setup lang="ts">
import { TimeTable } from '@/Types/time-table';
import { MenuItem } from 'primevue/menuitem';
import { defineProps, defineEmits } from 'vue';
import { route } from 'ziggy-js';
import { router } from '@inertiajs/vue3';
import useAlert from '@/Hooks/alert';

const props = defineProps<{ timeTable: TimeTable; editing: boolean }>();

const emit = defineEmits([]);

const alert = useAlert();

const reload = async () => {
  router.reload({
    only: ['timeTables'],
    onError: (e) => alert.error('Reload error:', e),
    onSuccess: () => alert.add('reload successful', 'success')
  });
};

const menuItems: MenuItem[] = [
  {
    label: 'Add Course',
    icon: 'pi pi-plus',
    command: () => console.log('not implemented yet')
    // TODO implement
  },
  {
    label: 'Refresh',
    icon: 'pi pi-refresh',
    command: reload
  }
];

const title = 'Show Timetable';
</script>

<template>
  <AppLayout :title="title">
    <Card :menu-items="menuItems">
      <DataTable
        :value="timeTable.courses"
        responsiveLayout="scroll"
        sortField="createdAt"
        selectionMode="single"
        stripedRows
      >
        <Column field="start_date" header="Start Date" sortable>
          <template #body="{ data }: { data: any }">
            {{ new Date(data.start_date).toLocaleString('de-DE') }}
          </template>
        </Column>
        <Column field="end_date" header="End Date" sortable>
          <template #body="{ data }: { data: any }">
            {{ new Date(data.end_date).toLocaleString('de-DE') }}
          </template>
        </Column>
        <Column field="title" header="Title" sortable />
        <Column field="instructor" header="Instructor" sortable />
        <Column field="place" header="Place" sortable />
      </DataTable>
    </Card>
  </AppLayout>
</template>
