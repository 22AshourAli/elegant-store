<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\Relation;
use InvalidArgumentException;

class CursorService
{
    public static function encode(array $payload): string
    {
        return base64_encode(json_encode($payload));
    }

    public static function decode(string $cursor): ?array
    {
        $decoded = base64_decode($cursor, true);
        if ($decoded === false) {
            return null;
        }
        $payload = json_decode($decoded, true);
        return is_array($payload) ? $payload : null;
    }

    public static function encodeSigned(array $payload): string
    {
        $key = config('app.key');
        if (!$key) {
            throw new InvalidArgumentException('APP_KEY is not set');
        }
        $iv = substr(hash('sha256', $key, true), 0, 16);
        $encrypted = openssl_encrypt(
            json_encode($payload),
            'aes-256-cbc',
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );
        if ($encrypted === false) {
            throw new InvalidArgumentException('Encryption failed');
        }
        return base64_encode($encrypted);
    }

    public static function decodeSigned(string $cursor): ?array
    {
        $key = config('app.key');
        if (!$key) {
            return null;
        }
        $iv = substr(hash('sha256', $key, true), 0, 16);
        $decoded = base64_decode($cursor, true);
        if ($decoded === false) {
            return null;
        }
        $decrypted = openssl_decrypt($decoded, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);
        if ($decrypted === false) {
            return null;
        }
        $payload = json_decode($decrypted, true);
        return is_array($payload) ? $payload : null;
    }

    /**
     * Apply cursor-based pagination to a query.
     *
     * @param Builder $query  Eloquent query builder
     * @param string|null $cursor  Base64-encoded cursor string
     * @param string $sortColumn  Column to sort by (default: created_at)
     * @param string $direction  Sort direction: 'asc' or 'desc' (default: desc)
     * @param int $limit  Results per page (default: 20)
     * @param string $tiebreaker  Secondary sort column for stable ordering (default: id)
     * @return array{data: \Illuminate\Support\Collection, next_cursor: string|null, prev_cursor: string|null, has_more: bool, per_page: int}
     */
    public static function applyCursor(
        Builder|Relation $query,
        ?string $cursor,
        string $sortColumn = 'created_at',
        string $direction = 'desc',
        int $limit = 20,
        string $tiebreaker = 'id',
    ): array {
        $table = $query->getModel()->getTable();
        $payload = $cursor ? self::decode($cursor) : null;

        if ($payload) {
            $sortValue = $payload['s'] ?? null;
            $tiebreakerValue = $payload['i'] ?? null;
            $isPrev = ($payload['d'] ?? 'n') === 'p';

            $effectiveDirection = $isPrev ? 'asc' : 'desc';
            $op = $effectiveDirection === 'desc' ? '<' : '>';

            $sortCol = "{$table}.{$sortColumn}";
            $tieCol = "{$table}.{$tiebreaker}";

            if ($sortValue !== null && $tiebreakerValue !== null) {
                $query->where(function (Builder $q) use ($sortCol, $sortValue, $op, $tieCol, $tiebreakerValue, $tiebreaker, $sortColumn, $effectiveDirection) {
                    $q->where($sortCol, $op, $sortValue);
                    if ($tiebreaker !== $sortColumn) {
                        $q->orWhere(function (Builder $q) use ($sortCol, $sortValue, $tieCol, $tiebreakerValue, $op, $effectiveDirection) {
                            $q->where($sortCol, $effectiveDirection === 'desc' ? '<=' : '>=', $sortValue)
                              ->where($tieCol, $op, $tiebreakerValue);
                        });
                    }
                });
            }
        }

        $sortDir = $direction;

        $query->orderBy("{$table}.{$sortColumn}", $sortDir)
              ->orderBy("{$table}.{$tiebreaker}", $sortDir);

        $results = $query->limit($limit + 1)->get();

        $hasMore = $results->count() > $limit;
        if ($hasMore) {
            $results = $results->slice(0, $limit);
        }

        $nextCursor = null;
        $prevCursor = null;

        if ($results->isNotEmpty()) {
            $last = $results->last();
            $first = $results->first();

            if ($hasMore) {
                $nextCursor = self::encode([
                    's' => $last->{$sortColumn},
                    'i' => $last->{$tiebreaker},
                    'd' => 'n',
                ]);
            }

            $prevCursor = self::encode([
                's' => $first->{$sortColumn},
                'i' => $first->{$tiebreaker},
                'd' => 'p',
            ]);
        }

        return [
            'data' => $results,
            'next_cursor' => $nextCursor,
            'prev_cursor' => $prevCursor,
            'has_more' => $hasMore,
            'per_page' => $limit,
        ];
    }
}
