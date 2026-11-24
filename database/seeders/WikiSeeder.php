<?php

namespace Database\Seeders;

use App\Models\WikiCategory;
use App\Models\WikiPage;
use Illuminate\Database\Seeder;

/**
 * Starter pages: guides about the tool itself under "Anleitungen › Das Tool",
 * and a pinned link collection to fill in.
 * Safe to run again: existing pages are left alone, so edits made in the wiki stay.
 *
 * php artisan db:seed --class=WikiSeeder
 */
class WikiSeeder extends Seeder
{
    // Title => Markdown file in database/seeders/wiki, its category (null: top level), pinned
    private const PAGES = [
        'Linksammlung' => ['linksammlung.md', null, true],
        'Stundenplan erstellen' => ['stundenplan-erstellen.md', 'tool', false],
        'Protokolle' => ['protokolle.md', 'tool', false],
        'Wiki-Seiten' => ['wiki-seiten.md', 'tool', false],
        'Markdown-Beispiele' => ['markdown-beispiele.md', 'tool', false],
    ];

    public function run(): void
    {
        $guides = WikiCategory::firstOrCreate(['name' => 'Anleitungen', 'parent_id' => null]);
        $tool = WikiCategory::firstOrCreate(['name' => 'Das Tool', 'parent_id' => $guides->id]);

        // Slugs are known before saving, so pages can link to each other via {{wiki:Title}}
        $pages = [];
        foreach (self::PAGES as $title => [, $category, $pinned]) {
            $categoryId = $category === 'tool' ? $tool->id : null;
            $pages[$title] = WikiPage::where('category_id', $categoryId)->where('title', $title)->first()
                ?? (new WikiPage)->forceFill(['title' => $title, 'category_id' => $categoryId, 'pinned' => $pinned]);
            $pages[$title]->slug ??= $pages[$title]->generateSlug();
        }

        foreach ($pages as $title => $page) {
            if ($page->exists) {
                continue;
            }

            $text = file_get_contents(__DIR__.'/wiki/'.self::PAGES[$title][0]);
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
