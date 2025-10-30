<script setup lang="ts">
import { defineProps, watch } from 'vue';

import AppLayout from '@/Components/Layout/AppLayout.vue';
import TimeTableInfo from './Partials/TimeTableInfo.vue';
import CourseTable from '../../Components/TimeTables/CourseTable.vue';
import TimeTableDesign from './Partials/TimeTableDesign.vue';
import { timetableShowModel, TimeTableShowProps } from '@/Models/TimeTables/timetable-show-model';

const props = defineProps<TimeTableShowProps>();

const { isLoading, title, reload, loadCourses, deleteCourse, addCourse, saveCourse } =
  timetableShowModel(props);

watch(() => props.timeTable, loadCourses, { immediate: true });
</script>

<template>
  <AppLayout :title="title">
    <Tabs value="0">
      <TabList>
        <Tab value="0">Design</Tab>
        <Tab value="1">Courses</Tab>
        <Tab value="2">Info</Tab>
      </TabList>
      <TabPanels>
        <TabPanel value="0">
          <TimeTableDesign :timeTable="timeTable" :courses="courses" :is-loading="isLoading" />
        </TabPanel>
        <TabPanel value="1">
          <div v-if="isLoading" class="flex flex-1">
            <ProgressSpinner />
          </div>

          <CourseTable
            v-else
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
