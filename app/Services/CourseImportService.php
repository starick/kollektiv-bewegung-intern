<?php

namespace App\Services;

use DateTimeZone;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\CourseImport;
use Carbon\Carbon;
use App\Models\Course;
use App\Models\TimeTable;

class CourseImportService
{
    private DateTimeZone $timezone;

    public function __construct()
    {
        $this->timezone = new DateTimeZone('Europe/Berlin');
    }

    public function import($file, TimeTable $timeTable = null)
    {
        $coursesFromExcel = Excel::toCollection(new CourseImport, $file)->first();
        $courses = new Collection();

        foreach ($coursesFromExcel as $courseData) {
            if (!$courseData->has('titel') || $courseData->get('titel') === null || $courseData->get('titel') === '') {
                continue;
            }

            $instructor = $courseData->get('wer');
            if ($instructor === null || $instructor === '') {
                $instructor = $courseData->get('beschreibung');
            }

            $place = $courseData->get('wo');
            if (in_array($place, ['', 'Bewegungsraum', 'Freiraum', 'Am Mittelhafen 42'])) {
                $place = null;
            }

            $dataMapped = array_filter([
                    'start_date' => Carbon::createFromFormat('d.m.Y H:i', $courseData->get('start_datum'), $timezone),
                    'end_date' => Carbon::createFromFormat('d.m.Y H:i', $courseData->get('end_datum'), $timezone),
                    'title' => $courseData->get('titel'),
                    'instructor' => $instructor ?? '',
                    'place' => $place,
                    'time_table_id' => $timeTable?->id,
                ], fn($data) => $data !== null);

            $courses->add(Course::create($dataMapped));
        }

        $courses->each(fn() => $course->save());

        return $coursesCollection;
    }
}