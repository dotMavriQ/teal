<?php

declare(strict_types=1);

namespace App\Livewire\Playing;

use Livewire\Attributes\Layout;
use Livewire\Component;

class PlayingIndex extends Component
{
    public function getSubcategories(): array
    {
        return [
            [
                'name' => 'Games',
                'icon' => 'game-controller',
                'description' => 'Video games across all platforms',
                'route' => 'games.index',
                'active' => true,
                'color' => 'green',
            ],
            [
                'name' => 'Board Games',
                'icon' => 'dice',
                'description' => 'Board games and tabletop games',
                'route' => 'board-games.index',
                'active' => true,
                'color' => 'amber',
            ],
        ];
    }

    #[Layout('layouts.app')]
    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.playing.playing-index', [
            'subcategories' => $this->getSubcategories(),
        ]);
    }
}
