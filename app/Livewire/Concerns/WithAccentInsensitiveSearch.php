<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

trait WithAccentInsensitiveSearch
{
    private function applyAccentInsensitiveSearch($query, string $search, array $columns): void
    {
        $words = preg_split('/\s+/', trim($search));

        foreach ($words as $word) {
            $query->where(function ($q) use ($word, $columns) {
                foreach ($columns as $column) {
                    $q->orWhereRaw('unaccent(COALESCE(' . $column . ", '')) ILIKE unaccent(?)", ['%' . $word . '%']);
                }
            });
        }
    }
}
