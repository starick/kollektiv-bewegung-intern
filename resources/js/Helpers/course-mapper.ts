import { Course } from '@/Types/course';
import { formatDayAndMonth, formatInternationalDate, formatWeekday } from './date-time-helper';
import { Time } from '@/ValueObjects/time';

function compareCourses(a: Course, b: Course): number {
  if (a.date < b.date) {
    return -1;
  }
  if (a.date > b.date) {
    return 1;
  }
  return a.startTime.compare(b.startTime);
}
export function groupCoursesByDay(courses: Course[]) {
  const coursesOrdered = courses.sort(compareCourses);
  const map = new Map<
    string,
    {
      day: string;
      dateLabel: string;
      dateKey: string; // YYYY-MM-DD for sorting
      courses: Array<Course>;
    }
  >();

  for (const course of coursesOrdered ?? []) {
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
  const dateString = course.date instanceof Date ? course.date.toISOString() : course.date;

  return {
    name: course.name,
    instructor: course.instructor,
    date: dateString,
    start_time: course.startTime.toString(),
    end_time: course.endTime.toString(),
    location: course.location.length > 0 ? course.location : null
  };
}

export function toCourse(data: any): Course {
  return {
    id: data.id ?? -1,
    name: data.name ?? 'MISSING NAME',
    instructor: data.instructor ?? 'MISSING INSTRUCTOR',
    date: new Date(data.date),
    startTime: Time.parse(data.startTime),
    endTime: Time.parse(data.endTime),
    location: data.location ?? ''
  } as Course;
}

export function newCourse(): Course {
  const now = new Date();

  const tempId = -Date.now();

  return {
    id: tempId,
    date: now,
    startTime: new Time(10, 0),
    endTime: new Time(12, 0),
    name: '',
    instructor: '',
    location: ''
  };
}
