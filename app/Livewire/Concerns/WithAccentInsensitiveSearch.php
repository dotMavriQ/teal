<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use Illuminate\Support\Facades\DB;

trait WithAccentInsensitiveSearch
{
    private function applyAccentInsensitiveSearch($query, string $search, array $columns): void
    {
        $grammar = DB::connection()->getQueryGrammar();
        $words = preg_split('/\s+/', trim($search));

        foreach ($words as $word) {
            $query->where(function ($q) use ($word, $columns, $grammar) {
                foreach ($columns as $column) {
                    $q->orWhereRaw('unaccent(COALESCE(' . $grammar->wrap($column) . ", '')) ILIKE unaccent(?)", ['%' . $word . '%']);
                }
            });
        }
    }
}
