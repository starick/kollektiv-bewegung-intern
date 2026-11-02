<?php

namespace Tests\Unit\ValueObjects;

use App\ValueObjects\CourseRow;
use DateTimeZone;
use PHPUnit\Framework\TestCase;

class CourseRowTest extends TestCase
{
    private function row(array $data): CourseRow
    {
        return new CourseRow(collect($data), new DateTimeZone('Europe/Berlin'));
    }

    public function test_title_returns_null_when_missing_or_empty(): void
    {
        $this->assertNull($this->row([])->title());
        $this->assertNull($this->row(['titel' => ''])->title());
        $this->assertSame('Yoga', $this->row(['titel' => 'Yoga'])->title());
    }

    public function test_start_date_is_parsed_in_given_time_zone(): void
    {
        $startDate = $this->row(['start_datum' => '02.01.2025 09:15'])->startDate();

        $this->assertSame('2025-01-02 09:15', $startDate->format('Y-m-d H:i'));
        $this->assertSame('Europe/Berlin', $startDate->getTimezone()->getName());
    }

    public function test_start_date_returns_null_when_missing_or_invalid(): void
    {
        $this->assertNull($this->row([])->startDate());
        $this->assertNull($this->row(['start_datum' => ''])->startDate());
        $this->assertNull($this->row(['start_datum' => 'invalid'])->startDate());
    }

    public function test_is_course_requires_title_and_start_date(): void
    {
        $this->assertTrue($this->row(['titel' => 'Yoga', 'start_datum' => '02.01.2025 09:15'])->isCourse());
        $this->assertFalse($this->row(['titel' => '', 'start_datum' => '02.01.2025 09:15'])->isCourse());
        $this->assertFalse($this->row(['titel' => 'Yoga', 'start_datum' => 'invalid'])->isCourse());
    }

    public function test_start_and_end_time_are_read_from_dates(): void
    {
        $row = $this->row(['start_datum' => '02.01.2025 09:15', 'end_datum' => '02.01.2025 10:45']);

        $this->assertSame('09:15', $row->startTime()->toString());
        $this->assertSame('10:45', $row->endTime()->toString());
    }

    public function test_instructor_falls_back_to_description(): void
    {
        $this->assertSame('Ada', $this->row(['wer' => 'Ada', 'beschreibung' => 'Math'])->instructor());
        $this->assertSame('Math', $this->row(['wer' => '', 'beschreibung' => 'Math'])->instructor());
        $this->assertNull($this->row(['wer' => '', 'beschreibung' => ''])->instructor());
    }

    public function test_location_is_null_for_default_rooms(): void
    {
        $this->assertSame('Room 101', $this->row(['wo' => 'Room 101'])->location());
        $this->assertNull($this->row(['wo' => 'Freiraum'])->location());
        $this->assertNull($this->row(['wo' => ''])->location());
    }
}
