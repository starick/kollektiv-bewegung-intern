<script setup lang="ts">
import { TimeTable } from '@/Types/time-table';
import { defineProps, defineEmits, ref } from 'vue';
import Card from '@/Components/General/Card.vue';
import { Course } from '@/Types/course';
import { formatDate, formatTime } from '@/Helpers/date-time-helper';

const props = defineProps<{ timeTable: TimeTable }>();

const emit = defineEmits(['reload', 'row-save', 'row-cancel', 'row-init']);

const courses = ref<Course[]>(props.timeTable.courses || []);
const isEditMode = ref<boolean>(false);

const toggleEdit = () => {
  isEditMode.value = !isEditMode.value;
};

const saveChanges = () => {
  // TODO implement
  isEditMode.value = false;
};

const discardChanges = () => {
  // TODO implement
  isEditMode.value = false;
};

const onReload = () => emit('reload');
</script>

<template>
  <DataTable
    :value="courses"
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
  >
    <template #header>
      <div class="flex flex-wrap items-center justify-end gap-6">
        <Button icon="pi pi-refresh" @click="onReload" rounded />
        <Button icon="pi pi-plus" rounded />
      </div>
    </template>

    <Column field="date" header="Date" sortable>
      <template #body="{ data }: { data: Course }">
        {{ formatDate(data.date) }}
      </template>
    </Column>

    <Column field="startTime" header="Start" sortable>
      <template #editor="{ data, field }">
        <Calendar
          v-model="data[field]"
          timeOnly
          hourFormat="24"
          showIcon
          @update:modelValue="(v) => (data[field] = v)"
        />
      </template>
      <template #body="{ data }: { data: Course }">
        {{ formatTime(data.startTime) }}
      </template>
    </Column>

    <Column field="endTime" header="End" sortable>
      <template #editor="{ data, field }">
        <Calendar
          v-model="data[field]"
          timeOnly
          hourFormat="24"
          showIcon
          @update:modelValue="(v) => (data[field] = v)"
        />
      </template>
      <template #body="{ data }: { data: Course }">
        {{ formatTime(data.endTime) }}
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
    <Column field="place" header="Place" sortable>
      <template #editor="{ data, field }">
        <InputText v-model="data[field]" />
      </template>
    </Column>

    <Column :rowEditor="true" style="width: 10%; min-width: 8rem" bodyStyle="text-align:center" />
  </DataTable>
</template>
