import { Course } from './course';

export interface TimeTable {
  id: number;
  year: number;
  week: number;
  coursesCount: number;
  createdAt: Date;
  updatedAt: Date;
  creator?: {
    id: number;
    name: string;
    email: string;
  };
  courses?: Course[];
}
