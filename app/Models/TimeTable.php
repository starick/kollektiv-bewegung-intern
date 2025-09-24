<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TimeTable extends Model
{
    /** @use HasFactory<\Database\Factories\TimeTableFactory> */
    use HasFactory;

    protected $fillable = [
        'year',
        'week',
        'created_by',
        'display_config',
    ];

    protected $casts = [
        'display_config' => 'array',
    ];

    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
