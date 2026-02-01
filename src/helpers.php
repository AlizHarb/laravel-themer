<?php

declare(strict_types=1);

use AlizHarb\Themer\Theme;
use AlizHarb\Themer\ThemeAsset;
use AlizHarb\Themer\ThemeManager;

if (! function_exists('get_active_theme')) {
    /**
     * Get the currently active theme instance.
     */
    function get_active_theme(): ?Theme
    {
        /** @var ThemeManager $manager */
        $manager = app('themer');

        return $manager->getActiveTheme();
    }
}

if (! function_exists('theme_asset')) {
    /**
     * Get the public URL for a theme asset.
     */
    function theme_asset(string $path): string
    {
        return ThemeAsset::url($path);
    }
}

if (! function_exists('is_theme_active')) {
    /**
     * Check if a specific theme is currently active.
     */
    function is_theme_active(string $themeName): bool
    {
        /** @var ThemeManager $manager */
        $manager = app('themer');

        return (bool) $manager->isActive($themeName);
    }
}
