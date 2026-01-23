<?php

declare(strict_types=1);

namespace App\Livewire\Reading;

use Livewire\Component;

class ReadingIndex extends Component
{
    public function getSubcategories(): array
    {
        return [
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
                'route' => null,
                'active' => false,
                'color' => 'purple',
            ],
        ];
    }

    public function render()
    {
        return view('livewire.reading.reading-index', [
            'subcategories' => $this->getSubcategories(),
        ])->layout('layouts.app');
    }
}
