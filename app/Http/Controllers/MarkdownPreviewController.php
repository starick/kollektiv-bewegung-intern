<?php

namespace App\Http\Controllers;

use App\Http\Requests\PreviewMarkdownRequest;
use App\Markdown\Markdown;

class MarkdownPreviewController extends Controller
{
    /**
     * Render Markdown for an edit preview without saving anything.
     */
    public function __invoke(PreviewMarkdownRequest $request)
    {
        return response()->json([
            'html' => Markdown::toHtml((string) $request->validated('text')),
        ]);
    }
}
