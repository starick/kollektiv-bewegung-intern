<?php

namespace App\Models;

use App\Models\Concerns\HasAuditTrail;
use Database\Factories\WikiPageFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

class WikiPage extends Model implements Auditable
{
    use HasAuditTrail;

    /** @use HasFactory<WikiPageFactory> */
    use HasFactory;

    use HasUlids;

    /**
     * Slugs that would collide with fixed routes like `/wiki/create`.
     */
    public const RESERVED_SLUGS = ['create'];

    /**
     * Used when the title has nothing a slug can be made of, like `???`.
     */
    public const FALLBACK_SLUG = 'seite';

    /**
     * Longest slug before a counter is added, so even `-123` stays within the 255 character column.
     * Transliteration makes slugs longer than titles (`ä` becomes `ae`).
     */
    public const SLUG_LENGTH = 240;

    /**
     * `text` holds the page body as raw Markdown, not HTML.
     */
    protected $fillable = [
        'title',
        'category_id',
        'text',
        'pinned',
        'created_by',
        'edited_by',
    ];

    /**
     * The audit's user already says who edited, and the slug never changes after creation.
     */
    protected $auditExclude = [
        'edited_by',
        'slug',
    ];

    /**
     * Too long to show old and new value side by side, the trail shows the changed lines instead.
     */
    protected $auditDiffed = [
        'text',
    ];

    protected $casts = [
        'pinned' => 'boolean',
    ];

    protected static function booted(): void
    {
        // Only set once, so links to the page keep working after renaming or moving it
        static::creating(function (WikiPage $page) {
            $page->slug ??= $page->generateSlug();
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(WikiCategory::class, 'category_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function editor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    /**
     * The title as slug, like `stundenplan-anlegen`, with a counter if it is reserved or taken.
     */
    public function generateSlug(): string
    {
        $base = self::shorten(Str::slug($this->title ?? '', '-', 'de')) ?: self::FALLBACK_SLUG;

        $slug = $base;
        for ($suffix = 2; in_array($slug, self::RESERVED_SLUGS, true) || static::where('slug', $slug)->exists(); $suffix++) {
            $slug = "{$base}-{$suffix}";
        }

        return $slug;
    }

    /**
     * Cuts an overlong slug at a word boundary, only a single overlong word is cut in the middle.
     */
    private static function shorten(string $slug): string
    {
        if (strlen($slug) <= self::SLUG_LENGTH) {
            return $slug;
        }

        $cut = substr($slug, 0, self::SLUG_LENGTH + 1);
        $lastSeparator = strrpos($cut, '-');

        return $lastSeparator ? substr($cut, 0, $lastSeparator) : substr($slug, 0, self::SLUG_LENGTH);
    }

    /**
     * Records the category as its path instead of its id, so the history stays readable
     * after the category is renamed or deleted.
     *
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public function transformAudit(array $data): array
    {
        foreach (['old_values', 'new_values'] as $key) {
            if (! array_key_exists('category_id', $data[$key] ?? [])) {
                continue;
            }

            $categoryId = $data[$key]['category_id'];
            unset($data[$key]['category_id']);
            $data[$key]['category'] = $categoryId ? WikiCategory::find($categoryId)?->path() : null;
        }

        return $data;
    }
}
