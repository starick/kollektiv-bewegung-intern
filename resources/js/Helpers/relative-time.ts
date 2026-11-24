const UNITS: Array<[Intl.RelativeTimeFormatUnit, number]> = [
  ['day', 86400],
  ['hour', 3600],
  ['minute', 60]
];

/**
 * "vor 2 Stunden", "gestern" … for the last week, the plain date for anything older.
 */
export function relativeTime(value: string, locale: string, now: Date = new Date()): string {
  const date = new Date(value);
  const seconds = Math.round((now.getTime() - date.getTime()) / 1000);

  if (seconds >= 7 * 86400) {
    return date.toLocaleDateString(locale === 'de' ? 'de-DE' : locale);
  }

  const format = new Intl.RelativeTimeFormat(locale, { numeric: 'auto' });
  for (const [unit, size] of UNITS) {
    if (seconds >= size) {
      return format.format(-Math.floor(seconds / size), unit);
    }
  }

  return format.format(0, 'second');
}
