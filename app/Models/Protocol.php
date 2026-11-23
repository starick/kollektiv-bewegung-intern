<?php

namespace App\Models;

use App\Models\Concerns\HasAuditTrail;
use Database\Factories\ProtocolFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use OwenIt\Auditing\Contracts\Auditable;

class Protocol extends Model implements Auditable
{
    use HasAuditTrail;

    /** @use HasFactory<ProtocolFactory> */
    use HasFactory;

    use HasUlids;

    /**
     * `text` holds the protocol body as raw Markdown, not HTML.
     */
    protected $fillable = [
        'date',
        'description',
        'topics',
        'participants',
        'text',
        'uploaded_by',
        'edited_by',
    ];

    /**
     * The audit's user already says who edited, so the editor column would only add noise.
     */
    protected $auditExclude = [
        'edited_by',
        'share_token',
        // Derived from date and description, which are recorded already
        'slug',
    ];

    protected $hidden = [
        'share_token',
    ];

    /**
     * Too long to show old and new value side by side, the trail shows the changed lines instead.
     */
    protected $auditDiffed = [
        'text',
    ];

    protected $casts = [
        'date' => 'date',
        'topics' => 'array',
        'participants' => 'array',
    ];

    protected static function booted(): void
    {
        // Rewording the description keeps the URL stable, a new date or adding/removing the description changes it
        static::saving(function (Protocol $protocol) {
            $descriptionAddedOrRemoved = $protocol->isDirty('description')
                && blank($protocol->getOriginal('description')) !== blank($protocol->description);

            if (! $protocol->slug || $protocol->isDirty('date') || $descriptionAddedOrRemoved) {
                $protocol->slug = $protocol->generateSlug();
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Longest description part of a slug, longer descriptions are cut at a word boundary.
     */
    public const SLUG_DESCRIPTION_LENGTH = 40;

    /**
     * Date plus description, like `2026-11-02-plenum`, with a counter if another protocol has it already.
     */
    public function generateSlug(): string
    {
        $base = collect([$this->date->toDateString(), static::descriptionSlug($this->description)])
            ->filter()
            ->implode('-');

        $slug = $base;
        for ($suffix = 2; static::where('slug', $slug)->whereKeyNot($this->getKey())->exists(); $suffix++) {
            $slug = "{$base}-{$suffix}";
        }

        return $slug;
    }

    private static function descriptionSlug(?string $description): string
    {
        $slug = Str::slug($description ?? '', '-', 'de');

        if (strlen($slug) <= self::SLUG_DESCRIPTION_LENGTH) {
            return $slug;
        }

        // Keep whole words, only a single overlong word is cut in the middle
        $cut = substr($slug, 0, self::SLUG_DESCRIPTION_LENGTH + 1);
        $lastSeparator = strrpos($cut, '-');

        return $lastSeparator ? substr($cut, 0, $lastSeparator) : substr($slug, 0, self::SLUG_DESCRIPTION_LENGTH);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function editor()
    {
        return $this->belongsTo(User::class, 'edited_by');
    }

    /**
     * Gives the protocol a new public share token, replacing any previous one.
     */
    public function share(): void
    {
        // Saved quietly, sharing is not an edit of the protocol and must not touch updated_at
        static::withoutTimestamps(fn () => $this->forceFill(['share_token' => Str::random(40)])->saveQuietly());
    }

    public function unshare(): void
    {
        static::withoutTimestamps(fn () => $this->forceFill(['share_token' => null])->saveQuietly());
    }

    public function shareUrl(): ?string
    {
        return $this->share_token ? route('protocols.public', $this->share_token) : null;
    }
}
