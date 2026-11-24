<?php

namespace Tests\Feature\Wiki;

use App\Markdown\Markdown;
use App\Models\WikiCategory;
use App\Models\WikiPage;
use Database\Seeders\WikiSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WikiSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_the_tool_guides_in_a_category(): void
    {
        $this->seed(WikiSeeder::class);

        $category = WikiCategory::where('name', 'Das Tool')->firstOrFail();
        $this->assertSame('Anleitungen › Das Tool', $category->path());
        $this->assertEqualsCanonicalizing(
            ['Stundenplan erstellen', 'Protokolle', 'Wiki-Seiten', 'Markdown-Beispiele'],
            $category->pages()->pluck('title')->all(),
        );
        $this->assertTrue($category->pages()->get()->every(fn (WikiPage $page) => trim($page->text) !== ''));
    }

    public function test_it_creates_a_pinned_link_collection_on_the_top_level(): void
    {
        $this->seed(WikiSeeder::class);

        $page = WikiPage::where('title', 'Linksammlung')->firstOrFail();
        $this->assertNull($page->category_id);
        $this->assertTrue($page->pinned);
        $this->assertNotSame('', trim($page->text));
    }

    public function test_running_it_twice_creates_nothing_twice(): void
    {
        $this->seed(WikiSeeder::class);
        $this->seed(WikiSeeder::class);

        $this->assertSame(2, WikiCategory::count());
        $this->assertSame(5, WikiPage::count());
    }

    public function test_it_leaves_edited_pages_alone(): void
    {
        $this->seed(WikiSeeder::class);
        $page = WikiPage::where('title', 'Protokolle')->firstOrFail();
        $page->update(['text' => 'Unser eigener Text']);

        $this->seed(WikiSeeder::class);

        $this->assertSame('Unser eigener Text', $page->fresh()->text);
    }

    public function test_links_between_the_pages_point_to_existing_pages(): void
    {
        $this->seed(WikiSeeder::class);

        $slugs = WikiPage::pluck('slug')->all();
        foreach (WikiPage::all() as $page) {
            $this->assertStringNotContainsString('{{', $page->text, $page->title);
            preg_match_all('~\]\(/wiki/([^)#?]+)\)~', $page->text, $links);
            foreach ($links[1] as $slug) {
                $this->assertContains($slug, $slugs, "{$page->title} links to /wiki/{$slug}");
            }
        }
    }

    public function test_the_markdown_example_renders_its_features(): void
    {
        $this->seed(WikiSeeder::class);

        $html = Markdown::toHtml(WikiPage::where('title', 'Markdown-Beispiele')->firstOrFail()->text);

        foreach (['<table>', '<blockquote>', '<del>', 'type="checkbox"', '<pre><code', 'data-video-embed'] as $feature) {
            $this->assertStringContainsString($feature, $html);
        }
    }
}
