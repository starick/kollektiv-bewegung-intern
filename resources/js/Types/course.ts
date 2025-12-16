import { Time } from '@/ValueObjects/time';

export interface Course {
  id: number;
  name: string;
  location: string;
  date: Date;
  startTime: Time;
  endTime: Time;
  instructor: string;
}
