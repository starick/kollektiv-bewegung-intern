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
    public function import($file, TimeTable $timeTable = null, DateTimeZone $timeZone = null)
    {
        $coursesFromExcel = Excel::toCollection(new CourseImport, $file)->first();
        $courses = new Collection();
        $timeZone ??= new DateTimeZone('Europe/Berlin');

        foreach ($coursesFromExcel as $courseData) {
            if ($course = $this->createCourse($courseData, $timeTable, $timeZone)) {
                $courses->push($course);
            }
        }

        return $courses;
    }

    public function createCourse(Collection $courseData, TimeTable $timeTable, DateTimeZone $timeZone): Course|null
    {
        if (
            !$courseData->has('titel') ||
            $courseData->get('titel') === null ||
            $courseData->get('titel') === ''
        ) {
            return null;
        }

        $instructor = $courseData->get('wer');
        if ($instructor === null || $instructor === '') {
            $instructor = $courseData->get('beschreibung');
        }

        $location = $courseData->get('wo');
        if (in_array($location, ['', 'Bewegungsraum', 'Freiraum', 'Am Mittelhafen 42'])) {
            $location = null;
        }

        $dataMapped = array_filter([
            'start_time'    => Carbon::createFromFormat('d.m.Y H:i', $courseData->get('start_datum'), $timeZone),
            'end_time'      => Carbon::createFromFormat('d.m.Y H:i', $courseData->get('end_datum'), $timeZone),
            'name'          => $courseData->get('titel') ?? '',
            'instructor'    => $instructor ?? '',
            'location'      => $location,
            'time_table_id' => $timeTable?->id,
        ], fn($data) => $data !== null);

        return $timeTable->isWithinTimeRange($dataMapped['start_time']) 
            ? Course::create($dataMapped)
            : null;
    }
}