<script setup lang="ts">
import { TimeTable } from '@/Types/time-table';
import { MenuItem } from 'primevue/menuitem';
import { defineProps, defineEmits, ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import { route } from 'ziggy-js';
import useAlert from '@/Hooks/alert';

import AppLayout from '@/Components/Layout/AppLayout.vue';
import Card from '@/Components/General/Card.vue';
import PrimaryButton from '@/Components/General/PrimaryButton.vue';
import TimeTableInfo from './Partials/TimeTableInfo.vue';

type Course = {
  id: number | string;
  name: string;
  instructor: string;
  place: string;
  startTime: string | Date;
  endTime: string | Date;
};

const props = defineProps<{
  timeTable: { data: TimeTable & { courses: Course[] } };
}>();

const emit = defineEmits<{
  (e: 'row-save', payload: { index: number; oldData: Course; newData: Course }): void;
  (e: 'row-cancel', payload: { index: number; data: Course }): void;
  (e: 'row-init', payload: { index: number; data: Course }): void;
}>();

const editingRows = ref<any[]>([]);
const alert = useAlert();

const reload = async () => {
  router.reload({
    only: ['timeTables'],
    onError: (e) => alert.error('Reload error:', e),
    onSuccess: () => alert.add('Reload successful', 'success')
  });
};

const title = 'Show Timetable';

const toDate = (v: string | Date) => (v instanceof Date ? v : new Date(v));
const toIso = (d: Date) => new Date(d.getTime() - d.getTimezoneOffset() * 60000).toISOString();

const onRowEditInit = (e: { index: number; data: Course }) => {
  emit('row-init', { index: e.index, data: e.data });
};

const onRowEditCancel = (e: { index: number; data: Course }) => {
  emit('row-cancel', { index: e.index, data: e.data });
};

const onRowEditSave = (e: { index: number; data: Course; newData: Course }) => {
  const newData: Course = {
    ...e.newData,
    startTime:
      typeof e.newData.startTime === 'string'
        ? e.newData.startTime
        : toIso(e.newData.startTime as Date),
    endTime:
      typeof e.newData.endTime === 'string' ? e.newData.endTime : toIso(e.newData.endTime as Date)
  };

  emit('row-save', { index: e.index, oldData: e.data, newData });
  alert.add('Row saved (not yet persisted — parent should handle the save).', 'info');
};
</script>

<template>
  <AppLayout :title="title">
    <Tabs value="0">
      <TabList>
        <Tab value="0">Info</Tab>
        <Tab value="1">Courses</Tab>
        <Tab value="2">Display</Tab>
      </TabList>
      <TabPanels>
        <TabPanel value="0">
          <TimeTableInfo :timeTable="timeTable.data" />
        </TabPanel>
        <TabPanel value="1">
          <p class="m-0">
            Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque
            laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi
            architecto beatae vitae dicta sunt explicabo. Nemo enim ipsam voluptatem quia voluptas
            sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione
            voluptatem sequi nesciunt. Consectetur, adipisci velit, sed quia non numquam eius modi.
          </p>
        </TabPanel>
        <TabPanel value="2">
          <p class="m-0">
            At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium
            voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint
            occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt
            mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et
            expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque
            nihil impedit quo minus.
          </p>
        </TabPanel>
      </TabPanels>
    </Tabs>
    <Card>
      <DataTable
        v-model:editingRows="editingRows"
        :value="timeTable.data.courses"
        dataKey="id"
        :pt="{
          table: { style: 'min-width: 50rem' },
          column: {
            bodycell: ({ state }) => ({
              style: state['d_editing'] && 'padding-top: 0.75rem; padding-bottom: 0.75rem'
            })
          }
        }"
        responsiveLayout="scroll"
        sortField="startTime"
        editMode="row"
        stripedRows
        @row-edit-init="onRowEditInit"
        @row-edit-cancel="onRowEditCancel"
        @row-edit-save="onRowEditSave"
      >
        <template #header>
          <div class="flex flex-wrap items-center justify-between gap-2">
            <span class="text-xl font-bold">Courses</span>
            <div class="flex flex-row gap-6">
              <Button icon="pi pi-plus" rounded />
            </div>
          </div>
        </template>

        <!-- Date (derived from startTime) — read-only display -->
        <Column field="startTime" header="Date" sortable>
          <template #body="{ data }: { data: Course }">
            {{
              new Date(data.startTime).toLocaleDateString('de-DE', {
                weekday: 'short',
                day: '2-digit',
                month: 'short'
              })
            }}
          </template>
        </Column>

        <!-- Start time -->
        <Column field="startTime" header="Start" sortable>
          <template #editor="{ data, field }">
            <Calendar
              v-model="data[field]"
              :model-value="toDate(data[field])"
              timeOnly
              hourFormat="24"
              showIcon
              @update:modelValue="(v) => (data[field] = v)"
            />
          </template>
          <template #body="{ data }: { data: Course }">
            {{
              new Date(data.startTime).toLocaleTimeString('de-DE', {
                hour: '2-digit',
                minute: '2-digit'
              })
            }}
          </template>
        </Column>

        <!-- End time -->
        <Column field="endTime" header="End" sortable>
          <template #editor="{ data, field }">
            <Calendar
              v-model="data[field]"
              :model-value="toDate(data[field])"
              timeOnly
              hourFormat="24"
              showIcon
              @update:modelValue="(v) => (data[field] = v)"
            />
          </template>
          <template #body="{ data }: { data: Course }">
            {{
              new Date(data.endTime).toLocaleTimeString('de-DE', {
                hour: '2-digit',
                minute: '2-digit'
              })
            }}
          </template>
        </Column>

        <!-- Title -->
        <Column field="name" header="Title" sortable>
          <template #editor="{ data, field }">
            <InputText v-model="data[field]" />
          </template>
        </Column>

        <!-- Instructor -->
        <Column field="instructor" header="Instructor" sortable>
          <template #editor="{ data, field }">
            <InputText v-model="data[field]" />
          </template>
        </Column>

        <!-- Place -->
        <Column field="place" header="Place" sortable>
          <template #editor="{ data, field }">
            <InputText v-model="data[field]" />
          </template>
        </Column>

        <!-- Row editor controls (only when editing=true) -->
        <Column
          :rowEditor="true"
          style="width: 10%; min-width: 8rem"
          bodyStyle="text-align:center"
        />
      </DataTable>
    </Card>
  </AppLayout>
</template>
