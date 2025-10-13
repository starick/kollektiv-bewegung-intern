<script setup lang="ts">
import { TimeTable } from '@/Types/time-table';
import TimeTableDisplay from '@/Components/TimeTables/TimeTableDisplay.vue';
import { reactive, ref } from 'vue';
import html2canvas from 'html2canvas';
import Card from '@/Components/General/Card.vue';

const props = defineProps<{
  timeTable: TimeTable;
}>();

const timetableRef = ref<HTMLElement | null>(null);

const displayConfig = reactive({
  background: {
    image: 'https://wallpapercave.com/wp/wp3272711.jpg'
  },
  body: {
    color: '#000000',
    fontFamily: 'Arial, sans-serif',
    fontSize: '14px',
    scale: 1
  },
  padding: 20
});

const onSave = () => {
  alert('Save functionality is not implemented yet.');
};

const onDownload = async () => {
  if (!timetableRef.value) return;

  const canvas = await html2canvas(timetableRef.value, {
    useCORS: true,
    backgroundColor: null,
    scale: 2,
    logging: false
  });

  const link = document.createElement('a');
  link.download = `timetable_${props.timeTable.year}-${props.timeTable.week}.jpg`;
  link.href = canvas.toDataURL('image/jpeg', 0.95);
  link.click();
};

const menuItems = [
  {
    label: 'Save Changes',
    icon: 'pi pi-save',
    command: onSave
  },
  {
    label: 'Download Image',
    icon: 'pi pi-download',
    command: onDownload
  }
];
</script>

<template>
  <div class="flex flex-row">
    <Card class="flex-1 flex-col rounded-r-none" border :menuItems="menuItems">
      <div class="flex flex-row justify-center">
        <Select
          v-model="displayConfig.background.image"
          :options="[
            { label: 'Test 1', value: 'https://wallpapercave.com/wp/wp3272711.jpg' },
            {
              label: 'Test 2s',
              value: 'https://cdn.wallpapersafari.com/48/17/0SDb2r.jpg'
            }
          ]"
          optionLabel="label"
          optionValue="value"
          placeholder="Background Image"
          class="w-48"
          :reduce="(opt) => opt.value"
        />
      </div>
      {{ displayConfig }}
    </Card>
    <div ref="timetableRef" class="flex-1">
      <TimeTableDisplay :timeTable="timeTable" :config="displayConfig" />
    </div>
  </div>
</template>
