<?php

namespace App\Models;

use App\Models\Concerns\HasAuditTrail;
use Database\Factories\ProtocolFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use OwenIt\Auditing\Contracts\Auditable;

class Protocol extends Model implements Auditable
{
    use HasAuditTrail;

    /** @use HasFactory<ProtocolFactory> */
    use HasFactory;

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
}
