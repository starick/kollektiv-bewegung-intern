<script setup lang="ts">
import { TimeTable } from '@/Types/time-table';
import { defineProps, defineEmits, ref, computed } from 'vue';
import { router } from '@inertiajs/vue3';
import useAlert from '@/Hooks/alert';

import AppLayout from '@/Components/Layout/AppLayout.vue';
import TimeTableInfo from './Partials/TimeTableInfo.vue';
import CourseTable from '../../Components/TimeTables/CourseTable.vue';
import { Course } from '@/Types/course';

const props = defineProps<{
  timeTable: { data: TimeTable & { courses: Course[] } };
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
          <p class="m-0">
            At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium
            voluptatum deleniti atque corrupti quos dolores et quas molestias excepturi sint
            occaecati cupiditate non provident, similique sunt in culpa qui officia deserunt
            mollitia animi, id est laborum et dolorum fuga. Et harum quidem rerum facilis est et
            expedita distinctio. Nam libero tempore, cum soluta nobis est eligendi optio cumque
            nihil impedit quo minus.
          </p>
        </TabPanel>
      </TabPanels>
    </Tabs>
  </AppLayout>
</template>
