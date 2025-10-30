<?php

namespace Tests\Unit\Services;

use App\Models\Course;
use App\Models\TimeTable;
use App\Services\CourseImportService;
use Carbon\Carbon;
use DateTimeZone;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class CourseImportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_import_creates_courses_and_skips_invalid_rows(): void
    {
        // Given a timetable for ISO week 1 of 2025
        $tt = TimeTable::create([
            'year' => 2025,
            'week' => 1,
            'created_by' => null,
            'display_config' => [],
        ]);

        // Inside week: 2024-12-31 10:00 to 2024-12-31 11:00 (ISO week 1 of 2025)
        $validRowInside = collect([
            'titel'        => 'Calculus',
            'wer'          => 'Ada',
            'beschreibung' => 'Math class',
            'wo'           => 'Room 101',
            'start_datum'  => '31.12.2024 10:00',
            'end_datum'    => '31.12.2024 11:00',
        ]);

        // Missing title -> should be skipped entirely
        $missingTitle = collect([
            'titel'        => '',
            'wer'          => 'Alan',
            'beschreibung' => 'CS',
            'wo'           => 'Room 202',
            'start_datum'  => '02.01.2025 12:00',
            'end_datum'    => '02.01.2025 13:00',
        ]);

        // Instructor fallback from 'beschreibung' when 'wer' empty; location normalized to null
        $fallbackInstructorAndNullLocation = collect([
            'titel'        => 'Astrophysics',
            'wer'          => '',
            'beschreibung' => 'Dr. Sagan',
            'wo'           => 'Freiraum', // should become null
            'start_datum'  => '02.01.2025 09:15',
            'end_datum'    => '02.01.2025 10:45',
        ]);

        // Outside week (just after end) -> filtered out by isWithinTimeRange
        $outsideWeek = collect([
            'titel'        => 'Chemistry',
            'wer'          => 'Marie',
            'beschreibung' => 'Lab',
            'wo'           => 'Room 303',
            'start_datum'  => '06.01.2025 08:00', // Monday after ISO week 1 (outside)
            'end_datum'    => '06.01.2025 09:00',
        ]);

        // Mock Excel facade to return a single "sheet" with the four rows above
        Excel::shouldReceive('toCollection')
            ->once()
            ->andReturn(collect([
                collect([$validRowInside, $missingTitle, $fallbackInstructorAndNullLocation, $outsideWeek])
            ]));

        $service = new CourseImportService();

        $result = $service->import('fake.xlsx', $tt, new DateTimeZone('Europe/Berlin'));

        // Only 2 valid & in-range rows should be created
        $this->assertCount(2, $result);
        $this->assertInstanceOf(Course::class, $result[0]);
        $this->assertInstanceOf(Course::class, $result[1]);

        // DB assertions for the specific rows

        // Row 1: "Calculus" — instructor taken from 'wer', location kept
        $this->assertDatabaseHas('courses', [
            'time_table_id' => $tt->id,
            'name' => 'Calculus',
            'instructor' => 'Ada',
            'location' => 'Room 101',
        ]);

        // Row 3: "Astrophysics" — instructor fallback from 'beschreibung', location null
        $this->assertDatabaseHas('courses', [
            'time_table_id' => $tt->id,
            'name' => 'Astrophysics',
            'instructor' => 'Dr. Sagan',
            'location' => null,
        ]);
    }
    public function test_create_course_returns_null_when_title_missing(): void
    {
        $tt = TimeTable::create([
            'year' => 2025,
            'week' => 1,
            'created_by' => null,
            'display_config' => [],
        ]);

        $service = new CourseImportService();

        $row = collect([
            'titel'        => '',
            'wer'          => 'Nobody',
            'beschreibung' => 'Fallback',
            'wo'           => 'Room Z',
            'start_datum'  => '02.01.2025 10:00',
            'end_datum'    => '02.01.2025 11:00',
        ]);

        $this->assertNull($service->createCourse($row, $tt, new DateTimeZone('Europe/Berlin')));
    }

    public function test_create_course_returns_null_when_outside_timetable_range(): void
    {
        $tt = TimeTable::create([
            'year' => 2025,
            'week' => 1,
            'created_by' => null,
            'display_config' => [],
        ]);

        $service = new CourseImportService();

        // Monday after ISO week 1 (outside)
        $row = collect([
            'titel'        => 'Outside Range',
            'wer'          => 'Lecturer',
            'beschreibung' => null,
            'wo'           => 'Room 9',
            'start_datum'  => '06.01.2025 08:00',
            'end_datum'    => '06.01.2025 09:00',
        ]);

        $this->assertNull($service->createCourse($row, $tt, new DateTimeZone('Europe/Berlin')));
    }
}
