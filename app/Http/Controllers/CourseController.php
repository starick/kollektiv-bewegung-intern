<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use App\Models\TimeTable;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    public function store(StoreCourseRequest $request, TimeTable $timeTable)
    {
        Course::create([
            ...$request->validated(),
            'time_table_id' => $timeTable->id,
        ]);

        return redirect()->route('time-tables.show', $timeTable)
            ->with('success', __('Course created successfully.'));
    }

    public function update(UpdateCourseRequest $request, TimeTable $timeTable, Course $course)
    {
        $course->update($request->validated());

        return redirect()->route('time-tables.show', $course->timeTable)
            ->with('success', __('Course updated successfully.'));
    }

    public function destroy(Request $request, TimeTable $timeTable, Course $course)
    {
        $course->delete();

        return redirect()->route('time-tables.show', $course->timeTable)
            ->with('success', __('Course deleted successfully.'));
    }
}
