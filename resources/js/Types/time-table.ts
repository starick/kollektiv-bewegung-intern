import { Course } from './course';

export interface TimeTable {
  id: number;
  year: number;
  week: number;
  creator: {
    id: number;
    name: string;
    email: string;
  };
  courses: Course[];
  createdAt: Date;
  updatedAt: Date;
}
