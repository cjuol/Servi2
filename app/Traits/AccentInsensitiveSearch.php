<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;

trait AccentInsensitiveSearch
{
    /**
     * Scope a query to search without accents.
     */
    public function scopeSearchUnaccented(Builder $query, string $column, string $search): Builder
    {
        if (config('database.default') === 'pgsql') {
            return $query->whereRaw("unaccent(LOWER({$column})) LIKE unaccent(LOWER(?))", ["%{$search}%"]);
        }
        
        // Para MySQL/MariaDB, utf8mb4_unicode_ci ya es insensible a acentos
        return $query->where($column, 'ILIKE', "%{$search}%");
    }

    /**
     * Scope a query to search across multiple columns without accents.
     */
    public function scopeSearchUnaccentedMultiple(Builder $query, array $columns, string $search): Builder
    {
        return $query->where(function ($q) use ($columns, $search) {
            foreach ($columns as $column) {
                if (config('database.default') === 'pgsql') {
                    $q->orWhereRaw("unaccent(LOWER({$column})) LIKE unaccent(LOWER(?))", ["%{$search}%"]);
                } else {
                    $q->orWhere($column, 'ILIKE', "%{$search}%");
                }
            }
        });
    }
}
