<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use App\Models\TimeTable;
use Illuminate\Http\Request;

class CourseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // probably not needed
        return redirect()->route('not-found');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // probably not needed
        return redirect()->route('not-found');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCourseRequest $request, TimeTable $timeTable)
    {
        $course = Course::create([
            ...$request->validated(),
            'time_table_id' => $timeTable->id,
        ]);
        return redirect()->route('time-tables.show', $timeTable)
            ->with('success', 'Course created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, TimeTable $timeTable, Course $course)
    {
        // probably not needed
        return redirect()->route('not-found');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Request $request, TimeTable $timeTable, Course $course)
    {
        // probably not needed
        return redirect()->route('not-found');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCourseRequest $request, TimeTable $timeTable, Course $course)
    {
        $course->update($request->validated());
        return redirect()->route('time-tables.show', $course->timeTable)
            ->with('success', 'Course updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, TimeTable $timeTable, Course $course)
    {
        $course->delete();
        return redirect()->route('time-tables.show', $course->timeTable)
            ->with('success', 'Course deleted successfully.');
    }
}
