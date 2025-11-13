<script setup lang="ts">
import { ref, onMounted, watch } from 'vue';
import { PropType } from 'vue';
import { TimeTable } from '@/Types/time-table';
import { Course } from '@/Types/course';
import { formatDate } from '@/Helpers/date-time-helper';
import TimeInput from '../Form/TimeInput.vue';
import { DataTableRowEditSaveEvent, useConfirm } from 'primevue';
import CourseCreateModal from './CourseCreateModal.vue';

const emit = defineEmits(['reload', 'row-save', 'row-cancel', 'row-delete', 'row-add']);

const props = defineProps({
  timeTable: Object as PropType<TimeTable>,
  courses: { type: Array as PropType<Course[]>, default: () => [] }
});

const confirm = useConfirm();

const selectedRow = ref<Course | null>(null);
const contextMenu = ref<any>(null);
const contextMenuOptions = ref([
  { label: 'Delete', icon: 'pi pi-trash', command: () => onDeleteRow(selectedRow.value) }
]);

const coursesRef = ref([]);
const editingRows = ref([]);
const showCreateModal = ref(false);
const rowDataBeforeEdit = ref<string | null>(null);

const toggleCreateModal = () => {
  showCreateModal.value = !showCreateModal.value;
};

const onRowContextMenu = (event) => {
  contextMenu.value?.show(event.originalEvent);
};

const onRowEditInit = (event) => {
  rowDataBeforeEdit.value = JSON.stringify(event.data);
};

const onRowEditCancel = (event) => {
  rowDataBeforeEdit.value = null;
};

const onRowEditSave = (event: DataTableRowEditSaveEvent) => {
  if (rowDataBeforeEdit.value === JSON.stringify(event.newData)) {
    if (
      event.data.startTime.compare(event.newData.startTime) === 0 &&
      event.data.endTime.compare(event.newData.endTime) === 0
    ) {
      console.log(
        'No changes detected, cancelling edit',
        event.data.startTime.compare(event.newData.startTime),
        event.data.endTime.compare(event.newData.endTime),
        event.data.endTime,
        event.newData.endTime
      );
      emit('row-cancel', event.data);
      return;
    }
  }

  coursesRef.value[event.index] = event.newData;
  emit('row-save', event.newData);
  showCreateModal.value = false;
};

function onAddRow(row: Course) {
  coursesRef.value = [row, ...coursesRef.value];
  editingRows.value = { ...editingRows.value, [row.id as any]: true };
  emit('row-add', row);
}

function onDeleteRow(row: Course) {
  confirm.require({
    header: 'Confirm deletion',
    message: 'Delete this course?',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'Delete',
    rejectLabel: 'Cancel',
    accept: () => {
      emit('row-delete', row);
      coursesRef.value = coursesRef.value.filter((r) => r.id !== row.id);
      const rows = { ...editingRows.value };
      delete rows[row.id as any];
      editingRows.value = rows;
    }
  });
}

const onReload = () => {
  emit('reload');
};

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
    <ContextMenu ref="contextMenu" :model="contextMenuOptions" @hide="selectedRow = null" />
    <CourseCreateModal
      v-model:visible="showCreateModal"
      @close="toggleCreateModal"
      @save="onAddRow"
    />
    <DataTable
      v-model:editingRows="editingRows"
      v-model:contextMenuSelection="selectedRow"
      :value="coursesRef"
      contextMenu
      editMode="row"
      dataKey="id"
      @rowContextmenu="onRowContextMenu"
      @row-edit-save="onRowEditSave"
      @row-edit-init="onRowEditInit"
      @row-edit-cancel="onRowEditCancel"
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
          <Button icon="pi pi-plus" rounded @click="toggleCreateModal" />
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
