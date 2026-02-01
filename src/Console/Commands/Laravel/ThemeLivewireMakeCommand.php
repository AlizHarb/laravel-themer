<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Console\Commands\Laravel;

use AlizHarb\Themer\Traits\HasThemeOption;
use Livewire\Features\SupportConsoleCommands\Commands\MakeCommand;

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
        /** @var string|null $themeName */
        $themeName = $this->getTheme();

        if ($themeName) {
            /** @var \AlizHarb\Themer\ThemeManager $manager */
            $manager = app(\AlizHarb\Themer\ThemeManager::class);

            /** @var ?string $nameArg */
            $nameArg = $this->argument('name');
            $theme = $manager->all()->get($themeName);

            if ($nameArg && $theme) {
                // Prefix with theme slug to ensure Livewire uses the correct namespace
                $this->input->setArgument('name', $theme->slug.'::'.$nameArg);
            }

            $result = $manager->useThemeGenerator($themeName, fn () => (int) parent::handle());

            // Move files from app/Http/Livewire to app/Livewire if they were created in the wrong location
            if ($nameArg) {
                $this->relocateGeneratedFiles($themeName, (string) $nameArg);
            }

            return $result;
        }

        return (int) parent::handle();
    }

    /**
     * Relocate generated Livewire files from Http/Livewire to Livewire directory.
     */
    protected function relocateGeneratedFiles(string $themeName, string $componentName): void
    {
        /** @var \AlizHarb\Themer\ThemeManager $manager */
        $manager = app(\AlizHarb\Themer\ThemeManager::class);
        $theme = $manager->all()->get($themeName);

        if (! $theme) {
            return;
        }

        // Convert component name to file path
        $cleanName = str_replace(['::', '.'], '/', $componentName);

        // Where Livewire actually creates the file
        $actualClassPath = $theme->path.'/app/Http/Livewire/'.$cleanName.'.php';

        // Where we want it to be
        $targetClassPath = $theme->path.'/app/Livewire/'.$cleanName.'.php';

        // Move the file if it exists in the wrong location
        if (file_exists($actualClassPath) && $actualClassPath !== $targetClassPath) {
            $targetDir = dirname($targetClassPath);
            if (! is_dir($targetDir)) {
                mkdir($targetDir, 0755, true);
            }
            rename($actualClassPath, $targetClassPath);

            // Clean up empty Http/Livewire directory
            $httpLivewireDir = dirname($actualClassPath);
            if (is_dir($httpLivewireDir) && count(scandir($httpLivewireDir) ?: []) === 2) {
                @rmdir($httpLivewireDir);
                $parentDir = dirname($httpLivewireDir);
                if (is_dir($parentDir) && count(scandir($parentDir) ?: []) === 2) {
                    @rmdir($parentDir); // Try to remove Http dir if empty
                }
            }
        }
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
