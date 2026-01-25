<?php

declare(strict_types=1);

namespace AlizHarb\Themer;

use Illuminate\Foundation\Vite;

/**
 * Utility class for resolving theme assets and Vite integration.
 */
final class ThemeAsset
{
    /**
     * Get the public URL for a theme asset.
     */
    public static function url(string $path): string
    {
        /** @var ThemeManager $manager */
        $manager = app('themer');
        $theme = $manager->getActiveTheme();

        if (!$theme) {
            return asset($path);
        }

        $baseUrl = $theme->assetPath ?: ('themes/'.$theme->name);

        return asset($baseUrl.'/'.ltrim($path, '/'));
    }

    /**
     * Get the Vite tag for theme-specific entrypoints.
     *
     * @param  string|string[]  $entrypoints
     */
    public static function vite(string|array $entrypoints, ?string $buildDirectory = null): string
    {
        /** @var ThemeManager $manager */
        $manager = app('themer');
        $theme = $manager->getActiveTheme();

        if (!$theme) {
            return (string) app(Vite::class)($entrypoints, $buildDirectory);
        }

        $entrypoints = (array) $entrypoints;
        $transformed = [];

        /** @var string $themesPath */
        $themesPath = config('themer.themes_path', base_path('themes'));
        $themesBase = str_replace(base_path().DIRECTORY_SEPARATOR, '', $themesPath);

        foreach ($entrypoints as $entrypoint) {
            $ep = ltrim($entrypoint, '/');
            $themeFile = $theme->path.DIRECTORY_SEPARATOR.$ep;

            $transformed[] = file_exists($themeFile) ? $themesBase.'/'.$theme->name.'/'.$ep : $entrypoint;
        }

        if ($buildDirectory === null) {
            $hotPath = public_path(sprintf('themes/%s/hot', $theme->name));
            $buildDirectory = file_exists($hotPath)
                ? 'themes/'.$theme->name
                : sprintf('themes/%s/build', $theme->name);
        }

        return (string) app(Vite::class)($transformed, $buildDirectory);
    }
}
