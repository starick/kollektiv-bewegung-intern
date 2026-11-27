<?php

namespace App\Http\Controllers;

use App\Markdown\Markdown;
use App\Models\WikiPage;
use App\Wiki\Changelog;
use App\Wiki\WikiTree;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;

class WikiChangelogController extends Controller
{
    public function __invoke()
    {
        Gate::authorize('viewAny', WikiPage::class);

        $changelog = Changelog::fromRepository();
        abort_unless($changelog->exists(), 404);

        return Inertia::render('Wiki/Changelog', [
            'version' => $changelog->version(),
            'html' => Markdown::toHtml($changelog->markdown()),
            // Shown as a sidebar on wide screens
            'tree' => (new WikiTree)->build(),
            'pinned' => WikiTree::pinned(),
            'changelog' => $changelog->link(),
        ]);
    }
}
