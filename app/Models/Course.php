<?php

namespace App\Models;

use App\Casts\TimeCast;
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
        'date',
        'start_time',
        'end_time',
        'location',
    ];

    protected $casts = [
        'start_time' => TimeCast::class,
        'end_time' => TimeCast::class,
        'date' => 'datetime',
    ];


    public function timeTable()
    {
        return $this->belongsTo(TimeTable::class);
    }
}
