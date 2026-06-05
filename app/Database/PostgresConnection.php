<?php

namespace App\Database;

use DateTimeInterface;

class PostgresConnection extends \Illuminate\Database\PostgresConnection
{
    protected function escapeBool($value)
    {
        return $value ? 'true' : 'false';
    }

    public function prepareBindings(array $bindings)
    {
        $grammar = $this->getQueryGrammar();

        foreach ($bindings as $key => $value) {
            if ($value instanceof DateTimeInterface) {
                $bindings[$key] = $value->format($grammar->getDateFormat());
            } elseif (is_bool($value)) {
                $bindings[$key] = $value ? 'true' : 'false';
            }
        }

        return $bindings;
    }
}
