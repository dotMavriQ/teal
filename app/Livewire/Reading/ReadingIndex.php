<?php

declare(strict_types=1);

namespace App\Livewire\Reading;

use Livewire\Attributes\Layout;
use Livewire\Component;

class ReadingIndex extends Component
{
    #[Layout('layouts.app')]
    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.reading.reading-index', [
            'subcategories' => [
                [
                    'name' => 'Books',
                    'icon' => 'book-open',
                    'description' => 'Novels, non-fiction, textbooks',
                    'route' => 'books.index',
                    'active' => true,
                    'color' => 'blue',
                ],
                [
                    'name' => 'Comics',
                    'icon' => 'squares-2x2',
                    'description' => 'Comics, manga, graphic novels',
                    'route' => 'comics.index',
                    'active' => true,
                    'color' => 'purple',
                ],
            ],
        ]);
    }
}
