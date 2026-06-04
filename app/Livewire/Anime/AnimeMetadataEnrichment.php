<?php

declare(strict_types=1);

namespace App\Livewire\Anime;

use App\Models\Anime;
use App\Services\JikanService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;

class AnimeMetadataEnrichment extends Component
{
    use \App\Livewire\Concerns\WithMetadataEnrichment;
    use \App\Livewire\Concerns\WithSourcePriority;

    /** @var list<string> */
    public array $sourcePriority = ['current', 'jikan'];

    /** @var array<int, array<string, mixed>> */
    public array $animeNeedingEnrichment = [];

    public bool $hasScanned = false;

    public bool $isScanning = false;

    public bool $showReviewModal = false;

    public ?int $reviewingAnimeId = null;

    /** @var array<string, mixed>|null */
    public ?array $reviewingAnime = null;

    /** @var array<string, mixed>|null */
    public ?array $reviewingMetadata = null;

    /** @var list<string> */
    public array $selectedFields = [];

    /** @var array<int, array<string, mixed>> */
    public array $fetchedData = [];

    public int $batchLimit = 50;

    /** @var list<string> */
    protected const ENRICHABLE_FIELDS = ['description', 'poster_url', 'runtime_minutes', 'genres', 'studios', 'episodes_total', 'media_type', 'original_title'];

    // Anime has no background job support, so override these to avoid errors
    /** @var array<array-key, mixed>|null */
    public ?array $jobStatus = null;

    /**
     * @return array<int, array<string, mixed>>
     */
    protected function enrichmentList(): array
    {
        return $this->animeNeedingEnrichment;
    }

    /**
     * @param  array<int, array<string, mixed>>  $list
     */
    protected function setEnrichmentList(array $list): void
    {
        $this->animeNeedingEnrichment = $list;
    }

    protected function setReviewingId(?int $id): void
    {
        $this->reviewingAnimeId = $id;
    }

    /**
     * @param  array<string, mixed>|null  $item
     */
    protected function setReviewingItem(?array $item): void
    {
        $this->reviewingAnime = $item;
    }

    /**
     * @return list<string>
     */
    protected function enrichableFields(): array
    {
        return self::ENRICHABLE_FIELDS;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function intFrom(array $data, string $key): int
    {
        $value = $data[$key] ?? null;

        return is_numeric($value) ? (int) $value : 0;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function strFrom(array $data, string $key): string
    {
        $value = $data[$key] ?? null;

        return is_string($value) ? $value : '';
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
            ->all();

        $this->hasScanned = true;
        $this->isScanning = false;
    }

    /**
     * @return list<string>
     */
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
            ->filter(fn ($a) => (bool) $a['has_missing'])
            ->take($this->batchLimit);

        $applied = 0;
        $fetched = 0;

        foreach ($toFetch as $animeData) {
            $metadata = null;
            $malId = $this->intFrom($animeData, 'mal_id');
            $title = $this->strFrom($animeData, 'title');

            if ($malId !== 0) {
                $metadata = $service->findByMalId($malId);
            }

            if (! $metadata && $title !== '') {
                $metadata = $service->searchByTitle($title);
            }

            $fetched++;

            if ($metadata) {
                $anime = Anime::where('user_id', Auth::id())->find($this->intFrom($animeData, 'id'));
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

        if (! is_array($animeData)) {
            return;
        }

        $service = app(JikanService::class);
        $metadata = null;
        $malId = $this->intFrom($animeData, 'mal_id');
        $title = $this->strFrom($animeData, 'title');

        if ($malId !== 0) {
            $metadata = $service->findByMalId($malId);
        }

        if (! $metadata && $title !== '') {
            $metadata = $service->searchByTitle($title);
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

    #[Layout('layouts.app')]
    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.anime.anime-metadata-enrichment');
    }
}
