<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Traits;

use AlizHarb\Themer\ThemeManager;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Symfony\Component\Console\Input\InputOption;

/**
 * Trait to add the --theme option to Artisan commands.
 */
trait HasThemeOption
{
    /**
     * Determine if the command is running for a specific theme.
     */
    protected function isTheme(): bool
    {
        return $this->option('theme') !== null;
    }

    /**
     * Get the theme name from the option.
     */
    protected function getTheme(): ?string
    {
        /** @var mixed $theme */
        $theme = $this->option('theme');

        return is_string($theme) ? $theme : null;
    }

    /**
     * Execute a callback within the context of a specific theme.
     */
    protected function withTheme(callable $next): int
    {
        $themeName = $this->getTheme();

        if (! $themeName) {
            /** @var int $result */
            $result = $next();

            return $result;
        }

        /** @var ThemeManager $manager */
        $manager = app(ThemeManager::class);
        $theme = $manager->all()->get($themeName);

        if (! $theme) {
            $this->components->error(sprintf('Theme [%s] not found.', $themeName));

            return 1;
        }

        $themeLower = strtolower($theme->name);
        $classPath = $theme->path.'/app/Livewire';
        $viewPath = $theme->path.'/resources/views/livewire';

        if (! is_dir($classPath)) {
            mkdir($classPath, 0755, true);
        }

        if (! is_dir($viewPath)) {
            mkdir($viewPath, 0755, true);
        }

        // Temporarily override Livewire configuration to redirect the generator
        /** @var string $originalNamespace */
        $originalNamespace = config('livewire.class_namespace', '');
        /** @var string $originalViewPath */
        $originalViewPath = config('livewire.view_path', '');
        /** @var string $originalNamespace */
        $originalNamespace = config('livewire.class_namespace', '');
        /** @var string $originalViewPath */
        $originalViewPath = config('livewire.view_path', '');

        // Register theme namespace for Livewire
        Livewire::addNamespace(
            $themeLower,
            $viewPath,
            'Theme\\'.Str::studly($theme->name).'\\Livewire',
            $classPath,
            $viewPath
        );

        // Use Config::set to make sure internal tools picking up config see the theme paths
        Config::set('livewire.class_namespace', 'Theme\\'.Str::studly($theme->name).'\\Livewire');
        Config::set('livewire.view_path', $viewPath);

        // Prefix name with theme namespace
        // @phpstan-ignore-next-line
        if ($this->hasArgument('name')) {
            /** @var string|null $name */
            // @phpstan-ignore-next-line
            $name = $this->argument('name');
            if ($name) {
                $cleanName = str_replace(['::', '/'], '.', $name);
                $this->input->setArgument('name', $themeLower.'::'.$cleanName);
            }
        }

        try {
            /** @var int $result */
            $result = $next();

            return $result;
        } finally {
            Config::set('livewire.class_namespace', $originalNamespace);
            Config::set('livewire.view_path', $originalViewPath);
        }
    }

    /**
     * Get the theme console command options.
     *
     * @return array<int, array{0: string, 1: string|null, 2: int, 3: string, 4: mixed|null}>
     */
    protected function getThemeOptions(): array
    {
        return [
            ['theme', null, InputOption::VALUE_OPTIONAL, 'The name of the theme.', null],
        ];
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
