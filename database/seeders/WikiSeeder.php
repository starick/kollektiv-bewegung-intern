<?php

namespace Database\Seeders;

use App\Models\WikiCategory;
use App\Models\WikiPage;
use Illuminate\Database\Seeder;

/**
 * Starter guides about the tool itself, under "Anleitungen › Das Tool".
 * Safe to run again: existing pages are left alone, so edits made in the wiki stay.
 *
 * php artisan db:seed --class=WikiSeeder
 */
class WikiSeeder extends Seeder
{
    // Title => Markdown file in database/seeders/wiki
    private const PAGES = [
        'Stundenplan erstellen' => 'stundenplan-erstellen.md',
        'Protokolle' => 'protokolle.md',
        'Wiki-Seiten' => 'wiki-seiten.md',
        'Markdown-Beispiele' => 'markdown-beispiele.md',
    ];

    public function run(): void
    {
        $guides = WikiCategory::firstOrCreate(['name' => 'Anleitungen', 'parent_id' => null]);
        $tool = WikiCategory::firstOrCreate(['name' => 'Das Tool', 'parent_id' => $guides->id]);

        // Slugs are known before saving, so pages can link to each other via {{wiki:Title}}
        $pages = [];
        foreach (array_keys(self::PAGES) as $title) {
            $pages[$title] = WikiPage::where('category_id', $tool->id)->where('title', $title)->first()
                ?? (new WikiPage)->forceFill(['title' => $title, 'category_id' => $tool->id]);
            $pages[$title]->slug ??= $pages[$title]->generateSlug();
        }

        foreach ($pages as $title => $page) {
            if ($page->exists) {
                continue;
            }

            $text = file_get_contents(__DIR__.'/wiki/'.self::PAGES[$title]);
            $page->forceFill([
                'text' => preg_replace_callback(
                    '/\{\{wiki:([^}]+)\}\}/',
                    fn (array $match) => '/wiki/'.$pages[$match[1]]->slug,
                    $text,
                ),
            ])->save();
        }
    }
}
