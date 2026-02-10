<?php

declare(strict_types=1);

namespace App\Livewire\Anime;

use App\Models\Anime;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class AnimeSettings extends Component
{
    public bool $showDeleteAllModal = false;

    public string $confirmationWord = '';

    public string $confirmationInput = '';

    public function openDeleteAllModal(): void
    {
        $this->confirmationWord = $this->generateConfirmationWord();
        $this->confirmationInput = '';
        $this->showDeleteAllModal = true;
    }

    public function closeDeleteAllModal(): void
    {
        $this->showDeleteAllModal = false;
        $this->confirmationInput = '';
    }

    public function deleteAllAnime(): void
    {
        if ($this->confirmationInput !== $this->confirmationWord) {
            $this->addError('confirmationInput', 'Confirmation word does not match.');

            return;
        }

        $count = Anime::query()
            ->where('user_id', Auth::id())
            ->delete();

        $this->showDeleteAllModal = false;
        $this->confirmationInput = '';

        session()->flash('message', "All {$count} anime have been permanently deleted.");
    }

    protected function generateConfirmationWord(): string
    {
        $words = [
            'obliterate', 'permanent', 'irreversible', 'destruction', 'annihilate',
            'eradicate', 'demolition', 'exterminate', 'catastrophe', 'apocalypse',
            'decimation', 'liquidate', 'elimination', 'termination', 'expunction',
        ];

        $word = $words[array_rand($words)];
        $chars = str_split($word);
        $length = count($chars);

        $pos1 = random_int(0, $length - 1);
        do {
            $pos2 = random_int(0, $length - 1);
        } while ($pos2 === $pos1);

        $chars[$pos1] = strtoupper($chars[$pos1]);
        $chars[$pos2] = strtoupper($chars[$pos2]);

        return implode('', $chars);
    }

    public function render()
    {
        return view('livewire.anime.anime-settings')
            ->layout('layouts.app');
    }
}
