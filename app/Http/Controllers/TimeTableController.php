<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreTimeTableRequest;
use App\Http\Requests\UpdateTimeTableRequest;
use App\Http\Resources\TimeTableResource;
use App\Models\TimeTable;
use Inertia\Inertia;
use App\Services\CourseImportService;

class TimeTableController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return Inertia::render('TimeTables/Index', [
            'timeTables' => TimeTableResource::collection(TimeTable::with('creator')->get()),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('TimeTables/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTimeTableRequest $request, CourseImportService $courseImportService)
    {
        $validated = collect($request->validated());

        $timeTable = TimeTable::create($validated->except('file')->toArray());

        if ($request->hasFile('file')) {
            $courses = $courseImportService->import($validated['file'], $timeTable);
        }

        return redirect()->route('time-tables.show', $timeTable)
            ->with('success', 'Time table created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(TimeTable $timeTable)
    {
        return Inertia::render('TimeTables/Show', [
            'timeTable' => new TimeTableResource($timeTable->load('creator', 'courses')),
            'editing' => false,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(TimeTable $timeTable)
    {
        return Inertia::render('TimeTables/Show', [
            'timeTable' => $timeTable->load('courses', 'creator'),
            'editing' => true,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTimeTableRequest $request, TimeTable $timeTable)
    {
        $timeTable->update($request->validated());
        return redirect()->route('time-tables.show', $timeTable)
            ->with('success', 'Time table updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(TimeTable $timeTable)
    {
        $timeTable->delete();
        return redirect()->route('time-tables.index')->with('success', 'Time table deleted successfully.');
    }
}
