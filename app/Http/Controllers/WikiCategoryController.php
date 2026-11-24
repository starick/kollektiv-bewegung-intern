<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreWikiCategoryRequest;
use App\Http\Requests\UpdateWikiCategoryRequest;
use App\Models\WikiCategory;
use Illuminate\Support\Facades\Gate;

class WikiCategoryController extends Controller
{
    public function store(StoreWikiCategoryRequest $request)
    {
        WikiCategory::create([
            'name' => trim($request->validated('name')),
            'parent_id' => $request->validated('parent_id'),
        ]);

        return back()->with('success', __('Category saved.'));
    }

    public function update(UpdateWikiCategoryRequest $request, WikiCategory $wikiCategory)
    {
        $wikiCategory->update([
            'name' => trim($request->validated('name')),
            'parent_id' => $request->validated('parent_id'),
        ]);

        return back()->with('success', __('Category saved.'));
    }

    /**
     * Only empty categories can be deleted, so no page ever loses its place.
     */
    public function destroy(WikiCategory $wikiCategory)
    {
        Gate::authorize('delete', $wikiCategory);

        if ($wikiCategory->children()->exists() || $wikiCategory->pages()->exists()) {
            return back()->withErrors(['category' => __('Only empty categories can be deleted.')]);
        }

        $wikiCategory->delete();

        return back()->with('success', __('Category deleted.'));
    }
}
