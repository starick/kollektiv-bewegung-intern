<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProtocolRequest;
use App\Http\Requests\UpdateProtocolRequest;
use App\Http\Resources\ProtocolResource;
use App\Markdown\DropImagesExtension;
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
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Protocol::class);

        $search = is_string($request->input('search')) ? trim($request->input('search')) : '';
        $sort = $request->input('sort') === 'updatedAt' ? 'updatedAt' : 'date';
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';
        $perPage = in_array($request->integer('per_page'), [10, 20, 50], true) ? $request->integer('per_page') : 20;

        $protocols = Protocol::with('editor')
            ->when($search !== '', function ($query) use ($search) {
                $like = '%'.addcslashes($search, '%_\\').'%';

                $query->where(fn ($query) => $query
                    ->where('description', 'like', $like)
                    // MySQL compares JSON case-sensitively, so compare the topics as lowercased text
                    ->orWhereRaw('LOWER(CAST(topics AS CHAR)) LIKE ?', [mb_strtolower($like)])
                    ->orWhere('text', 'like', $like));
            })
            ->orderBy($sort === 'updatedAt' ? 'updated_at' : 'date', $direction)
            ->orderBy('id', $direction)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('Protocols/Index', [
            'protocols' => ProtocolResource::collection($protocols),
            'filters' => [
                'search' => $search,
                'sort' => $sort,
                'direction' => $direction,
                'perPage' => $perPage,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        Gate::authorize('create', Protocol::class);

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
            ->with('success', __('Protocol created successfully.'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Protocol $protocol)
    {
        Gate::authorize('view', $protocol);

        return Inertia::render('Protocols/Show', [
            'protocol' => (new ProtocolResource($protocol->load('uploader', 'editor')))->resolve(),
            // Raw HTML in the Markdown is escaped so protocols can't inject markup or scripts.
            'html' => Str::markdown($protocol->text, [
                'html_input' => 'escape',
                'allow_unsafe_links' => false,
            ], [new DropImagesExtension]),
            'can' => [
                'update' => $request->user()->can('update', $protocol),
                'delete' => $request->user()->can('delete', $protocol),
            ],
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Protocol $protocol)
    {
        Gate::authorize('update', $protocol);

        return Inertia::render('Protocols/Edit', [
            'protocol' => (new ProtocolResource($protocol))->resolve(),
            'text' => $protocol->text,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateProtocolRequest $request, Protocol $protocol)
    {
        $protocol->update([
            'date' => $request->validated('date'),
            'description' => $request->validated('description'),
            'topics' => $request->topics(),
            'text' => $request->validated('text'),
            'edited_by' => auth()->id(),
        ]);

        return redirect()->route('protocols.show', $protocol)
            ->with('success', __('Protocol updated successfully.'));
    }

    /**
     * Download the protocol as Markdown file.
     */
    public function download(Protocol $protocol)
    {
        Gate::authorize('view', $protocol);

        $filename = collect([$protocol->date->toDateString(), Str::slug($protocol->description ?? '')])
            ->filter()
            ->implode('-').'.md';

        return response()->streamDownload(
            fn () => print ($protocol->text),
            $filename,
            ['Content-Type' => 'text/markdown; charset=UTF-8'],
        );
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Protocol $protocol)
    {
        Gate::authorize('delete', $protocol);

        $protocol->delete();

        return redirect()->route('protocols.index')->with('success', __('Protocol deleted successfully.'));
    }
}
