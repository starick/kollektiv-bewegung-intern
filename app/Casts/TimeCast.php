<?php

namespace App\Casts;

use App\ValueObjects\Time;
use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class TimeCast implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): ?Time
    {
        return $value === null ? null : Time::parse($value);
    }

    public function set($model, string $key, $value, array $attributes): ?string
    {
        if ($value === null) {
            return null;
        }

        if ($value instanceof Time) {
            return $value->toString();
        }

        return Time::parse($value)->toString();
    }
}
