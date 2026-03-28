<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

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
        $default = defined('static::DEFAULT_SORT_COLUMN') ? static::DEFAULT_SORT_COLUMN : 'updated_at';

        return in_array($this->sortBy, self::ALLOWED_SORT_COLUMNS, true) ? $this->sortBy : $default;
    }

    public function setViewMode(string $mode): void
    {
        $default = defined('static::DEFAULT_VIEW_MODE') ? static::DEFAULT_VIEW_MODE : 'gallery';
        $this->viewMode = in_array($mode, ['gallery', 'list']) ? $mode : $default;
    }

    public function paginationView(): string
    {
        return 'livewire.custom-pagination';
    }
}
