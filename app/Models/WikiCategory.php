<?php

namespace App\Models;

use Database\Factories\WikiCategoryFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

/**
 * A folder in the wiki tree. Categories only file pages, so they are not audited.
 */
class WikiCategory extends Model
{
    /** @use HasFactory<WikiCategoryFactory> */
    use HasFactory;

    use HasUlids;

    public const PATH_SEPARATOR = ' › ';

    protected $fillable = [
        'name',
        'parent_id',
    ];

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function pages(): HasMany
    {
        return $this->hasMany(WikiPage::class, 'category_id');
    }

    /**
     * The categories above this one, the top-level one first.
     *
     * @return Collection<int, WikiCategory>
     */
    public function ancestors(): Collection
    {
        $ancestors = collect();
        $seen = [$this->getKey()];

        for ($category = $this->parent; $category !== null; $category = $category->parent) {
            // A cycle can't be saved through the app, the guard only protects against broken data
            if (in_array($category->getKey(), $seen, true)) {
                break;
            }

            $seen[] = $category->getKey();
            $ancestors->prepend($category);
        }

        return $ancestors;
    }

    /**
     * Names from the top level down to this category, like `Anleitungen › Stundenplan`.
     */
    public function path(): string
    {
        return $this->ancestors()->push($this)->pluck('name')->implode(self::PATH_SEPARATOR);
    }

    public function isDescendantOf(WikiCategory $other): bool
    {
        return $this->ancestors()->contains(fn (WikiCategory $ancestor) => $ancestor->is($other));
    }
}
