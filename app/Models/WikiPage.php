<?php

namespace App\Models;

use Database\Factories\WikiPageFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WikiPage extends Model
{
    /** @use HasFactory<WikiPageFactory> */
    use HasFactory;

    use HasUlids;

    protected $fillable = [
        'title',
        'category_id',
        'text',
        'pinned',
        'created_by',
        'edited_by',
    ];
}
