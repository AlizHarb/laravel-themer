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
     * @param string $path
     */
    protected function viewPath($path = ''): string
    {
        /** @var string|null $themeName */
        $themeName = $this->getTheme();

        if ($themeName === null || $themeName === '') {
            return (string) parent::viewPath($path);
        }

        /** @var \AlizHarb\Themer\ThemeManager $manager */
        $manager = app(\AlizHarb\Themer\ThemeManager::class);
        $theme = $manager->all()->get($themeName);

        if (! $theme) {
            return (string) parent::viewPath($path);
        }

        return $theme->path.'/resources/views/'.str_replace('.', '/', $path).'.blade.php';
    }

    /**
     * Get the destination class path.
     *
     * @param string $name
     */
    protected function getPath($name): string
    {
        /** @var string|null $themeName */
        $themeName = $this->getTheme();

        if ($themeName === null || $themeName === '') {
            return (string) parent::getPath($name);
        }

        /** @var \AlizHarb\Themer\ThemeManager $manager */
        $manager = app(\AlizHarb\Themer\ThemeManager::class);
        $theme = $manager->all()->get($themeName);

        if (! $theme) {
            return (string) parent::getPath($name);
        }

        $namespace = (string) $this->laravel->getNamespace();
        $name = str_replace([$namespace, '\\'], ['', '/'], $name);

        return $theme->path.'/app/'.$name.'.php';
    }

    /**
     * Get the console command options.
     *
     * @return array<int, array{0: string, 1: string|null, 2: int, 3: string, 4: mixed|null}>
     */
    protected function getOptions(): array
    {
        /** @var array<int, array{0: string, 1: string|null, 2: int, 3: string, 4: mixed|null}> $options */
        $options = array_merge(parent::getOptions(), $this->getThemeOptions());

        return $options;
    }
}
