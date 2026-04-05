import { DEFAULT_DISPLAY_CONFIG } from '@/Constants/default-display-config';

export type TimeTableDesignConfig = typeof DEFAULT_DISPLAY_CONFIG;

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
  displayConfig?: TimeTableDesignConfig;
}
