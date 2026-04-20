import useAlert from '@/Composables/use-alerts';
import useCourseActions from '@/Composables/use-course-actions';
import { courseToDTO } from '@/Helpers/course-mapper';
import { Course } from '@/Types/course';
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';
import { TimeTable } from '@/Types/time-table';

export interface TimeTableShowProps {
  timeTable: TimeTable;
  courses?: Array<Course>;
}

export function timetableShowModel(props: TimeTableShowProps) {
  const alert = useAlert();
  const courseActions = useCourseActions(props.timeTable.id);

  const isLoading = ref<boolean>(true);

  async function reload() {
    router.reload({
      only: ['timeTable'],
      onError: (e) => alert.error('Reload error:', e)
    });
  }

  function loadCourses() {
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
        loadCourses();
      },
      onError: (e: Event) => alert.error('Error adding course:', e)
    });
  }

  function saveCourse(course: Course) {
    courseActions.edit(course.id, course, {
      onSuccess: () => {
        loadCourses();
      },
      onError: (e: Event) => alert.error('Error saving course:', e)
    });
  }

  const title = 'Show Timetable';

  return {
    isLoading,
    title,
    reload,
    loadCourses,
    deleteCourse,
    addCourse,
    saveCourse
  };
}
