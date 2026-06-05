<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Shared index component behavior for search, sort, view mode, and pagination.
 *
 * Using classes must define:
 *   - private const ALLOWED_SORT_COLUMNS (string[])
 *   - public string $sortBy
 *   - public string $sortDirection
 *   - public string $viewMode
 */
trait WithIndexFiltering
{
    public function sort(string $column): void
    {
        if ($this->sortBy === $column) {
            $this->sortDirection = $this->sortDirection === 'asc' ? 'desc' : 'asc';
        } else {
            $this->sortBy = $column;
            $this->sortDirection = 'asc';
        }
    }

    protected function safeSortDirection(): string
    {
        return $this->sortDirection === 'asc' ? 'asc' : 'desc';
    }

    protected function safeSortBy(): string
    {
        return in_array($this->sortBy, self::ALLOWED_SORT_COLUMNS, true)
            ? $this->sortBy
            : $this->defaultSortColumn();
    }

    public function setViewMode(string $mode): void
    {
        $this->viewMode = in_array($mode, ['gallery', 'list'], true)
            ? $mode
            : $this->defaultViewMode();
    }

    /**
     * Default sort column when the requested one is not allowed.
     * Using classes may override to customise.
     */
    protected function defaultSortColumn(): string
    {
        return 'updated_at';
    }

    /**
     * Default view mode when the requested one is invalid.
     * Using classes may override to customise.
     */
    protected function defaultViewMode(): string
    {
        return 'gallery';
    }

    public function paginationView(): string
    {
        return 'livewire.custom-pagination';
    }

    /**
     * Order by a nullable column with PostgreSQL `NULLS LAST` semantics.
     *
     * The column must already be validated against the component's
     * ALLOWED_SORT_COLUMNS whitelist (e.g. via safeSortBy()/in_array());
     * it is never user-controlled SQL.
     *
     * @template TModel of Model
     *
     * @param  Builder<TModel>  $query
     */
    protected function applyNullsLastOrder(Builder $query, string $column, string $direction): void
    {
        $dir = $direction === 'asc' ? 'asc' : 'desc';

        // @phpstan-ignore argument.type ($column is a whitelist-validated identifier, not user input)
        $query->orderByRaw("\"$column\" $dir NULLS LAST");
    }
}
