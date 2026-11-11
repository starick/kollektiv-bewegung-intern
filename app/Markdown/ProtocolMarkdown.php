<?php

namespace App\Markdown;

use Illuminate\Support\Str;

/**
 * Renders protocol Markdown the same way for the protocol page and the edit preview.
 */
class ProtocolMarkdown
{
    public static function toHtml(string $markdown): string
    {
        // Raw HTML in the Markdown is escaped so protocols can't inject markup or scripts.
        return Str::markdown($markdown, [
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ], [new DropImagesExtension]);
    }
}
