<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;

class PostgresBoolean implements CastsAttributes
{
    public function get($model, string $key, $value, array $attributes): bool
    {
        return filter_var($value, FILTER_VALIDATE_BOOLEAN);
    }

    public function set($model, string $key, $value, array $attributes): array
    {
        return [$key => $value ? 'true' : 'false'];
    }
}
