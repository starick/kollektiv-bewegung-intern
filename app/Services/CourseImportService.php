<?php

namespace App\Services;

use App\ValueObjects\Time;
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
        $timeZone ??= new DateTimeZone('UTC');

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
            'date'          => Carbon::createFromFormat('d.m.Y H:i', $courseData->get('start_datum'), $timeZone),
            'start_time'    => Time::parse($courseData->get('start_datum')),
            'end_time'      => Time::parse($courseData->get('end_datum')),
            'name'          => $courseData->get('titel') ?? '',
            'instructor'    => $instructor ? mb_strimwidth($instructor,0, 64,'...') : '',
            'location'      => $location,
            'time_table_id' => $timeTable?->id,
        ], fn($data) => $data !== null);

        return $timeTable->isWithinTimeRange($dataMapped['date']) 
            ? Course::create($dataMapped)
            : null;
    }
}