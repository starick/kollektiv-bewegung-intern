<script setup lang="ts">
import Card from '@/Components/General/Card.vue';
import FormSection from '@/Components/General/FormSection.vue';
import InputLabel from '@/Components/General/InputLabel.vue';
import PrimaryButton from '@/Components/General/PrimaryButton.vue';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { AddYearsOptions, endOfWeek, getWeek, isSunday, lastDayOfWeek, parse } from 'date-fns';
import { FileUploadUploaderEvent } from 'primevue';
import { computed } from 'vue';
import { route } from 'ziggy-js';

const currentDate = new Date();

const formData = useForm<{ year: number; week: number; data?: File }>({
  year: currentDate.getFullYear(),
  week: isSunday(currentDate) ? getWeek(currentDate) + 1 : getWeek(currentDate)
});

const submit = () => {
  formData.post(route('time-tables.store'), {
    errorBag: 'createTimeTable',
    preserveScroll: true,
    forceFormData: true
  });
};

const upload = (event: FileUploadUploaderEvent) => {
  formData.data = event.files?.[0];
};

const startDate = computed(() => {
  return parse(formData.week.toString(), 'I', new Date().setFullYear(formData.year));
});

const endDate = computed(() => {
  return lastDayOfWeek(startDate.value);
});

const title = 'Create New Timetable';
</script>

<template>
  <AppLayout :title="title">
    <Card>
      <form @submit.prevent="submit">
        <FormSection class="mb-4">
          <template #title> Time Frame </template>
          <template #description
            >Selected range: {{ startDate.toLocaleDateString('de-DE') }} -
            {{ endDate.toLocaleDateString('de-DE') }}
          </template>
          <template #form>
            <div class="flex flex-row">
              <div>
                <InputLabel for="year" value="Year" />
                <InputNumber
                  id="year"
                  v-model="formData.year"
                  :use-grouping="false"
                  show-buttons
                  :min="2000"
                  :max="currentDate.getFullYear() + 1"
                />
              </div>
              <div class="ml-4">
                <InputLabel for="week" value="Week" />
                <InputNumber id="week" v-model="formData.week" :min="1" :max="53" show-buttons />
              </div>
            </div>
          </template>
        </FormSection>
        <FormSection>
          <template #title>File Upload</template>
          <template #description> Upload courses as .xls or .xlsx file </template>
          <template #form>
            <div v-if="formData.data?.name">{{ formData.data.name }}</div>
            <FileUpload
              name="file"
              mode="basic"
              customUpload
              :auto="true"
              @uploader="upload"
              accept=".xlsx,.xls,.csv"
            />
          </template>
        </FormSection>
        <div class="my-4 py-2 rounded border-gray-300 dark:border-gray-700">
          <h2 class="text-lg mb-2 font-bold">File Upload</h2>
        </div>

        <PrimaryButton
          :class="{ 'opacity-25': formData.processing }"
          :disabled="formData.processing"
        >
          Create
        </PrimaryButton>
      </form>
    </Card>
  </AppLayout>
</template>
