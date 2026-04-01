<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

trait WithMetadataEnrichment
{
    /**
     * Return the property name holding the items needing enrichment.
     * e.g. 'booksNeedingEnrichment', 'moviesNeedingEnrichment', 'animeNeedingEnrichment'
     */
    abstract protected function enrichmentListProperty(): string;

    /**
     * Return the property name holding the reviewing item ID.
     * e.g. 'reviewingBookId', 'reviewingMovieId', 'reviewingAnimeId'
     */
    abstract protected function reviewingIdProperty(): string;

    /**
     * Return the property name holding the reviewing item data.
     * e.g. 'reviewingBook', 'reviewingMovie', 'reviewingAnime'
     */
    abstract protected function reviewingItemProperty(): string;

    /**
     * Return the list of fields that can be enriched for this media type.
     */
    abstract protected function enrichableFields(): array;

    /**
     * Determine which fetched fields should be pre-selected for applying,
     * based on source priority and whether current values exist.
     */
    protected function getFieldsToApply(array $itemData, ?array $metadata): array
    {
        if (! $metadata) {
            return [];
        }

        $fields = [];
        $currentFirst = $this->sourcePriority[0] === 'current';

        foreach ($this->enrichableFields() as $field) {
            $hasCurrentValue = ! empty($itemData['current'][$field]);
            $hasNewValue = ! empty($metadata[$field]);

            if (! $hasNewValue) {
                continue;
            }

            if (! $hasCurrentValue) {
                $fields[] = $field;
            } elseif (! $currentFirst) {
                $fields[] = $field;
            }
        }

        return $fields;
    }

    /**
     * Open the review modal for a given item.
     */
    protected function openReviewFor(int $id): void
    {
        $listProperty = $this->enrichmentListProperty();
        $itemData = collect($this->{$listProperty})->firstWhere('id', $id);

        if (! $itemData) {
            return;
        }

        $this->{$this->reviewingIdProperty()} = $id;
        $this->{$this->reviewingItemProperty()} = $itemData;
        $this->reviewingMetadata = $this->fetchedData[$id] ?? null;
        $this->selectedFields = $this->getFieldsToApply($itemData, $this->reviewingMetadata);
        $this->showReviewModal = true;
    }

    /**
     * Close the review modal and reset state.
     */
    public function closeReviewModal(): void
    {
        $this->showReviewModal = false;
        $this->{$this->reviewingIdProperty()} = null;
        $this->{$this->reviewingItemProperty()} = null;
        $this->reviewingMetadata = null;
        $this->selectedFields = [];
    }

    /**
     * Build the update data array from selected fields and reviewing metadata.
     */
    protected function buildUpdateData(): array
    {
        $updateData = [];

        foreach ($this->selectedFields as $field) {
            if (isset($this->reviewingMetadata[$field]) && $this->reviewingMetadata[$field] !== null) {
                $updateData[$field] = $this->reviewingMetadata[$field];
            }
        }

        return $updateData;
    }

    /**
     * Update the local enrichment list after metadata has been applied,
     * removing filled fields from the missing list.
     */
    protected function updateLocalItemData(int $itemId, array $updateData): void
    {
        $listProperty = $this->enrichmentListProperty();

        foreach ($this->{$listProperty} as $index => $itemData) {
            if ($itemData['id'] === $itemId) {
                foreach ($updateData as $field => $value) {
                    $this->{$listProperty}[$index]['current'][$field] = $value;

                    $missingIndex = array_search($field, $this->{$listProperty}[$index]['missing']);
                    if ($missingIndex !== false) {
                        unset($this->{$listProperty}[$index]['missing'][$missingIndex]);
                        $this->{$listProperty}[$index]['missing'] = array_values($this->{$listProperty}[$index]['missing']);
                    }
                }

                $this->{$listProperty}[$index]['has_missing'] = ! empty($this->{$listProperty}[$index]['missing']);
                break;
            }
        }
    }

    /**
     * Get the count of items with missing metadata.
     */
    public function getItemsWithMissingCount(): int
    {
        $listProperty = $this->enrichmentListProperty();

        return collect($this->{$listProperty})->where('has_missing', true)->count();
    }

    /**
     * Get the count of items that have had metadata fetched.
     */
    public function getFetchedCount(): int
    {
        return count($this->fetchedData);
    }

    /**
     * Check if a background job is currently running.
     */
    public function isJobRunning(): bool
    {
        return $this->jobStatus && $this->jobStatus['status'] === 'running';
    }

    /**
     * Check if a background job has completed.
     */
    public function isJobCompleted(): bool
    {
        return $this->jobStatus && $this->jobStatus['status'] === 'completed';
    }
}
