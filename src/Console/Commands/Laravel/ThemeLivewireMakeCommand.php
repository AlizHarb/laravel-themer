<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Console\Commands\Laravel;

use AlizHarb\Themer\Traits\HasThemeOption;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Livewire\Features\SupportConsoleCommands\Commands\MakeCommand;
use Livewire\Livewire;

/**
 * Custom Livewire MakeCommand that supports themes.
 */
final class ThemeLivewireMakeCommand extends MakeCommand
{
    use HasThemeOption;

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $themeName = $this->getTheme();

        if ($themeName) {
            /** @var \AlizHarb\Themer\ThemeManager $manager */
            $manager = app(\AlizHarb\Themer\ThemeManager::class);
            $theme = $manager->all()->get($themeName);

            if (!$theme) {
                $this->components->error(sprintf('Theme [%s] not found.', $themeName));

                return self::FAILURE;
            }

            $themeLower = strtolower($theme->name);
            $classPath = $theme->path.'/app/Livewire';
            $viewPath = $theme->path.'/resources/views/livewire';

            if (!is_dir($classPath)) {
                mkdir($classPath, 0755, true);
            }

            if (!is_dir($viewPath)) {
                mkdir($viewPath, 0755, true);
            }

            // Temporarily override Livewire configuration to redirect the generator
            $originalNamespace = (string) config('livewire.class_namespace');
            $originalViewPath = (string) config('livewire.view_path');

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
            /** @var string|null $name */
            $name = $this->argument('name');
            if ($name) {
                $cleanName = str_replace(['::', '/'], '.', $name);
                $this->input->setArgument('name', $themeLower.'::'.$cleanName);
            }

            try {
                $result = (int) parent::handle();
            } finally {
                Config::set('livewire.class_namespace', $originalNamespace);
                Config::set('livewire.view_path', $originalViewPath);
            }

            return $result;
        }

        return (int) parent::handle();
    }
}
