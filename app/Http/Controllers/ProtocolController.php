<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProtocolRequest;
use App\Http\Resources\ProtocolResource;
use App\Models\Protocol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Inertia\Inertia;

class ProtocolController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $protocols = Protocol::with('uploader', 'editor')
            ->orderByDesc('date')
            ->get();

        return Inertia::render('Protocols/Index', [
            'protocols' => ProtocolResource::collection($protocols),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Protocols/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreProtocolRequest $request)
    {
        // Strip a UTF-8 byte order mark, which some editors prepend to exported files.
        $text = preg_replace('/^\xEF\xBB\xBF/', '', $request->file('file')->get());

        $protocol = Protocol::create([
            'date' => $request->validated('date'),
            'description' => $request->validated('description'),
            'topics' => $request->topics(),
            'text' => $text,
            'uploaded_by' => auth()->id(),
        ]);

        return redirect()->route('protocols.show', $protocol)
            ->with('success', 'Protocol created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Protocol $protocol)
    {
        return Inertia::render('Protocols/Show', [
            'protocol' => (new ProtocolResource($protocol->load('uploader', 'editor')))->resolve(),
            // Raw HTML in the Markdown is escaped so protocols can't inject markup or scripts.
            'html' => Str::markdown($protocol->text, [
                'html_input' => 'escape',
                'allow_unsafe_links' => false,
            ]),
            'can' => [
                'delete' => $request->user()->can('delete', $protocol),
            ],
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Protocol $protocol)
    {
        Gate::authorize('delete', $protocol);

        $protocol->delete();

        return redirect()->route('protocols.index')->with('success', 'Protocol deleted successfully.');
    }
}
