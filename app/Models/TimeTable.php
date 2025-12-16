<?php

namespace App\Models;

use Carbon\Carbon;
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

    public function getStartTimeAttribute()
    {
        if ($this->year && $this->week) {
            $dt = Carbon::now();
            $dt->setISODate($this->year, $this->week, 1)->setTime(0, 0, 0);
            return $dt->format('Y-m-d H:i:s');
        }
        return null;
    }

    public function getEndTimeAttribute()
    {
        if ($this->year && $this->week) {
            $dt = Carbon::now();
            $dt->setISODate($this->year, $this->week, 7)->setTime(23, 59, 59);
            return $dt->format('Y-m-d H:i:s');
        }
        return null;
    }

    public function isWithinTimeRange(Carbon $dateTime): bool
    {
        $start = Carbon::parse($this->start_time);
        $end = Carbon::parse($this->end_time);

        return $dateTime->between($start, $end);
    }
 
    public function courses()
    {
        return $this->hasMany(Course::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
