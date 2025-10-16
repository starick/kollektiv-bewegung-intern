import { Course } from '@/Types/course';
import { formatDayAndMonth, formatInternationalDate, formatWeekday } from './date-time-helper';

export function groupCoursesByDay(courses: Course[]) {
  const map = new Map<
    string,
    {
      day: string;
      dateLabel: string;
      dateKey: string; // YYYY-MM-DD for sorting
      courses: Array<Course>;
    }
  >();

  for (const course of courses ?? []) {
    const dateKey = formatInternationalDate(course.date);

    if (map.has(dateKey)) {
      map.get(dateKey)?.courses.push(course);
    } else {
      map.set(dateKey, {
        day: formatWeekday(course.date),
        dateLabel: formatDayAndMonth(course.date),
        dateKey,
        courses: [course]
      });
    }
  }

  return Array.from(map.values()).sort((a, b) =>
    a.dateKey < b.dateKey ? -1 : a.dateKey > b.dateKey ? 1 : 0
  );
}

export function courseToDTO(course: Course): Record<string, string | null> {
  return {
    name: course.name,
    instructor: course.instructor,
    date: course.date.toISOString(),
    start_time: course.startTime.toISOString(),
    end_time: course.endTime.toISOString(),
    location: course.location.length > 0 ? course.location : null
  };
}
