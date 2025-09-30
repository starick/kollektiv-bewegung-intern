<?php

namespace App\Imports;

use App\Models\Course;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;

class CourseImport implements ToModel, WithHeadingRow
{
    private function getPlace(array $row)
    {
        $place = Arr::get($row, 'place');

        return in_array($place, ['', 'Bewegungsraum', 'Freiraum']) ? null : $place;
    }

    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        $place = Arr::get($row, 'Wo');

        if (!array_key_exists('Titel', $row)) {
            return null;
        }
        return new Course([
            'title' => $row['Titel'],
            'instructor' => Arr::get($row, 'Beschreibung') ?? Arr::get($row, 'Wer'),
            'place' => $row['Wo'],
            'start_date' => $row['Start-Datum'],
            'end_date' => $row['End-Datum'],
        ]);
    }
}