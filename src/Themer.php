<?php

declare(strict_types=1);

namespace AlizHarb\Themer;

/**
 * Utility class to resolve theme-specific resources.
 */
final class Themer
{
    /**
     * Get the view path for a resource based on the active theme.
     */
    public static function resolve(string $view): string
    {
        /** @var ThemeManager $manager */
        $manager = app('themer');
        $theme = $manager->getActiveTheme();

        if ($theme instanceof Theme) {
            $themeView = 'theme::'.$view;

            if (view()->exists($themeView)) {
                return $themeView;
            }
        }

        return $view;
    }
}
