<?php

namespace App\Wiki;

use App\Models\WikiCategory;
use App\Models\WikiPage;
use Illuminate\Support\Collection;

/**
 * The whole wiki as a nested tree, built from two queries. A team wiki has dozens of
 * categories, so assembling it in PHP is cheaper than recursive queries.
 */
class WikiTree
{
    /** @var Collection<string, WikiCategory> */
    private Collection $categories;

    public function __construct()
    {
        $this->categories = WikiCategory::query()->get(['id', 'parent_id', 'name'])->keyBy('id');
    }

    /**
     * Categories with their subcategories and pages, plus the pages without a category.
     * Sorted alphabetically ignoring case, categories before pages.
     *
     * @return array{categories: array<int, array<string, mixed>>, rootPages: array<int, array<string, mixed>>}
     */
    public function build(): array
    {
        $pages = WikiPage::query()
            ->get(['id', 'title', 'slug', 'category_id', 'updated_at'])
            ->sortBy(fn (WikiPage $page) => mb_strtolower($page->title))
            ->groupBy(fn (WikiPage $page) => $page->category_id ?? '');
        $children = $this->categories
            ->sortBy(fn (WikiCategory $category) => mb_strtolower($category->name))
            ->groupBy(fn (WikiCategory $category) => $category->parent_id ?? '');

        $node = function (WikiCategory $category) use (&$node, $children, $pages): array {
            return [
                'id' => $category->id,
                'name' => $category->name,
                'parentId' => $category->parent_id,
                'children' => ($children[$category->id] ?? collect())->map($node)->values()->all(),
                'pages' => ($pages[$category->id] ?? collect())->map(fn (WikiPage $page) => self::link($page))->values()->all(),
            ];
        };

        return [
            'categories' => ($children[''] ?? collect())->map($node)->values()->all(),
            'rootPages' => ($pages[''] ?? collect())->map(fn (WikiPage $page) => self::link($page))->values()->all(),
        ];
    }

    /**
     * Every category with its full path, sorted by path, for selecting a page's or category's parent.
     *
     * @return array<int, array{id: string, path: string}>
     */
    public function categoryOptions(): array
    {
        return $this->categories
            ->map(fn (WikiCategory $category) => ['id' => $category->id, 'path' => $this->path($category->id)])
            ->sortBy(fn (array $option) => mb_strtolower($option['path']))
            ->values()
            ->all();
    }

    /**
     * Path of a category from the loaded categories, without further queries.
     */
    public function path(?string $categoryId): ?string
    {
        $names = [];

        // The visited list guards against broken data with a cycle
        while ($categoryId !== null && ($category = $this->categories->get($categoryId)) && ! array_key_exists($categoryId, $names)) {
            $names[$categoryId] = $category->name;
            $categoryId = $category->parent_id;
        }

        return $names ? implode(WikiCategory::PATH_SEPARATOR, array_reverse($names)) : null;
    }

    /**
     * @return array{title: string, slug: string, updatedAt: mixed}
     */
    public static function link(WikiPage $page): array
    {
        return [
            'title' => $page->title,
            'slug' => $page->slug,
            'updatedAt' => $page->updated_at,
        ];
    }
}
