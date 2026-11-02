<?php

namespace App\Services;

use App\Imports\CourseImport;
use App\ValueObjects\CourseRow;
use DateTimeZone;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;

class CourseWeekCountService
{
    /**
     * Counts the courses in the file per ISO week, most courses first.
     *
     * @return list<array{year: int, week: int, courseCount: int}>
     */
    public function countPerWeek($file, ?DateTimeZone $timeZone = null): array
    {
        $coursesFromExcel = Excel::toCollection(new CourseImport, $file)->first() ?? new Collection();
        $timeZone ??= new DateTimeZone('UTC');

        return $coursesFromExcel
            ->map(fn (Collection $courseData) => new CourseRow($courseData, $timeZone))
            ->filter(fn (CourseRow $row) => $row->isCourse())
            ->map(fn (CourseRow $row) => $row->startDate()->isoWeekYear() . '-' . $row->startDate()->isoWeek())
            ->countBy()
            ->sortDesc()
            ->map(function (int $count, string $key) {
                [$year, $week] = explode('-', $key);

                return ['year' => (int) $year, 'week' => (int) $week, 'courseCount' => $count];
            })
            ->values()
            ->all();
    }
}
