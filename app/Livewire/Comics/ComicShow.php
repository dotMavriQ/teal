<?php

declare(strict_types=1);

namespace App\Livewire\Comics;

use App\Enums\ReadingStatus;
use App\Models\Comic;
use App\Models\ComicIssue;
use App\Services\ComicVineService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;

class ComicShow extends Component
{
    use AuthorizesRequests;

    public Comic $comic;

    public bool $fetchingIssues = false;

    public function mount(Comic $comic): void
    {
        $this->authorize('view', $comic);
        $this->comic = $comic;
    }

    public function updateStatus(string $status): void
    {
        $this->authorize('update', $this->comic);

        $updates = ['status' => $status];

        if ($status === 'reading' && ! $this->comic->date_started) {
            $updates['date_started'] = now();
        }

        if ($status === 'read' && ! $this->comic->date_finished) {
            $updates['date_finished'] = now();
        }

        $this->comic->update($updates);
        $this->comic->refresh();
    }

    public function updateRating(int $rating): void
    {
        $this->authorize('update', $this->comic);

        $this->comic->update(['rating' => $rating]);
        $this->comic->refresh();
    }

    public function fetchIssues(): void
    {
        $this->authorize('update', $this->comic);

        if (! $this->comic->comicvine_volume_id) {
            session()->flash('error', 'This comic has no Comic Vine volume ID.');
            return;
        }

        $this->fetchingIssues = true;

        $comicVine = app(ComicVineService::class);
        $issues = $comicVine->fetchVolumeIssues($this->comic->comicvine_volume_id);

        if (empty($issues)) {
            session()->flash('error', 'No issues found or could not fetch from Comic Vine.');
            $this->fetchingIssues = false;
            return;
        }

        $existingIds = $this->comic->issues()
            ->whereNotNull('comicvine_issue_id')
            ->pluck('comicvine_issue_id')
            ->all();

        $added = 0;
        foreach ($issues as $issue) {
            if (in_array($issue['issue_id'], $existingIds)) {
                continue;
            }

            ComicIssue::create([
                'comic_id' => $this->comic->id,
                'user_id' => $this->comic->user_id,
                'title' => $issue['title'],
                'issue_number' => $issue['issue_number'],
                'cover_date' => $issue['cover_date'],
                'cover_url' => $issue['cover_url'],
                'description' => $issue['description'],
                'comicvine_issue_id' => $issue['issue_id'],
                'comicvine_url' => $issue['comicvine_url'],
                'status' => 'want_to_read',
            ]);
            $added++;
        }

        $this->fetchingIssues = false;
        $this->comic->refresh();

        session()->flash('message', "Fetched {$added} new issue(s) from Comic Vine.");
    }

    public function updateIssueStatus(int $issueId, string $status): void
    {
        $this->authorize('update', $this->comic);

        $issue = ComicIssue::where('id', $issueId)
            ->where('comic_id', $this->comic->id)
            ->firstOrFail();

        $updates = ['status' => $status];

        if ($status === 'read' && ! $issue->date_read) {
            $updates['date_read'] = now();
        }

        $issue->update($updates);
    }

    public function updateIssueRating(int $issueId, int $rating): void
    {
        $this->authorize('update', $this->comic);

        $issue = ComicIssue::where('id', $issueId)
            ->where('comic_id', $this->comic->id)
            ->firstOrFail();

        $issue->update(['rating' => $issue->rating === $rating ? null : $rating]);
    }

    public function deleteComic(): void
    {
        $this->authorize('delete', $this->comic);

        $this->comic->delete();

        session()->flash('message', 'Comic deleted successfully.');

        $this->redirect(route('comics.index'));
    }

    public function render()
    {
        $issues = $this->comic->issues()
            ->orderByRaw("CAST(NULLIF(issue_number, '') AS UNSIGNED) ASC")
            ->orderBy('issue_number')
            ->get();

        return view('livewire.comics.comic-show', [
            'statuses' => ReadingStatus::cases(),
            'issues' => $issues,
            'issueStats' => [
                'total' => $issues->count(),
                'read' => $issues->where('status', ReadingStatus::Read)->count(),
                'reading' => $issues->where('status', ReadingStatus::Reading)->count(),
            ],
        ])->layout('layouts.app');
    }
}
