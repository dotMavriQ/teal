<?php

declare(strict_types=1);

namespace App\Livewire;

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class ThemeSwitcher extends Component
{
    public string $theme;

    public function mount(): void
    {
        $user = Auth::user();
        $theme = $user instanceof User ? $user->theme : null;

        if (is_string($theme) && $theme !== '') {
            $this->theme = $theme;

            return;
        }

        $default = config('themes.default', 'normie');
        $this->theme = is_string($default) ? $default : 'normie';
    }

    public function setTheme(string $theme): void
    {
        $available = config('themes.available');
        $availableThemes = collect(is_array($available) ? $available : [])->pluck('value')->toArray();

        if (! in_array($theme, $availableThemes)) {
            return;
        }

        $this->theme = $theme;

        $user = Auth::user();
        if ($user instanceof User) {
            $user->update(['theme' => $theme]);
        }

        $this->dispatch('theme-changed', theme: $theme);
    }

    public function render(): \Illuminate\Contracts\View\View
    {
        return view('livewire.theme-switcher', [
            'themes' => config('themes.available'),
        ]);
    }
}
