import { describe, expect, it } from 'vitest';
import { relativeTime } from '@/Helpers/relative-time';

const now = new Date('2026-11-24T12:00:00Z');
const ago = (seconds: number) => new Date(now.getTime() - seconds * 1000).toISOString();

describe('relativeTime', () => {
  it('says "now" for the last minute', () => {
    expect(relativeTime(ago(20), 'de', now)).toBe('jetzt');
  });

  it('counts minutes, hours and days in German', () => {
    expect(relativeTime(ago(5 * 60), 'de', now)).toBe('vor 5 Minuten');
    expect(relativeTime(ago(2 * 3600), 'de', now)).toBe('vor 2 Stunden');
    expect(relativeTime(ago(26 * 3600), 'de', now)).toBe('gestern');
    expect(relativeTime(ago(3 * 86400), 'de', now)).toBe('vor 3 Tagen');
  });

  it('follows the locale', () => {
    expect(relativeTime(ago(2 * 3600), 'en', now)).toBe('2 hours ago');
  });

  it('shows the date for anything older than a week', () => {
    expect(relativeTime(ago(10 * 86400), 'de', now)).toBe('14.11.2026');
  });
});
