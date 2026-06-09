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
        $bool = $value ? 'true' : 'false';
        return [$key => \Illuminate\Support\Facades\DB::connection()->getDriverName() === 'mysql' ? ($value ? 1 : 0) : $bool];
    }
}
