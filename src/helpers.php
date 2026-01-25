<?php

declare(strict_types=1);

use AlizHarb\Themer\Theme;
use AlizHarb\Themer\ThemeAsset;
use AlizHarb\Themer\ThemeManager;

if (!function_exists('get_active_theme')) {
    /**
     * Get the currently active theme instance.
     */
    function get_active_theme(): ?Theme
    {
        /** @var ThemeManager|null $manager */
        $manager = app()->bound('themer') ? app('themer') : null;

        return $manager?->getActiveTheme();
    }
}

if (!function_exists('theme_asset')) {
    /**
     * Generate an asset path for the active theme.
     */
    function theme_asset(string $path): string
    {
        return ThemeAsset::url($path);
    }
}
