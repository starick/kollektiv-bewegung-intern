<?php

namespace App\Services;

use App\ValueObjects\CourseRow;
use DateTimeZone;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CourseImport;
use App\Models\Course;
use App\Models\TimeTable;

class CourseImportService
{
    public function import($file, ?TimeTable $timeTable = null, ?DateTimeZone $timeZone = null)
    {
        $coursesFromExcel = Excel::toCollection(new CourseImport, $file)->first();
        $courses = new Collection();
        $timeZone ??= new DateTimeZone('UTC');

        foreach ($coursesFromExcel as $courseData) {
            if ($course = $this->createCourse(new CourseRow($courseData, $timeZone), $timeTable)) {
                $courses->push($course);
            }
        }

        return $courses;
    }

    public function createCourse(CourseRow $row, TimeTable $timeTable): Course|null
    {
        if (!$row->isCourse()) {
            return null;
        }

        $instructor = $row->instructor();

        $dataMapped = array_filter([
            'date'          => $row->startDate(),
            'start_time'    => $row->startTime(),
            'end_time'      => $row->endTime(),
            'name'          => $row->title(),
            'instructor'    => $instructor ? mb_strimwidth($instructor,0, 64,'...') : '',
            'location'      => $row->location(),
            'time_table_id' => $timeTable?->id,
        ], fn($data) => $data !== null);

        return $timeTable->isWithinTimeRange($dataMapped['date']) 
            ? Course::create($dataMapped)
            : null;
    }
}
