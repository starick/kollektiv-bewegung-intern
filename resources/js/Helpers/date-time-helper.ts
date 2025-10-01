import { addWeeks, endOfISOWeek, startOfISOWeek } from 'date-fns';

export function formatTime(minutes: number): string {
  const hrs = Math.floor(minutes / 60);
  const mins = minutes % 60;
  return `${hrs.toString().padStart(2, '0')}:${mins.toString().padStart(2, '0')}`;
}

export function formatDateTime(d: Date | string): string {
  return new Date(d).toLocaleString('de-DE');
}

export function formatDate(d: Date | string): string {
  return new Date(d).toLocaleDateString('de-DE');
}

export function startOfWeek(year: number, week: number): Date {
  const isoWeek1 = startOfISOWeek(new Date(year, 0, 4));
  return addWeeks(isoWeek1, (week ?? 1) - 1);
}

export function endOfWeek(year: number, week: number): Date {
  return endOfISOWeek(startOfWeek(year, week));
}
