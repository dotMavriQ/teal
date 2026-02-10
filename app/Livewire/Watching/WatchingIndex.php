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
                'name' => 'Movies & TV Shows',
                'icon' => 'film',
                'description' => 'Movies, TV shows, documentaries',
                'route' => 'movies.index',
                'active' => true,
                'color' => 'purple',
            ],
            [
                'name' => 'Anime',
                'icon' => 'sparkles',
                'description' => 'Anime series, films, OVAs',
                'route' => 'anime.index',
                'active' => true,
                'color' => 'pink',
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
