<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { PropType } from 'vue';
import { TimeTable } from '@/Types/time-table';
import { Course } from '@/Types/course';
import { formatDate } from '@/Helpers/date-time-helper';
import TimeInput from '../Form/TimeInput.vue';
import { newCourse } from '@/Helpers/course-mapper';
import { DataTableRowEditSaveEvent } from 'primevue';

const emit = defineEmits(['reload', 'row-save', 'row-cancel', 'row-delete', 'row-add']);

const props = defineProps({
  timeTable: Object as PropType<TimeTable>,
  courses: { type: Array as PropType<Course[]>, default: () => [] }
});

const coursesRef = ref([]);
const editingRows = ref([]);

const onRowEditSave = (event: DataTableRowEditSaveEvent) => {
  if (JSON.stringify(event.data) === JSON.stringify(event.newData)) {
    return;
  }

  coursesRef.value[event.index] = event.newData;
  emit('row-save', event.newData);
};

function onAddRow() {
  const row = newCourse();
  coursesRef.value = [row, ...coursesRef.value];
  editingRows.value = { ...editingRows.value, [row.id as any]: true };
  emit('row-add', row);
}

function onDeleteRow(row: Course) {
  emit('row-delete', row);
  coursesRef.value = coursesRef.value.filter((r) => r.id !== row.id);
  const ed = { ...editingRows.value };
  delete ed[row.id as any];
  editingRows.value = ed;
}

watch(
  () => props.courses,
  (list) => {
    coursesRef.value = Array.isArray(list) ? [...list] : [];
  },
  { immediate: true }
);
</script>

<template>
  <div class="card">
    <DataTable
      v-model:editingRows="editingRows"
      :value="coursesRef"
      editMode="row"
      dataKey="id"
      @row-edit-save="onRowEditSave"
      :pt="{
        table: { style: 'min-width: 50rem' },
        column: {
          bodycell: ({ state }) => ({
            style: state['d_editing'] && 'padding-top: 0.75rem; padding-bottom: 0.75rem'
          })
        }
      }"
    >
      <template #header>
        <div class="flex flex-wrap items-center justify-end gap-6">
          <Button icon="pi pi-refresh" rounded @click="onReload" />
          <Button icon="pi pi-plus" rounded @click="onAddRow" />
        </div>
      </template>
      <Column field="date" header="Date" sortable>
        <template #editor="{ data, field }">
          <Calendar v-model="data[field]" dateFormat="dd.mm.yy" showIcon />
        </template>
        <template #body="{ data }: { data: Course }">
          {{ formatDate(data.date) }}
        </template>
      </Column>

      <Column field="startTime" header="Start" sortable>
        <template #editor="{ data }: { data: Course }">
          <TimeInput v-model="data.startTime" />
        </template>
        <template #body="{ data }: { data: Course }">
          {{ data.startTime }}
        </template>
      </Column>

      <Column field="endTime" header="End" sortable>
        <template #editor="{ data, field }">
          <TimeInput v-model="data[field]" />
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
        :rowEditor="true"
        style="width: 10%; min-width: 8rem"
        bodyStyle="text-align:center"
      ></Column>
    </DataTable>
  </div>
</template>
