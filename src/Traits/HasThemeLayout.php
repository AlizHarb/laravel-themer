<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Traits;

use AlizHarb\Themer\Themer;

/**
 * Trait to allow Livewire components to automatically use the active theme's layout.
 */
trait HasThemeLayout
{
    /**
     * Resolve the layout for the component.
     */
    public function layout(): string
    {
        /** @var string $default */
        $default = property_exists($this, 'layout') ? $this->layout : 'layouts.app';

        return Themer::resolve($default);
    }
}
