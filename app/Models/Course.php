<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    /** @use HasFactory<\Database\Factories\CourseFactory> */
    use HasFactory;
    

    protected $fillable = [
        'time_table_id',
        'name',
        'instructor',
        'start_time',
        'end_time',
        'location',
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];


    public function timeTable()
    {
        return $this->belongsTo(TimeTable::class);
    }
}
