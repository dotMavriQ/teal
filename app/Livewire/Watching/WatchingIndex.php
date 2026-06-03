<?php

declare(strict_types=1);

namespace App\Livewire\Watching;

use Livewire\Attributes\Layout;
use Livewire\Component;

class WatchingIndex extends Component
{
    public function getSubcategories(): array
    {
        return [
            [
                'name' => 'Movies & TV-Shows',
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

    #[Layout('layouts.app')]
    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.watching.watching-index', [
            'subcategories' => $this->getSubcategories(),
        ]);
    }
}
