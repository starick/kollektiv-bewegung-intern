<script setup lang="ts">
import { defineProps, defineEmits, ref, PropType, watch } from 'vue';
import { TimeTable } from '@/Types/time-table';
import { Course } from '@/Types/course';
import { formatDate, formatTime } from '@/Helpers/date-time-helper';

const props = defineProps({
  timeTable: Object as PropType<TimeTable>,
  courses: Array<Course>,
  editable: { type: Boolean, default: false }
});

const courses = ref<Course[]>(props.courses || []);

const emit = defineEmits(['reload', 'row-save', 'row-cancel', 'row-delete', 'row-add']);

const editingRows = ref<Record<string | number, boolean>>({});

const originalRows = new Map<string | number, Course>();

function onReload() {
  emit('reload');
}

function onRowEditInit(e: any) {
  console.log({ e });
  originalRows.set(e.data.id as any, { ...e.data });
}

function onRowEditSave(e: { data: Course; index: number }) {
  emit('row-save', e.data);
  originalRows.delete(e.data.id as any);
}

function onRowEditCancel(e: { data: Course; index: number }) {
  const original = originalRows.get(e.data.id as any);
  if (original) {
    Object.assign(e.data, original);
    originalRows.delete(e.data.id as any);
  }
  emit('row-cancel', e.data);
}

function makeEmptyCourse(): Course {
  const now = new Date();
  const start = new Date(now);
  start.setMinutes(0, 0, 0);
  const end = new Date(start);
  end.setHours(end.getHours() + 1);

  const tempId = -Date.now();

  return {
    id: tempId,
    date: now,
    startTime: start,
    endTime: end,
    name: '',
    instructor: '',
    place: ''
  } as unknown as Course;
}

function onAddRow() {
  const row = makeEmptyCourse();
  courses.value = [row, ...courses.value];
  editingRows.value = { ...editingRows.value, [row.id as any]: true };
  emit('row-add', row);
}

function onDeleteRow(row: Course) {
  emit('row-delete', row);
  courses.value = courses.value.filter((r) => r.id !== row.id);
  const ed = { ...editingRows.value };
  delete ed[row.id as any];
  editingRows.value = ed;
}

watch(
  () => props.courses,
  (list) => {
    courses.value = Array.isArray(list) ? [...list] : [];
  },
  { immediate: true }
);
</script>

<template>
  <DataTable
    :value="courses"
    dataKey="id"
    v-model:editingRows="editingRows"
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
    @row-edit-save="onRowEditSave"
    @row-edit-cancel="onRowEditCancel"
  >
    <template #header>
      <div class="flex flex-wrap items-center justify-end gap-6">
        <Button icon="pi pi-refresh" @click="onReload" rounded />
        <Button icon="pi pi-plus" rounded :disabled="!editable" @click="onAddRow" />
      </div>
    </template>

    <Column field="date" header="Date" sortable>
      <template #editor="{ data, field }">
        <Calendar v-model="data[field]" dateFormat="yy-mm-dd" showIcon />
      </template>
      <template #body="{ data }: { data: Course }">
        {{ formatDate(data.date) }}
      </template>
    </Column>

    <Column field="startTime" header="Start" sortable>
      <template #editor="{ data, field }">
        <Calendar v-model="data[field]" timeOnly hourFormat="24" showIcon />
      </template>
      <template #body="{ data }: { data: Course }">
        {{ data.startTime }}
      </template>
    </Column>

    <Column field="endTime" header="End" sortable>
      <template #editor="{ data, field }">
        <Calendar v-model="data[field]" timeOnly hourFormat="24" showIcon />
      </template>
      <template #body="{ data }: { data: Course }">
        {{ data.endTime }}
      </template>
    </Column>

    <Column field="name" header="Title" sortable>
      <template #editor="{ data, field }">
        <InputText v-model="data[field]" />
      </template>
    </Column>

    <Column field="instructor" header="Instructor" sortable>
      <template #editor="{ data, field }">
        <InputText v-model="data[field]" />
      </template>
    </Column>

    <Column field="location" header="Location" sortable>
      <template #editor="{ data, field }">
        <InputText v-model="data[field]" />
      </template>
    </Column>
    <Column
      :rowEditor="editable"
      style="width: 10%; min-width: 8rem"
      bodyStyle="text-align:center"
    />
    <Column v-if="editable" style="width: 6rem; text-align: center">
      <template #body="{ data }">
        <Button icon="pi pi-trash" severity="danger" text rounded @click="onDeleteRow(data)" />
      </template>
    </Column>
  </DataTable>
</template>
