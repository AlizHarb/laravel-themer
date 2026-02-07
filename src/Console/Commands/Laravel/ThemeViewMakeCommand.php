<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Console\Commands\Laravel;

use AlizHarb\Themer\ThemeManager;
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
     * @param string $name
     */
    protected function getPath($name): string
    {
        /** @var string|null $themeName */
        $themeName = $this->getTheme();

        if ($themeName === null || $themeName === '') {
            return (string) parent::getPath($name);
        }

        /** @var ThemeManager $manager */
        $manager = app(ThemeManager::class);
        $theme = $manager->all()->get($themeName);

        if (! $theme) {
            return (string) parent::getPath($name);
        }

        $viewPath = $theme->path.'/resources/views/';

        $normalized = str((string) $name)->replace(['.', '\\'], '/');

        if ($normalized->startsWith('App/')) {
            $normalized = $normalized->after('App/');
        }

        return $viewPath.$normalized->toString().'.blade.php';
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
