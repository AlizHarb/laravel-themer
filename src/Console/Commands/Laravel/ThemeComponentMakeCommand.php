<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Console\Commands\Laravel;

use AlizHarb\Themer\Traits\HasThemeOption;
use Illuminate\Foundation\Console\ComponentMakeCommand;

/**
 * Custom ComponentMakeCommand that supports themes.
 */
final class ThemeComponentMakeCommand extends ComponentMakeCommand
{
    use HasThemeOption;

    /**
     * Get the destination view path.
     *
     * @param  string  $path
     */
    protected function viewPath($path = ''): string
    {
        $themeName = $this->getTheme();

        if ($themeName === null || $themeName === '') {
            return parent::viewPath($path);
        }

        /** @var \AlizHarb\Themer\ThemeManager $manager */
        $manager = app(\AlizHarb\Themer\ThemeManager::class);
        $theme = $manager->all()->get($themeName);

        if (!$theme) {
            return parent::viewPath($path);
        }

        return $theme->path.'/resources/views/'.str_replace('.', '/', $path).'.blade.php';
    }

    /**
     * Get the destination class path.
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

        $name = str_replace([$this->laravel->getNamespace(), '\\'], ['', '/'], $name);

        return $theme->path.'/app/'.$name.'.php';
    }
}
