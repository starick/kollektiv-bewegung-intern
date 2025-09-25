<script setup lang="ts">
import Card from '@/Components/General/Card.vue';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { route } from 'ziggy-js';
import { router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Column, DataTable } from 'primevue';
import { MenuItem } from 'primevue/menuitem';
import { TimeTable } from '@/Types/time-table';

const menuItems: MenuItem[] = [
  {
    label: 'Create New',
    icon: 'pi pi-plus',
    command: () => router.get(route('time-tables.create'))
  }
];

const timeTables = ref<TimeTable[]>([]);

const title = 'Timetables Overview';
</script>

<template>
  <AppLayout :title="title">
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ title }}
      </h2>
    </template>

    <Card :menu-items="menuItems">
      <DataTable :value="timeTables" responsiveLayout="scroll">
        <Column field="createdAt" header="Created At" sortable>
          <template #body="slotProps">
            {{ new Date(slotProps.data.createdAt).toLocaleDateString('de-DE') }}
          </template>
        </Column>
        <Column field="year" header="Year" sortable>
          <template #body="slotProps">
            {{ slotProps.data.year }}
          </template>
        </Column>
        <Column field="week" header="Week" sortable>
          <template #body="slotProps">
            {{ slotProps.data.week }}
          </template>
        </Column>

        <Column field="creator" header="Author" sortable>
          <template #body="slotProps">
            {{ slotProps.data.creator.name }}
          </template>
        </Column>
      </DataTable>
    </Card>
  </AppLayout>
</template>
