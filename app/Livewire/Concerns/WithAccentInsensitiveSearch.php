<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

trait WithAccentInsensitiveSearch
{
    /**
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     * @param  list<string>  $columns
     */
    private function applyAccentInsensitiveSearch(Builder $query, string $search, array $columns): void
    {
        $grammar = DB::connection()->getQueryGrammar();
        $words = preg_split('/\s+/', trim($search)) ?: [];

        foreach ($words as $word) {
            $query->where(function (Builder $q) use ($word, $columns, $grammar): void {
                foreach ($columns as $column) {
                    // @phpstan-ignore argument.type (column is a hardcoded whitelist supplied by the caller, never user input)
                    $q->orWhereRaw('unaccent(COALESCE('.$grammar->wrap($column).", '')) ILIKE unaccent(?)", ['%'.$word.'%']);
                }
            });
        }
    }
}
