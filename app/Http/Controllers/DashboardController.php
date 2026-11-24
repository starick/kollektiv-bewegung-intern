<?php

namespace App\Http\Controllers;

use App\Http\Resources\TimeTableResource;
use App\Models\Protocol;
use App\Models\TimeTable;
use App\Models\WikiPage;
use Illuminate\Http\Request;
use Inertia\Inertia;
use OwenIt\Auditing\Models\Audit;

/**
 * The start page: newest timetable, newest protocol and the latest wiki changes.
 */
class DashboardController extends Controller
{
    private const WIKI_CHANGES = 10;

    public function __invoke(Request $request)
    {
        // Protocols and the wiki are only for the shared team, like their own pages
        $team = $request->user()->belongsToSharedTeam();

        return Inertia::render('Dashboard', [
            'timeTable' => $this->newestTimeTable(),
            'protocol' => $team ? $this->newestProtocol() : null,
            'wikiChanges' => $team ? $this->wikiChanges() : null,
        ]);
    }

    private function newestTimeTable(): ?array
    {
        $timeTable = TimeTable::orderByDesc('year')->orderByDesc('week')->with('courses', 'creator')->first();

        return $timeTable ? json_decode((new TimeTableResource($timeTable))->toJson(), true) : null;
    }

    private function newestProtocol(): ?array
    {
        $protocol = Protocol::orderByDesc('date')->latest()->with('editor')->first();

        return $protocol ? [
            'slug' => $protocol->slug,
            'date' => $protocol->date->toDateString(),
            'description' => $protocol->description,
            'topics' => $protocol->topics ?? [],
            'participantCount' => count($protocol->participants ?? []),
            'updatedAt' => $protocol->updated_at,
            'editor' => $protocol->editor?->name,
        ] : null;
    }

    /**
     * The most recently changed wiki pages, each with its latest change only.
     *
     * @return list<array{title: ?string, slug: ?string, event: string, user: ?string, createdAt: mixed}>
     */
    private function wikiChanges(): array
    {
        $latestPerPage = Audit::selectRaw('max(id)')
            ->where('auditable_type', (new WikiPage)->getMorphClass())
            ->groupBy('auditable_id');

        $audits = Audit::whereIn('id', $latestPerPage)->with('user')->latest('id')->limit(self::WIKI_CHANGES)->get();
        $pages = WikiPage::whereIn('id', $audits->pluck('auditable_id'))->get(['id', 'title', 'slug'])->keyBy('id');

        return $audits->map(function (Audit $audit) use ($pages) {
            // Deleted pages are gone, their title survives in the audit
            $page = $pages->get($audit->auditable_id);

            return [
                'title' => $page?->title ?? $audit->old_values['title'] ?? $audit->new_values['title'] ?? null,
                'slug' => $page?->slug,
                'event' => $audit->event,
                'user' => $audit->user?->name,
                'createdAt' => $audit->created_at,
            ];
        })->all();
    }
}
