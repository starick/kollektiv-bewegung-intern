<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Protocol extends Model
{
    /** @use HasFactory<\Database\Factories\ProtocolFactory> */
    use HasFactory;

    /**
     * `text` holds the protocol body as raw Markdown, not HTML.
     */
    protected $fillable = [
        'date',
        'description',
        'topics',
        'text',
        'uploaded_by',
        'edited_by',
    ];

    protected $casts = [
        'date' => 'date',
        'topics' => 'array',
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
