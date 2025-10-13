import { addWeeks, endOfISOWeek, startOfISOWeek } from 'date-fns';

export function formatDateTime(d: Date | string): string {
  return new Date(d).toLocaleString('de-DE');
}

export function formatDate(d: Date | string): string {
  return new Date(d).toLocaleDateString('de-DE');
}

export function formatTime(d: Date | string): string {
  return new Date(d).toLocaleTimeString('de-DE', { hour: '2-digit', minute: '2-digit' });
}

export function formatDayAndMonth(d: Date | string): string {
  return new Date(d).toLocaleDateString('de-DE', { day: '2-digit', month: '2-digit' });
}

export function formatWeekday(d: Date | string): string {
  return new Date(d).toLocaleDateString('de-DE', { weekday: 'short' });
}

export function startOfWeek(year: number, week: number): Date {
  const isoWeek1 = startOfISOWeek(new Date(year, 0, 4));
  return addWeeks(isoWeek1, (week ?? 1) - 1);
}

export function endOfWeek(year: number, week: number): Date {
  return endOfISOWeek(startOfWeek(year, week));
}
