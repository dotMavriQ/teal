<?php

declare(strict_types=1);

namespace App\Livewire\Playing;

use Livewire\Component;

class PlayingIndex extends Component
{
    public function getSubcategories(): array
    {
        return [
            [
                'name' => 'Games',
                'icon' => 'puzzle-piece',
                'description' => 'Video games across all platforms',
                'route' => 'games.index',
                'active' => true,
                'color' => 'green',
            ],
        ];
    }

    public function render()
    {
        return view('livewire.playing.playing-index', [
            'subcategories' => $this->getSubcategories(),
        ])->layout('layouts.app');
    }
}
