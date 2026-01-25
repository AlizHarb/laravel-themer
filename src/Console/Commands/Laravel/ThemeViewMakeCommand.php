<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Console\Commands\Laravel;

use AlizHarb\Themer\Traits\HasThemeOption;
use Illuminate\Foundation\Console\ViewMakeCommand;

/**
 * Custom ViewMakeCommand that supports themes.
 */
final class ThemeViewMakeCommand extends ViewMakeCommand
{
    use HasThemeOption;

    /**
     * Get the destination view path.
     *
     * @param  string  $name
     */
    protected function getPath($name): string
    {
        $themeName = $this->getTheme();

        if ($themeName === null || $themeName === '') {
            return parent::getPath($name);
        }

        /** @var \AlizHarb\Themer\ThemeManager $manager */
        $manager = app(\AlizHarb\Themer\ThemeManager::class);
        $theme = $manager->all()->get($themeName);

        if (!$theme) {
            return parent::getPath($name);
        }

        $viewPath = $theme->path.'/resources/views/';

        $normalized = str((string) $name)->replace(['.', '\\'], '/');

        if ($normalized->startsWith('App/')) {
            $normalized = $normalized->after('App/');
        }

        return $viewPath.$normalized->toString().'.blade.php';
    }
}
