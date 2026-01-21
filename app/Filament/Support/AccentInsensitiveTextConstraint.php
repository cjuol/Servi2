<?php

namespace App\Filament\Support;

use Filament\Tables\Filters\QueryBuilder\Constraints\TextConstraint;
use Illuminate\Database\Eloquent\Builder;

class AccentInsensitiveTextConstraint extends TextConstraint
{
    public function apply(Builder $query, string $column = null): Builder
    {
        $column = $column ?? $this->getColumn();
        $value = $this->getValue();

        if (blank($value)) {
            return $query;
        }

        if (config('database.default') === 'pgsql') {
            return $query->whereRaw("unaccent(LOWER({$column}::text)) LIKE unaccent(LOWER(?))", ["%{$value}%"]);
        }

        return $query->where($column, 'ILIKE', "%{$value}%");
    }
}
