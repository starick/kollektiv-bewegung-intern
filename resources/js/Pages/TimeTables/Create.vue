<script setup lang="ts">
import Card from '@/Components/General/Card.vue';
import FormSection from '@/Components/Form/FormSection.vue';
import InputLabel from '@/Components/Form/InputLabel.vue';
import PrimaryButton from '@/Components/General/PrimaryButton.vue';
import SecondaryButton from '@/Components/General/SecondaryButton.vue';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { router, useForm } from '@inertiajs/vue3';
import { getWeek, isSunday } from 'date-fns';
import { formatDate, startOfWeek, endOfWeek } from '@/Helpers/date-time-helper';
import { computed, ref, watch } from 'vue';
import { route } from 'ziggy-js';
import { DESIGN_TEMPLATES } from '@/Constants/design-templates';
import DesignTemplateSelect from '@/Components/TimeTables/DesignTemplateSelect.vue';

const currentDate = new Date();

const selectedTemplate = ref(DESIGN_TEMPLATES[0]);

const formData = useForm<{
  year: number;
  week: number;
  file?: File | null;
  display_config?: string;
}>({
  year: currentDate.getFullYear(),
  week: isSunday(currentDate) ? getWeek(currentDate) + 1 : getWeek(currentDate),
  file: null,
  display_config: JSON.stringify(DESIGN_TEMPLATES[0].config)
});

watch(selectedTemplate, (template) => {
  formData.display_config = JSON.stringify(template.config);
});

const onSelect = (e: { files?: File[] }) => {
  formData.file = e.files?.[0] ?? null;
};

const onClear = () => {
  formData.file = null;
};

const submit = () => {
  formData.post(route('time-tables.store'), {
    errorBag: 'createTimeTable',
    preserveScroll: true,
    forceFormData: true
  });
};

const startDate = computed<Date>(() => startOfWeek(formData.year, formData.week));
const endDate = computed<Date>(() => endOfWeek(formData.year, formData.week));

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
            {{ formatDate(startDate) }} - {{ formatDate(endDate) }}
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

        <FormSection class="mb-4">
          <template #title>Design Template</template>
          <template #description>Choose a starting design for this timetable</template>

          <template #form>
            <div class="col-span-6">
              <DesignTemplateSelect v-model="selectedTemplate" />
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
