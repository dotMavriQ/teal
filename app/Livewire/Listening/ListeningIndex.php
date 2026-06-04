<?php

declare(strict_types=1);

namespace App\Livewire\Listening;

use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

class ListeningIndex extends Component
{
    /**
     * @return list<array<string, mixed>>
     */
    public function getSubcategories(): array
    {
        return [
            [
                'name' => 'Live',
                'icon' => 'ticket',
                'description' => 'Concerts, gigs, and live events',
                'route' => 'concerts.index',
                'active' => true,
                'color' => 'orange',
            ],
            [
                'name' => 'Collection',
                'icon' => 'disc',
                'description' => 'Albums, records, and music you own or want',
                'route' => 'albums.index',
                'active' => true,
                'color' => 'purple',
            ],
        ];
    }

    #[Layout('layouts.app')]
    public function render(): View
    {
        return view('livewire.listening.listening-index', [
            'subcategories' => $this->getSubcategories(),
        ]);
    }
}
