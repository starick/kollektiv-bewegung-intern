<script setup lang="ts">
import { router, useForm } from '@inertiajs/vue3';
import { useConfirm } from 'primevue';
import { route } from 'ziggy-js';
import { TimeTable } from '@/Types/time-table';

const props = defineProps<{ timeTable: TimeTable }>();

const confirm = useConfirm();

const reimportForm = useForm<{ file: File | null }>({ file: null });

const onFileSelect = (e: { files?: File[] }) => {
  reimportForm.file = e.files?.[0] ?? null;
};

const onFileClear = () => {
  reimportForm.file = null;
};

const onReimport = () => {
  confirm.require({
    header: 'Reimport Courses',
    message: 'This will delete all existing courses and replace them with the uploaded file. Continue?',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'Reimport',
    rejectLabel: 'Cancel',
    acceptClass: 'p-button-danger',
    accept: () => reimportForm.post(route('time-tables.reimport', { time_table: props.timeTable.id }), {
      forceFormData: true,
      onSuccess: () => reimportForm.reset()
    })
  });
};

const onDelete = () => {
  confirm.require({
    header: 'Delete Timetable',
    message: 'This will permanently delete the timetable and all its courses. Continue?',
    icon: 'pi pi-exclamation-triangle',
    acceptLabel: 'Delete',
    rejectLabel: 'Cancel',
    acceptClass: 'p-button-danger',
    accept: () => router.delete(route('time-tables.destroy', { time_table: props.timeTable.id }))
  });
};
</script>

<template>
  <div class="max-w-lg space-y-6">
    <div class="border-b pb-6">
      <p class="font-medium mb-1">Reimport Courses</p>
      <p class="text-sm text-gray-600 mb-4">
        Deletes all existing courses and imports new ones from a file.
      </p>
      <div class="flex flex-col gap-3">
        <FileUpload
          name="file"
          mode="basic"
          :auto="false"
          :customUpload="false"
          :multiple="false"
          accept=".xlsx,.xls,.csv"
          @select="onFileSelect"
          @clear="onFileClear"
        />
        <div v-if="reimportForm.errors.file" class="text-red-600 text-sm">
          {{ reimportForm.errors.file }}
        </div>
        <Button
          label="Reimport"
          icon="pi pi-upload"
          severity="danger"
          :disabled="!reimportForm.file || reimportForm.processing"
          :loading="reimportForm.processing"
          @click="onReimport"
        />
      </div>
    </div>

    <div>
      <p class="font-medium mb-1">Delete Timetable</p>
      <p class="text-sm text-gray-600 mb-4">
        Permanently deletes this timetable and all linked courses.
      </p>
      <Button
        label="Delete Timetable"
        icon="pi pi-trash"
        severity="danger"
        @click="onDelete"
      />
    </div>
  </div>
</template>
