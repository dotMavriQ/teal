<?php

declare(strict_types=1);

namespace App\Livewire\Anime;

use App\Models\Anime;
use App\Services\JikanService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class AnimeMetadataEnrichment extends Component
{
    public array $sourcePriority = ['current', 'jikan'];

    public array $animeNeedingEnrichment = [];

    public bool $hasScanned = false;

    public bool $isScanning = false;

    public bool $showReviewModal = false;

    public ?int $reviewingAnimeId = null;

    public ?array $reviewingAnime = null;

    public ?array $reviewingMetadata = null;

    public array $selectedFields = [];

    public array $fetchedData = [];

    public int $batchLimit = 50;

    protected const ENRICHABLE_FIELDS = ['description', 'poster_url', 'runtime_minutes', 'genres', 'studios', 'episodes_total', 'media_type', 'original_title'];

    public function moveSourceUp(string $source): void
    {
        $index = array_search($source, $this->sourcePriority);
        if ($index > 0) {
            $temp = $this->sourcePriority[$index - 1];
            $this->sourcePriority[$index - 1] = $source;
            $this->sourcePriority[$index] = $temp;
        }
    }

    public function moveSourceDown(string $source): void
    {
        $index = array_search($source, $this->sourcePriority);
        if ($index < count($this->sourcePriority) - 1) {
            $temp = $this->sourcePriority[$index + 1];
            $this->sourcePriority[$index + 1] = $source;
            $this->sourcePriority[$index] = $temp;
        }
    }

    public function scanLibrary(): void
    {
        $this->isScanning = true;
        $this->hasScanned = false;
        $this->fetchedData = [];

        $randomFunction = DB::getDriverName() === 'sqlite' ? 'RANDOM()' : 'RAND()';
        $this->animeNeedingEnrichment = Anime::query()
            ->where('user_id', Auth::id())
            ->orderByRaw("metadata_fetched_at IS NULL DESC, {$randomFunction}")
            ->get(['id', 'title', 'original_title', 'mal_id', 'year', 'description', 'poster_url', 'runtime_minutes', 'genres', 'studios', 'episodes_total', 'media_type', 'metadata_fetched_at'])
            ->map(function ($anime) {
                $missing = $this->getMissingFields($anime);

                return [
                    'id' => $anime->id,
                    'title' => $anime->title,
                    'mal_id' => $anime->mal_id,
                    'year' => $anime->year,
                    'metadata_fetched_at' => $anime->metadata_fetched_at?->toIso8601String(),
                    'current' => [
                        'description' => $anime->description,
                        'poster_url' => $anime->poster_url,
                        'runtime_minutes' => $anime->runtime_minutes,
                        'genres' => $anime->genres,
                        'studios' => $anime->studios,
                        'episodes_total' => $anime->episodes_total,
                        'media_type' => $anime->media_type,
                        'original_title' => $anime->original_title,
                    ],
                    'missing' => $missing,
                    'has_missing' => ! empty($missing),
                ];
            })
            ->toArray();

        $this->hasScanned = true;
        $this->isScanning = false;
    }

    protected function getMissingFields(Anime $anime): array
    {
        $missing = [];

        if (empty($anime->description)) {
            $missing[] = 'description';
        }
        if (empty($anime->poster_url)) {
            $missing[] = 'poster_url';
        }
        if (empty($anime->runtime_minutes)) {
            $missing[] = 'runtime_minutes';
        }
        if (empty($anime->genres)) {
            $missing[] = 'genres';
        }
        if (empty($anime->studios)) {
            $missing[] = 'studios';
        }

        return $missing;
    }

    public function startBatchFetch(): void
    {
        $service = app(JikanService::class);

        $toFetch = collect($this->animeNeedingEnrichment)
            ->filter(fn ($a) => $a['has_missing'])
            ->take($this->batchLimit);

        $applied = 0;
        $fetched = 0;

        foreach ($toFetch as $animeData) {
            $metadata = null;

            if (! empty($animeData['mal_id'])) {
                $metadata = $service->findByMalId($animeData['mal_id']);
            }

            if (! $metadata && ! empty($animeData['title'])) {
                $metadata = $service->searchByTitle($animeData['title']);
            }

            $fetched++;

            if ($metadata) {
                $anime = Anime::where('user_id', Auth::id())->find($animeData['id']);
                if ($anime) {
                    $updates = [];
                    foreach (self::ENRICHABLE_FIELDS as $field) {
                        if (! empty($metadata[$field]) && empty($anime->$field)) {
                            $updates[$field] = $metadata[$field];
                        }
                    }
                    if (! empty($metadata['year']) && empty($anime->year)) {
                        $updates['year'] = $metadata['year'];
                    }
                    if (! empty($metadata['mal_score']) && empty($anime->mal_score)) {
                        $updates['mal_score'] = $metadata['mal_score'];
                    }
                    if (! empty($metadata['mal_url']) && empty($anime->mal_url)) {
                        $updates['mal_url'] = $metadata['mal_url'];
                    }

                    $updates['metadata_fetched_at'] = now();
                    $anime->update($updates);
                    $applied++;
                }
            }
        }

        session()->flash('message', "Metadata fetch completed! Fetched {$fetched}, updated {$applied} anime.");

        $this->scanLibrary();
    }

    public function fetchSingleAnime(int $id): void
    {
        $animeData = collect($this->animeNeedingEnrichment)->firstWhere('id', $id);

        if (! $animeData) {
            return;
        }

        $service = app(JikanService::class);
        $metadata = null;

        if (! empty($animeData['mal_id'])) {
            $metadata = $service->findByMalId($animeData['mal_id']);
        }

        if (! $metadata && ! empty($animeData['title'])) {
            $metadata = $service->searchByTitle($animeData['title']);
        }

        if ($metadata) {
            $this->fetchedData[$id] = $metadata;
            $this->reviewingMetadata = $metadata;
            $this->selectedFields = $this->getFieldsToApply($animeData, $metadata);
        }
    }

    public function startReview(int $id): void
    {
        $animeData = collect($this->animeNeedingEnrichment)->firstWhere('id', $id);

        if (! $animeData) {
            return;
        }

        $this->reviewingAnimeId = $id;
        $this->reviewingAnime = $animeData;
        $this->reviewingMetadata = $this->fetchedData[$id] ?? null;
        $this->selectedFields = $this->getFieldsToApply($animeData, $this->reviewingMetadata);
        $this->showReviewModal = true;
    }

    protected function getFieldsToApply(array $animeData, ?array $metadata): array
    {
        if (! $metadata) {
            return [];
        }

        $fields = [];
        $currentFirst = $this->sourcePriority[0] === 'current';

        foreach (self::ENRICHABLE_FIELDS as $field) {
            $hasCurrentValue = ! empty($animeData['current'][$field]);
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

    public function applyMetadata(): void
    {
        if (! $this->reviewingAnimeId || ! $this->reviewingMetadata || empty($this->selectedFields)) {
            $this->closeReviewModal();

            return;
        }

        $anime = Anime::query()
            ->where('user_id', Auth::id())
            ->find($this->reviewingAnimeId);

        if (! $anime) {
            $this->closeReviewModal();

            return;
        }

        $updateData = [];

        foreach ($this->selectedFields as $field) {
            if (isset($this->reviewingMetadata[$field]) && $this->reviewingMetadata[$field] !== null) {
                $updateData[$field] = $this->reviewingMetadata[$field];
            }
        }

        if (! empty($updateData)) {
            $anime->update($updateData);
        }

        $this->updateLocalAnimeData($this->reviewingAnimeId, $updateData);
        $this->closeReviewModal();

        session()->flash('message', 'Metadata applied successfully.');
    }

    public function skipAnime(): void
    {
        $this->closeReviewModal();
    }

    public function closeReviewModal(): void
    {
        $this->showReviewModal = false;
        $this->reviewingAnimeId = null;
        $this->reviewingAnime = null;
        $this->reviewingMetadata = null;
        $this->selectedFields = [];
    }

    protected function updateLocalAnimeData(int $animeId, array $updateData): void
    {
        foreach ($this->animeNeedingEnrichment as $index => $animeData) {
            if ($animeData['id'] === $animeId) {
                foreach ($updateData as $field => $value) {
                    $this->animeNeedingEnrichment[$index]['current'][$field] = $value;

                    $missingIndex = array_search($field, $this->animeNeedingEnrichment[$index]['missing']);
                    if ($missingIndex !== false) {
                        unset($this->animeNeedingEnrichment[$index]['missing'][$missingIndex]);
                        $this->animeNeedingEnrichment[$index]['missing'] = array_values($this->animeNeedingEnrichment[$index]['missing']);
                    }
                }

                $this->animeNeedingEnrichment[$index]['has_missing'] = ! empty($this->animeNeedingEnrichment[$index]['missing']);
                break;
            }
        }
    }

    public function getSourceLabel(string $source): string
    {
        return match ($source) {
            'current' => 'Keep Current Values',
            'jikan' => 'Jikan (MyAnimeList)',
            default => $source,
        };
    }

    public function getAnimeWithMissingCount(): int
    {
        return collect($this->animeNeedingEnrichment)->where('has_missing', true)->count();
    }

    public function getFetchedCount(): int
    {
        return count($this->fetchedData);
    }

    public function render()
    {
        return view('livewire.anime.anime-metadata-enrichment')
            ->layout('layouts.app');
    }
}
