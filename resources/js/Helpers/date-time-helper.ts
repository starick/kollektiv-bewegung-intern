export function formatDateTime(d: Date | string): string {
  return new Date(d).toLocaleString('de-DE', { timeZone: 'UTC' });
}

export function formatDate(d: Date | string): string {
  return new Date(d).toLocaleDateString('de-DE', { timeZone: 'UTC' });
}

export function formatInternationalDate(d: Date | string): string {
  return new Date(d).toISOString().slice(0, 10);
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

// Returns UTC midnight of the Monday starting the given ISO week.
export function startOfWeek(year: number, week: number): Date {
  const jan4 = new Date(Date.UTC(year, 0, 4));
  const dow = jan4.getUTCDay() || 7; // 1=Mon … 7=Sun
  const week1Monday = new Date(jan4.getTime() - (dow - 1) * 86400000);
  return new Date(week1Monday.getTime() + ((week ?? 1) - 1) * 7 * 86400000);
}

// Returns UTC midnight of the Sunday ending the given ISO week.
export function endOfWeek(year: number, week: number): Date {
  return new Date(startOfWeek(year, week).getTime() + 6 * 86400000);
}
