import { SassString } from 'sass';
import { Course } from './course';

// TODO: Be more specific as soon as type is more fleshed-out / expecations are clear
export type TimeTableDesignConfig = Record<string, any>;

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
  designConfig?: TimeTableDesignConfig;
}
