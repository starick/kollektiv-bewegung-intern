<script setup lang="ts">
import { TimeTable } from '@/Types/time-table';
import { MenuItem } from 'primevue/menuitem';
import { defineProps, defineEmits } from 'vue';
import { route } from 'ziggy-js';
import { router } from '@inertiajs/vue3';
import useAlert from '@/Hooks/alert';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import Card from '@/Components/General/Card.vue';

const props = defineProps<{ timeTable: { data: TimeTable }; editing: boolean }>();

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
        :value="timeTable.data.courses"
        responsiveLayout="scroll"
        sortField="createdAt"
        selectionMode="single"
        stripedRows
      >
        <Column field="date" header="Date" sortable>
          <template #body="{ data }: { data: any }">
            {{
              new Date(data.startTime).toLocaleDateString('de-DE', {
                weekday: 'short',
                day: '2-digit',
                month: 'short'
              })
            }}
          </template>
        </Column>
        <Column field="startTime" header="Start" sortable>
          <template #body="{ data }: { data: any }">
            {{
              new Date(data.startTime).toLocaleTimeString('de-DE', {
                hour: '2-digit',
                minute: '2-digit'
              })
            }}
          </template>
        </Column>
        <Column field="endTime" header="End" sortable>
          <template #body="{ data }: { data: any }">
            {{
              new Date(data.endTime).toLocaleTimeString('de-DE', {
                hour: '2-digit',
                minute: '2-digit'
              })
            }}
          </template>
        </Column>
        <Column field="name" header="Title" sortable />
        <Column field="instructor" header="Instructor" sortable />
        <Column field="place" header="Place" sortable />
      </DataTable>
    </Card>
  </AppLayout>
</template>
