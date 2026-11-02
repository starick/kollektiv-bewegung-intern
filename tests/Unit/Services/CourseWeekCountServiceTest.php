<?php

namespace Tests\Unit\Services;

use App\Services\CourseWeekCountService;
use Maatwebsite\Excel\Facades\Excel;
use Tests\TestCase;

class CourseWeekCountServiceTest extends TestCase
{
    public function test_count_per_week_counts_courses_per_iso_week_ordered_by_count(): void
    {
        Excel::shouldReceive('toCollection')
            ->once()
            ->andReturn(collect([
                collect([
                    collect(['titel' => 'A', 'start_datum' => '31.12.2024 10:00']), // ISO week 1 of 2025
                    collect(['titel' => 'B', 'start_datum' => '02.01.2025 09:00']), // ISO week 1 of 2025
                    collect(['titel' => 'C', 'start_datum' => '06.01.2025 08:00']), // ISO week 2 of 2025
                    collect(['titel' => 'D', 'start_datum' => '']),
                    collect(['titel' => 'E', 'start_datum' => 'invalid']),
                ]),
            ]));

        $service = new CourseWeekCountService();

        $this->assertSame([
            ['year' => 2025, 'week' => 1, 'courseCount' => 2],
            ['year' => 2025, 'week' => 2, 'courseCount' => 1],
        ], $service->countPerWeek('fake.xlsx'));
    }

    public function test_count_per_week_returns_empty_list_without_dates(): void
    {
        Excel::shouldReceive('toCollection')
            ->once()
            ->andReturn(collect([collect([collect(['titel' => 'A'])])]));

        $service = new CourseWeekCountService();

        $this->assertSame([], $service->countPerWeek('fake.xlsx'));
    }
}
