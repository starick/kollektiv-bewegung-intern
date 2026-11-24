<?php

namespace App\Markdown;

use Illuminate\Support\Str;

/**
 * Renders Markdown the same way for protocol and wiki pages and the edit preview.
 */
class Markdown
{
    public static function toHtml(string $markdown): string
    {
        // Raw HTML in the Markdown is escaped so pages can't inject markup or scripts.
        return Str::markdown($markdown, [
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ], [new DropImagesExtension, new VideoEmbedExtension]);
    }
}
