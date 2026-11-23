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
