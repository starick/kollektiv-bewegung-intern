<script setup lang="ts">
import Card from '@/Components/General/Card.vue';
import FormSection from '@/Components/General/FormSection.vue';
import InputLabel from '@/Components/Form/InputLabel.vue';
import PrimaryButton from '@/Components/General/PrimaryButton.vue';
import SecondaryButton from '@/Components/General/SecondaryButton.vue';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { router, useForm } from '@inertiajs/vue3';
import { getWeek, isSunday, startOfISOWeek, endOfISOWeek, addWeeks } from 'date-fns';
import { computed } from 'vue';
import { route } from 'ziggy-js';

const currentDate = new Date();

const formData = useForm<{ year: number; week: number; file?: File | null }>({
  year: currentDate.getFullYear(),
  week: isSunday(currentDate) ? getWeek(currentDate) + 1 : getWeek(currentDate),
  file: null
});

const onSelect = (e: { files?: File[] }) => {
  formData.file = e.files?.[0] ?? null;
};

const onClear = () => {
  formData.data = null;
};

const submit = () => {
  formData.post(route('time-tables.store'), {
    errorBag: 'createTimeTable',
    preserveScroll: true,
    forceFormData: true
  });
};

const startDate = computed<Date>(() => {
  const isoWeek1 = startOfISOWeek(new Date(formData.year, 0, 4));
  return addWeeks(isoWeek1, (formData.week ?? 1) - 1);
});

const endDate = computed<Date>(() => endOfISOWeek(startDate.value));

const title = 'Create New Timetable';
</script>

<template>
  <AppLayout :title="title">
    <Card>
      <form @submit.prevent="submit">
        <FormSection class="mb-4">
          <template #title>Time Frame</template>
          <template #description>
            Selected range:
            {{ startDate.toLocaleDateString('de-DE') }} - {{ endDate.toLocaleDateString('de-DE') }}
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
          <template #description>Upload courses as .xls or .xlsx file</template>

          <template #form>
            <FileUpload
              name="data"
              mode="basic"
              :auto="false"
              :customUpload="false"
              :multiple="false"
              accept=".xlsx,.xls,.csv"
              @select="onSelect"
              @clear="onClear"
            />
            <div v-if="formData.errors.file" class="text-red-600 mt-2">
              {{ formData.errors.file }}
            </div>
          </template>
        </FormSection>

        <div class="flex flex-row justify-center mt-8">
          <SecondaryButton
            :class="{ 'opacity-25': formData.processing }"
            :disabled="formData.processing"
            class="mr-4"
            @click="router.get(route('time-tables.index'))"
          >
            Cancel
          </SecondaryButton>
          <PrimaryButton
            :class="{ 'opacity-25': formData.processing }"
            :disabled="formData.processing"
          >
            Create
          </PrimaryButton>
        </div>
      </form>
    </Card>
  </AppLayout>
</template>
