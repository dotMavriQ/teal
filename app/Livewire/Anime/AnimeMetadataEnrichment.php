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
    use \App\Livewire\Concerns\WithMetadataEnrichment;
    use \App\Livewire\Concerns\WithSourcePriority;

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

    // Anime has no background job support, so override these to avoid errors
    public ?array $jobStatus = null;

    protected function enrichmentListProperty(): string
    {
        return 'animeNeedingEnrichment';
    }

    protected function reviewingIdProperty(): string
    {
        return 'reviewingAnimeId';
    }

    protected function reviewingItemProperty(): string
    {
        return 'reviewingAnime';
    }

    protected function enrichableFields(): array
    {
        return self::ENRICHABLE_FIELDS;
    }

    public function scanLibrary(): void
    {
        $this->isScanning = true;
        $this->hasScanned = false;
        $this->fetchedData = [];

        $randomFunction = in_array(DB::getDriverName(), ['sqlite', 'pgsql']) ? 'RANDOM()' : 'RAND()';
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
        $this->openReviewFor($id);
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

        $updateData = $this->buildUpdateData();

        if (! empty($updateData)) {
            $anime->update($updateData);
        }

        $this->updateLocalItemData($this->reviewingAnimeId, $updateData);
        $this->closeReviewModal();

        session()->flash('message', 'Metadata applied successfully.');
    }

    public function skipAnime(): void
    {
        $this->closeReviewModal();
    }

    public function getSourceLabel(string $source): string
    {
        return match ($source) {
            'current' => 'Keep Current Values',
            'jikan' => 'Jikan (MyAnimeList)',
            default => $source,
        };
    }

    public function render()
    {
        return view('livewire.anime.anime-metadata-enrichment')
            ->layout('layouts.app');
    }
}
