<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWikiPageRequest;
use App\Http\Requests\UpdateWikiPageRequest;
use App\Http\Resources\AuditResource;
use App\Http\Resources\WikiPageResource;
use App\Markdown\Markdown;
use App\Models\WikiCategory;
use App\Models\WikiPage;
use App\Wiki\Changelog;
use App\Wiki\WikiTree;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class WikiPageController extends Controller
{
    /**
     * The category tree with pinned pages on top, or search results while searching.
     */
    public function index(Request $request)
    {
        Gate::authorize('viewAny', WikiPage::class);

        $search = is_string($request->input('search')) ? trim($request->input('search')) : '';
        $tree = new WikiTree;

        $results = null;
        if ($search !== '') {
            // `!` as escape character works the same in MySQL and SQLite, a backslash doesn't
            $like = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $search).'%';

            $results = WikiPage::query()
                ->where(fn ($query) => $query
                    ->whereRaw("title LIKE ? ESCAPE '!'", [$like])
                    ->orWhereRaw("text LIKE ? ESCAPE '!'", [$like]))
                ->get(['id', 'title', 'slug', 'category_id', 'updated_at'])
                ->sortBy(fn (WikiPage $page) => mb_strtolower($page->title))
                ->map(fn (WikiPage $page) => [...WikiTree::link($page), 'path' => $tree->path($page->category_id)])
                ->values()
                ->all();
        }

        return Inertia::render('Wiki/Index', [
            ...$tree->build(),
            'pinned' => WikiTree::pinned(),
            'changelog' => Changelog::fromRepository()->link(),
            'results' => $results,
            'filters' => ['search' => $search],
            'categoryOptions' => $tree->categoryOptions(),
            'can' => ['create' => $request->user()->can('create', WikiPage::class)],
        ]);
    }

    public function create(Request $request)
    {
        Gate::authorize('create', WikiPage::class);

        $categoryId = $request->string('category')->toString();

        return Inertia::render('Wiki/Create', [
            'categoryOptions' => (new WikiTree)->categoryOptions(),
            // Preselected when the page is created from a category in the tree
            'categoryId' => WikiCategory::whereKey($categoryId)->exists() ? $categoryId : null,
        ]);
    }

    public function store(StoreWikiPageRequest $request)
    {
        $page = WikiPage::create([
            ...$request->pageAttributes(),
            'created_by' => auth()->id(),
        ]);

        return redirect()->route('wiki.show', $page)->with('success', __('Page saved.'));
    }

    public function show(Request $request, WikiPage $page)
    {
        Gate::authorize('view', $page);

        return Inertia::render('Wiki/Show', [
            'page' => (new WikiPageResource($page->load('creator', 'editor')))->resolve(),
            'breadcrumbs' => $page->category
                ? $page->category->ancestors()->push($page->category)
                    ->map(fn (WikiCategory $category) => $category->only('id', 'name'))
                    ->values()
                    ->all()
                : [],
            'html' => Markdown::toHtml($page->text),
            // Shown as a sidebar on wide screens
            'tree' => (new WikiTree)->build(),
            'pinned' => WikiTree::pinned(),
            'changelog' => Changelog::fromRepository()->link(),
            'audits' => AuditResource::collection($page->auditTrail())->resolve(),
            'can' => [
                'update' => $request->user()->can('update', $page),
                'delete' => $request->user()->can('delete', $page),
            ],
        ]);
    }

    public function edit(WikiPage $page)
    {
        Gate::authorize('update', $page);

        return Inertia::render('Wiki/Edit', [
            'page' => (new WikiPageResource($page->load('creator', 'editor')))->resolve(),
            'text' => $page->text,
            'categoryOptions' => (new WikiTree)->categoryOptions(),
        ]);
    }

    public function update(UpdateWikiPageRequest $request, WikiPage $page)
    {
        $page->update([
            ...$request->pageAttributes(),
            'edited_by' => auth()->id(),
        ]);

        return redirect()->route('wiki.show', $page)->with('success', __('Page saved.'));
    }

    public function destroy(WikiPage $page)
    {
        Gate::authorize('delete', $page);

        $page->delete();

        return redirect()->route('wiki.index')->with('success', __('Page deleted.'));
    }
}
