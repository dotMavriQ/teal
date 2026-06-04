<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

trait WithMetadataEnrichment
{
    /**
     * Return the items needing enrichment for this media type.
     *
     * @return array<int, array<string, mixed>>
     */
    abstract protected function enrichmentList(): array;

    /**
     * Replace the items needing enrichment for this media type.
     *
     * @param  array<int, array<string, mixed>>  $list
     */
    abstract protected function setEnrichmentList(array $list): void;

    /**
     * Set the id of the item currently being reviewed.
     */
    abstract protected function setReviewingId(?int $id): void;

    /**
     * Set the data of the item currently being reviewed.
     *
     * @param  array<string, mixed>|null  $item
     */
    abstract protected function setReviewingItem(?array $item): void;

    /**
     * Return the list of fields that can be enriched for this media type.
     *
     * @return list<string>
     */
    abstract protected function enrichableFields(): array;

    /**
     * Determine which fetched fields should be pre-selected for applying,
     * based on source priority and whether current values exist.
     *
     * @param  array<string, mixed>  $itemData
     * @param  array<string, mixed>|null  $metadata
     * @return list<string>
     */
    protected function getFieldsToApply(array $itemData, ?array $metadata): array
    {
        if ($metadata === null) {
            return [];
        }

        $fields = [];
        $currentFirst = ($this->sourcePriority[0] ?? null) === 'current';
        $current = is_array($itemData['current'] ?? null) ? $itemData['current'] : [];

        foreach ($this->enrichableFields() as $field) {
            $hasCurrentValue = ! empty($current[$field]);
            $hasNewValue = ! empty($metadata[$field]);

            if (! $hasNewValue) {
                continue;
            }

            if (! $hasCurrentValue || ! $currentFirst) {
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
        $itemData = collect($this->enrichmentList())->firstWhere('id', $id);

        if (! is_array($itemData)) {
            return;
        }

        $metadata = $this->fetchedData[$id] ?? null;
        $metadata = is_array($metadata) ? $metadata : null;

        $this->setReviewingId($id);
        $this->setReviewingItem($itemData);
        $this->reviewingMetadata = $metadata;
        $this->selectedFields = $this->getFieldsToApply($itemData, $metadata);
        $this->showReviewModal = true;
    }

    /**
     * Close the review modal and reset state.
     */
    public function closeReviewModal(): void
    {
        $this->showReviewModal = false;
        $this->setReviewingId(null);
        $this->setReviewingItem(null);
        $this->reviewingMetadata = null;
        $this->selectedFields = [];
    }

    /**
     * Build the update data array from selected fields and reviewing metadata.
     *
     * @return array<string, mixed>
     */
    protected function buildUpdateData(): array
    {
        $updateData = [];
        $metadata = $this->reviewingMetadata ?? [];

        foreach ($this->selectedFields as $field) {
            if (isset($metadata[$field])) {
                $updateData[$field] = $metadata[$field];
            }
        }

        return $updateData;
    }

    /**
     * Update the local enrichment list after metadata has been applied,
     * removing filled fields from the missing list.
     *
     * @param  array<string, mixed>  $updateData
     */
    protected function updateLocalItemData(int $itemId, array $updateData): void
    {
        $list = $this->enrichmentList();

        foreach ($list as $index => $itemData) {
            if (($itemData['id'] ?? null) !== $itemId) {
                continue;
            }

            $current = is_array($itemData['current'] ?? null) ? $itemData['current'] : [];
            $missing = is_array($itemData['missing'] ?? null) ? array_values($itemData['missing']) : [];

            foreach ($updateData as $field => $value) {
                $current[$field] = $value;

                $missingIndex = array_search($field, $missing, true);
                if ($missingIndex !== false) {
                    unset($missing[$missingIndex]);
                    $missing = array_values($missing);
                }
            }

            $itemData['current'] = $current;
            $itemData['missing'] = $missing;
            $itemData['has_missing'] = $missing !== [];
            $list[$index] = $itemData;
            break;
        }

        $this->setEnrichmentList($list);
    }

    /**
     * Get the count of items with missing metadata.
     */
    public function getItemsWithMissingCount(): int
    {
        return collect($this->enrichmentList())->where('has_missing', true)->count();
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
        return $this->jobStatus !== null && ($this->jobStatus['status'] ?? null) === 'running';
    }

    /**
     * Check if a background job has completed.
     */
    public function isJobCompleted(): bool
    {
        return $this->jobStatus !== null && ($this->jobStatus['status'] ?? null) === 'completed';
    }
}
