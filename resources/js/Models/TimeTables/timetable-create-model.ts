import { router, useForm } from '@inertiajs/vue3';
import axios from 'axios';
import { useConfirm } from 'primevue';
import { getWeek, isSunday } from 'date-fns';
import { startOfWeek, endOfWeek } from '@/Helpers/date-time-helper';
import { computed, ref, watch } from 'vue';
import { route } from 'ziggy-js';
import { DESIGN_TEMPLATES } from '@/Constants/design-templates';
import { WeekCourseCount } from '@/Types/week-course-count';

export function timetableCreateModel() {
  const confirm = useConfirm();

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

  const weekDetected = ref(false);
  const detectingWeek = ref(false);

  async function onFileSelect(e: { files?: File[] }) {
    formData.file = e.files?.[0] ?? null;
    weekDetected.value = false;

    if (formData.file) {
      await detectWeekFromFile(formData.file);
    }
  }

  async function detectWeekFromFile(file: File) {
    const data = new FormData();
    data.append('file', file);

    detectingWeek.value = true;

    try {
      const response = await axios.post<Array<WeekCourseCount>>(
        route('time-tables.course-count-per-week'),
        data
      );

      const [detected] = response.data;
      if (detected) {
        formData.year = detected.year;
        formData.week = detected.week;
        weekDetected.value = true;

        if (response.data.length > 1) {
          warnMultipleWeeks(detected);
        }
      }
    } catch {
      // Fall back to manual week selection if the file can't be read
    } finally {
      detectingWeek.value = false;
    }
  }

  function warnMultipleWeeks(detected: WeekCourseCount) {
    confirm.require({
      header: 'File spans multiple weeks',
      message:
        `Only courses in week ${detected.week} / ${detected.year} will be imported. ` +
        `Courses in other weeks will be skipped.`,
      icon: 'pi pi-exclamation-triangle',
      acceptLabel: 'OK',
      rejectClass: 'hidden'
    });
  }

  function onFileClear() {
    formData.file = null;
    weekDetected.value = false;
  }

  function submit() {
    formData.post(route('time-tables.store'), {
      errorBag: 'createTimeTable',
      preserveScroll: true,
      forceFormData: true
    });
  }

  function cancel() {
    router.get(route('time-tables.index'));
  }

  const startDate = computed<Date>(() => startOfWeek(formData.year, formData.week));
  const endDate = computed<Date>(() => endOfWeek(formData.year, formData.week));

  const title = 'Create New Timetable';

  return {
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
  };
}
