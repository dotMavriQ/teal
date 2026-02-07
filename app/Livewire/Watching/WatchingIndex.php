<?php

declare(strict_types=1);

namespace App\Livewire\Watching;

use Livewire\Component;

class WatchingIndex extends Component
{
    public function getSubcategories(): array
    {
        return [
            [
                'name' => 'Movies',
                'icon' => 'film',
                'description' => 'Movies, documentaries, short films',
                'route' => 'movies.index',
                'active' => true,
                'color' => 'purple',
            ],
            [
                'name' => 'TV Shows',
                'icon' => 'tv',
                'description' => 'Series, anime, miniseries',
                'route' => null,
                'active' => false,
                'color' => 'blue',
            ],
        ];
    }

    public function render()
    {
        return view('livewire.watching.watching-index', [
            'subcategories' => $this->getSubcategories(),
        ])->layout('layouts.app');
    }
}
