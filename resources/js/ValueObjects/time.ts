import { min } from 'date-fns';

export class Time {
  hour: number;
  minute: number;

  private constructor(hour: number, minute: number) {
    if (hour < 0 || hour > 23) {
      throw new RangeError('Hour must be between 0 and 23');
    }
    if (minute < 0 || minute > 59) {
      throw new RangeError('Minute must be between 0 and 59');
    }
    this.hour = hour;
    this.minute = minute;
  }

  public setHour(hour: number): Time {
    if (hour < 0 || hour > 23) {
      throw new RangeError('Hour must be between 0 and 23');
    }
    this.hour = hour;

    return this;
  }

  static parse(time: string): Time {
    const trimmed = time.trim();

    // "HH:MM" or "HH:MM:SS"
    let match = trimmed.match(/^(\d{1,2}):(\d{2})(?::\d{2})?$/);
    if (match) {
      const [, h, m] = match;
      return new Time(parseInt(h, 10), parseInt(m, 10));
    }

    // date or longer string (e.g. "31.12.2024 10:00")
    match = trimmed.match(/\b(\d{1,2}):(\d{2})(?::\d{2})?\b/);
    if (match) {
      const [, h, m] = match;
      return new Time(parseInt(h, 10), parseInt(m, 10));
    }

    throw new Error(
      'Expected time format "HH:MM", "HH:MM:SS", or a string containing a time like "31.12.2024 10:00".'
    );
  }

  public setMinute(minute: number): Time {
    if (minute < 0 || minute > 59) {
      throw new RangeError('Minute must be between 0 and 59');
    }
    this.minute = minute;

    return this;
  }

  toString(): string {
    return `${String(this.hour).padStart(2, '0')}:${String(this.minute).padStart(2, '0')}`;
  }

  toJSON(): string {
    return JSON.stringify(this);
  }
}
