<script setup lang="ts">
import Card from '@/Components/General/Card.vue';
import FormSection from '@/Components/Form/FormSection.vue';
import InputLabel from '@/Components/Form/InputLabel.vue';
import PrimaryButton from '@/Components/General/PrimaryButton.vue';
import SecondaryButton from '@/Components/General/SecondaryButton.vue';
import AppLayout from '@/Components/Layout/AppLayout.vue';
import { formatDate } from '@/Helpers/date-time-helper';
import DesignTemplateSelect from '@/Components/TimeTables/DesignTemplateSelect.vue';
import { timetableCreateModel } from '@/Models/TimeTables/timetable-create-model';

const {
  currentDate,
  selectedTemplate,
  formData,
  weekDetected,
  detectingWeek,
  startDate,
  endDate,
  title,
  onFileSelect,
  onFileClear,
  submit,
  cancel
} = timetableCreateModel();
</script>

<template>
  <AppLayout :title="title">
    <Card>
      <form @submit.prevent="submit">
        <FormSection class="mb-4">
          <template #title>{{ $t('File Upload') }}</template>
          <template #description>{{ $t('Upload courses as .xls or .xlsx file') }}</template>

          <template #form>
            <FileUpload
              name="data"
              mode="basic"
              :auto="false"
              :customUpload="false"
              :multiple="false"
              accept=".xlsx,.xls,.csv"
              @select="onFileSelect"
              @clear="onFileClear"
            />
            <div v-if="formData.errors.file" class="text-red-600 mt-2">
              {{ formData.errors.file }}
            </div>
          </template>
        </FormSection>

        <FormSection class="mb-4">
          <template #title>{{ $t('Time Frame') }}</template>
          <template #description>
            {{
              $t('Selected range: :start - :end', {
                start: formatDate(startDate),
                end: formatDate(endDate)
              })
            }}
          </template>

          <template #form>
            <div v-if="detectingWeek" class="col-span-6">{{ $t('Reading week from file…') }}</div>
            <div v-else-if="weekDetected" class="col-span-6">
              {{ formData.week }} / {{ formData.year }}
              <span class="text-gray-500 dark:text-gray-400 ml-1">{{
                $t('(detected from file)')
              }}</span>
            </div>
            <div v-else class="col-span-6 flex flex-row">
              <div>
                <InputLabel for="year" :value="$t('Year')" />
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
                <InputLabel for="week" :value="$t('Week')" />
                <InputNumber id="week" v-model="formData.week" :min="1" :max="53" show-buttons />
              </div>
            </div>
            <div v-if="formData.file && !detectingWeek && !weekDetected" class="col-span-6">
              {{ $t('Could not detect the week from this file, please select it manually.') }}
            </div>
          </template>
        </FormSection>

        <FormSection>
          <template #title>{{ $t('Design Template') }}</template>
          <template #description>{{ $t('Choose a starting design for this timetable') }}</template>

          <template #form>
            <div class="col-span-6">
              <DesignTemplateSelect v-model="selectedTemplate" />
            </div>
          </template>
        </FormSection>

        <div class="flex flex-row justify-center mt-8">
          <SecondaryButton
            :class="{ 'opacity-25': formData.processing }"
            :disabled="formData.processing"
            class="mr-4"
            @click="cancel"
          >
            {{ $t('Cancel') }}
          </SecondaryButton>
          <PrimaryButton
            :class="{ 'opacity-25': formData.processing }"
            :disabled="formData.processing"
          >
            {{ $t('Create') }}
          </PrimaryButton>
        </div>
      </form>
    </Card>
  </AppLayout>
</template>
