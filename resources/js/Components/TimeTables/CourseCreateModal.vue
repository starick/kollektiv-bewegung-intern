<script setup lang="ts">
import { ref, watch } from 'vue';
import { Course } from '@/Types/course';
import InfoField from '../General/InfoField.vue';
import TextInput from '../Form/TextInput.vue';
import Card from '../General/Card.vue';
import TimeInput from '../Form/TimeInput.vue';
import { newCourse } from '@/Helpers/course-mapper';
import { trans } from '@/Composables/use-translations';

const visible = defineModel<boolean>('visible');
const emit = defineEmits(['save', 'close']);

const course = ref<Course>(newCourse());
const errors = ref<{ name?: string; instructor?: string }>({});

watch(visible, (val) => {
  if (val) {
    course.value = newCourse();
    errors.value = {};
  }
});

function validate(): boolean {
  errors.value = {};
  if (!course.value.name.trim()) {
    errors.value.name = trans('Title is required.');
  }
  if (!course.value.instructor.trim()) {
    errors.value.instructor = trans('Instructor is required.');
  }
  return Object.keys(errors.value).length === 0;
}

function onSave() {
  if (validate()) {
    emit('save', course.value);
  }
}
</script>

<template>
  <Dialog :visible="visible" modal :closable="false">
    <Card class="mb-4">
      <template #header>
        <div class="flex justify-between items-center">
          <h3 class="text-lg font-medium">{{ $t('Create New Course') }}</h3>
          <Button
            icon="pi pi-times"
            rounded
            class="text-gray-500 hover:text-gray-700"
            @click="$emit('close')"
          />
        </div>
      </template>

      <form @submit.prevent="onSave" class="grid grid-cols-2 gap-4 mb-4">
        <InfoField :title="$t('date')">
          <Calendar v-model="course.date" dateFormat="yy-mm-dd" showIcon />
        </InfoField>

        <div class="flex flex-row gap-4">
          <InfoField :title="$t('start time')">
            <TimeInput v-model="course.startTime" />
          </InfoField>
          <InfoField :title="$t('end time')">
            <TimeInput v-model="course.endTime" />
          </InfoField>
        </div>

        <InfoField :title="$t('title')">
          <TextInput
            v-model="course.name"
            class="w-full"
            :class="{ 'border-red-500': errors.name }"
          />
          <small v-if="errors.name" class="text-red-500">{{ errors.name }}</small>
        </InfoField>

        <InfoField :title="$t('instructor')">
          <TextInput
            v-model="course.instructor"
            class="w-full"
            :class="{ 'border-red-500': errors.instructor }"
          />
          <small v-if="errors.instructor" class="text-red-500">{{ errors.instructor }}</small>
        </InfoField>

        <InfoField :title="$t('place (leave empty if in Bewegungsraum)')">
          <TextInput v-model="course.location" clearable class="w-full" />
        </InfoField>

        <div class="col-span-2 flex justify-center mt-6">
          <Button :label="$t('Save')" icon="pi pi-check" class="w-1/2" type="submit" />
        </div>
      </form>
    </Card>
  </Dialog>
</template>
