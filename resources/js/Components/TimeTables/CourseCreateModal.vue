<script setup lang="ts">
import { ref } from 'vue';
import { Course } from '@/Types/course';
import InfoField from '../General/InfoField.vue';
import TextInput from '../Form/TextInput.vue';
import Card from '../General/Card.vue';

defineProps<{
  visible: boolean;
}>();

const emit = defineEmits(['save', 'close']);

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

const course = ref<Course>(makeEmptyCourse());
</script>

<template>
  <OverlayPanel :visible="visible" :dismissable="true" @hide="$emit('close')">
    <Card class="mb-4">
      <template #header>
        <div class="flex justify-between items-center">
          <h3 class="text-lg font-medium">Create New Course</h3>
          <button class="text-gray-500 hover:text-gray-700" @click="$emit('close')">&times;</button>
        </div>
      </template>
      <div class="flex flex-row">
        <InfoField title="date">
          <Calendar v-model="course.date" dateFormat="yy-mm-dd" showIcon />
        </InfoField>
        <InfoField title="start time">
          // TODO custom time picker
          {{ course.startTime }}
        </InfoField>
        <InfoField title="end time">
          // TODO custom time picker
          {{ course.startTime }}
        </InfoField>
        <InfoField title="title">
          <TextInput v-model="course.name" />
        </InfoField>
        <InfoField title="instructor">
          <TextInput v-model="course.instructor" />
        </InfoField>
        <InfoField title="place">
          <TextInput v-model="course.location" clearable />
        </InfoField>
      </div>
    </Card>
  </OverlayPanel>
</template>
