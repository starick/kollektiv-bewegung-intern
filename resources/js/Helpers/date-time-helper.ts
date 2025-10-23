import { addWeeks, endOfISOWeek, setHours, startOfISOWeek } from 'date-fns';

// TODO: Something has to happen with timezones here. I hate timezones =/

export function formatDateTime(d: Date | string): string {
  return new Date(d).toLocaleString('de-DE', { timeZone: 'UTC' });
}

export function formatDate(d: Date | string): string {
  return setHours(new Date(d), 12).toLocaleDateString('de-DE', { timeZone: 'UTC' });
}

export function formatInternationalDate(d: Date | string): string {
  const date = new Date(d);

  return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, '0')}-${String(
    date.getDate()
  ).padStart(2, '0')}`;
}

export function formatTime(d: Date | string): string {
  return new Date(d).toLocaleTimeString('de-DE', {
    hour: '2-digit',
    minute: '2-digit',
    timeZone: 'UTC'
  });
}

export function formatDayAndMonth(d: Date | string): string {
  return new Date(d).toLocaleDateString('de-DE', {
    day: '2-digit',
    month: '2-digit',
    timeZone: 'UTC'
  });
}

export function formatWeekday(d: Date | string): string {
  return new Date(d).toLocaleDateString('de-DE', { weekday: 'short', timeZone: 'UTC' });
}

export function startOfWeek(year: number, week: number): Date {
  const isoWeek1 = startOfISOWeek(new Date(year, 0, 4));
  return addWeeks(isoWeek1, (week ?? 1) - 1);
}

export function endOfWeek(year: number, week: number): Date {
  return endOfISOWeek(startOfWeek(year, week));
}
