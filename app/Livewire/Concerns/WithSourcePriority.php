<?php

declare(strict_types=1);

namespace App\Livewire\Concerns;

trait WithSourcePriority
{
    public function moveSourceUp(string $source): void
    {
        $index = array_search($source, $this->sourcePriority, true);
        if ($index === false || $index === 0) {
            return;
        }

        $reordered = $this->sourcePriority;
        [$reordered[$index - 1], $reordered[$index]] = [$reordered[$index], $reordered[$index - 1]];
        $this->sourcePriority = array_values($reordered);
    }

    public function moveSourceDown(string $source): void
    {
        $index = array_search($source, $this->sourcePriority, true);
        if ($index === false || $index >= count($this->sourcePriority) - 1) {
            return;
        }

        $reordered = $this->sourcePriority;
        [$reordered[$index + 1], $reordered[$index]] = [$reordered[$index], $reordered[$index + 1]];
        $this->sourcePriority = array_values($reordered);
    }
}
