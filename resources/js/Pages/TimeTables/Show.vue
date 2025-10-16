<script setup lang="ts">
import { TimeTable } from '@/Types/time-table';
import { defineProps, defineEmits, ref, computed, toRef, onMounted, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import useAlert from '@/Composables/useAlert';

import AppLayout from '@/Components/Layout/AppLayout.vue';
import TimeTableInfo from './Partials/TimeTableInfo.vue';
import CourseTable from '../../Components/TimeTables/CourseTable.vue';
import { Course } from '@/Types/course';
import TimeTableDesign from './Partials/TimeTableDesign.vue';
import useCourseActions from '@/Composables/useCourseActions';
import { courseToDTO } from '@/Helpers/course-mapper';

const props = defineProps<{
  timeTable: TimeTable;
  courses?: Array<Course>;
}>();

const emit = defineEmits<{
  (e: 'row-save', payload: { index: number; oldData: Course; newData: Course }): void;
  (e: 'row-cancel', payload: { index: number; data: Course }): void;
  (e: 'row-init', payload: { index: number; data: Course }): void;
}>();

const editingRows = ref<any[]>([]);
const alert = useAlert();
const courseActions = useCourseActions(props.timeTable.id);

const isLoading = ref<boolean>(true);

async function reload() {
  console.log('load timeTable');
  router.reload({
    only: ['timeTable'],
    onError: (e) => alert.error('Reload error:', e)
  });
}

function loadCourses() {
  console.log('load courses');
  courseActions.reload({
    onError: (e) => alert.error('Reload error:', e),
    onFinish: () => (isLoading.value = false)
  });
}

function deleteCourse(course: Course) {
  courseActions.remove(course.id, {
    onSuccess: () => {
      alert.add('course deleted', 'success');
      loadCourses();
    },
    onError: (e: Event) => alert.error('Error deleting course:', e)
  });
}

function addCourse(course: Course) {
  courseActions.add(courseToDTO(course), {
    onSuccess: () => {
      alert.add('course deleted', 'success');
      loadCourses();
    },
    onError: (e: Event) => alert.error('Error adding course:', e)
  });
}

function saveCourse(course: Course) {
  courseActions.edit(course.id, courseToDTO(course), {
    onSuccess: () => {
      alert.add('course deleted', 'success');
      loadCourses();
    },
    onError: (e: Event) => alert.error('Error saving course:', e)
  });
}

const title = 'Show Timetable';

watch(() => props.timeTable, loadCourses, { immediate: true });
</script>

<template>
  <AppLayout :title="title">
    {{ timeTable }}
    <Tabs value="0">
      <TabList>
        <Tab value="0">Design</Tab>
        <Tab value="1">Courses</Tab>
        <Tab value="2">Info</Tab>
      </TabList>
      <TabPanels>
        <TabPanel value="0">
          <TimeTableDesign :timeTable="timeTable" :courses="courses" />
        </TabPanel>
        <TabPanel value="1">
          <CourseTable
            :timeTable="timeTable"
            :courses="courses"
            editable
            @reload="reload"
            @row-delete="deleteCourse"
            @row-save="saveCourse"
            @row-add="addCourse"
          />
        </TabPanel>
        <TabPanel value="2">
          <TimeTableInfo :timeTable="timeTable" />
        </TabPanel>
      </TabPanels>
    </Tabs>
  </AppLayout>
</template>
