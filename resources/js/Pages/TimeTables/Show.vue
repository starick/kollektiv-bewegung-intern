<script setup lang="ts">
import { TimeTable } from '@/Types/time-table';
import { defineProps, defineEmits, ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import useAlert from '@/Hooks/alert';

import AppLayout from '@/Components/Layout/AppLayout.vue';
import TimeTableInfo from './Partials/TimeTableInfo.vue';
import CourseTable from '../../Components/TimeTables/CourseTable.vue';
import { Course } from '@/Types/course';
import TimeTableDesign from './Partials/TimeTableDesign.vue';

const props = defineProps<{
  timeTable: { data: TimeTable };
}>();

const emit = defineEmits<{
  (e: 'row-save', payload: { index: number; oldData: Course; newData: Course }): void;
  (e: 'row-cancel', payload: { index: number; data: Course }): void;
  (e: 'row-init', payload: { index: number; data: Course }): void;
}>();

const editingRows = ref<any[]>([]);
const alert = useAlert();

const reload = async () => {
  router.reload({
    only: ['timeTables'],
    onError: (e) => alert.error('Reload error:', e)
  });
};

const title = 'Show Timetable';
</script>

<template>
  <AppLayout :title="title">
    <Tabs value="0">
      <TabList>
        <Tab value="0">Info</Tab>
        <Tab value="1">Courses</Tab>
        <Tab value="2">Display</Tab>
      </TabList>
      <TabPanels>
        <TabPanel value="0">
          <TimeTableInfo :timeTable="timeTable.data" />
        </TabPanel>
        <TabPanel value="1">
          <CourseTable :timeTable="timeTable.data" @reload="reload" />
        </TabPanel>
        <TabPanel value="2">
          <TimeTableDesign :timeTable="timeTable.data" />
        </TabPanel>
      </TabPanels>
    </Tabs>
  </AppLayout>
</template>
