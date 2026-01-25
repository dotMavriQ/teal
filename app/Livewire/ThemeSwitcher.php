<?php

declare(strict_types=1);

namespace App\Livewire;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ThemeSwitcher extends Component
{
    public string $theme;

    public function mount(): void
    {
        $this->theme = Auth::user()?->theme ?? config('themes.default', 'normie');
    }

    public function setTheme(string $theme): void
    {
        $availableThemes = collect(config('themes.available'))->pluck('value')->toArray();

        if (! in_array($theme, $availableThemes)) {
            return;
        }

        $this->theme = $theme;

        if (Auth::check()) {
            Auth::user()->update(['theme' => $theme]);
        }

        $this->dispatch('theme-changed', theme: $theme);
    }

    public function render()
    {
        return view('livewire.theme-switcher', [
            'themes' => config('themes.available'),
        ]);
    }
}
