<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Console command to install and configure Laravel Themer.
 */
class ThemeInstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'themer:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install and configure Laravel Themer';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->components->info('Installing Laravel Themer...');

        $this->publishResources();
        $this->ensureThemesDirectoryExists();
        $this->configureVite();

        $this->components->info('Laravel Themer has been successfully installed! 🎨');

        if ($this->confirm('Would you like to show some love by starring the repo on GitHub? ⭐', true)) {
            $url = 'https://github.com/alizharb/laravel-themer';
            if (PHP_OS_FAMILY === 'Darwin') {
                exec("open {$url}");
            } elseif (PHP_OS_FAMILY === 'Windows') {
                exec("start {$url}");
            } elseif (PHP_OS_FAMILY === 'Linux') {
                exec("xdg-open {$url}");
            }
            $this->line("Thanks! You're awesome! 💙");
        }

        return self::SUCCESS;
    }

    /**
     * Publish the package resources.
     */
    protected function publishResources(): void
    {
        $this->info('Publishing resources...');

        $this->call('vendor:publish', [
            '--provider' => "AlizHarb\Themer\ThemeServiceProvider",
            '--tag' => 'themer-config',
        ]);
    }

    /**
     * Ensure the themes directory exists.
     */
    protected function ensureThemesDirectoryExists(): void
    {
        $path = config('themer.themes_path', base_path('themes'));

        if (!File::isDirectory($path)) {
            File::makeDirectory($path, 0755, true);
            $this->components->info("Created themes directory at: {$path}");
        }
    }

    /**
     * Configure Vite for theme assets.
     */
    protected function configureVite(): void
    {
        $viteConfigPath = base_path('vite.config.js');

        if (!File::exists($viteConfigPath)) {
            return;
        }

        $this->createViteThemerLoader();

        $content = (string) File::get($viteConfigPath);

        if (!str_contains($content, 'vite.themer.js')) {
            $this->components->warn('Vite needs to be configured to load theme assets.');

            if ($this->confirm('Would you like to automatically configure vite.config.js?', true)) {
                // Add themerLoader import - handle both single-line and multiline imports
                if (!str_contains($content, 'themerLoader')) {
                    // Try to find the vite import (single or multiline)
                    if (preg_match('/import\s+\{[^}]*defineConfig[^}]*\}\s+from\s+[\'"]vite[\'"];?/', $content, $matches)) {
                        $viteImport = $matches[0];
                        $content = str_replace(
                            $viteImport,
                            $viteImport."\nimport { themerLoader } from './vite.themer.js';",
                            $content
                        );
                    }
                }

                $content = preg_replace(
                    "/input:\s*\[([^\]]+)\],/",
                    "input: [\n                $1,\n                ...themerLoader.inputs()\n            ],",
                    $content
                );

                // Try to find if refresh is already an array or true
                if (str_contains($content, 'refresh: [')) {
                    $content = str_replace(
                        'refresh: [',
                        "refresh: [\n                ...themerLoader.refreshPaths(),",
                        $content
                    );
                } else {
                    $content = preg_replace(
                        "/refresh:\s*true,/",
                        "refresh: [\n                ...themerLoader.refreshPaths(),\n                'resources/views/**',\n                'routes/**',\n            ],",
                        $content
                    );
                }

                File::put($viteConfigPath, $content);
                $this->components->info('Configured vite.config.js to use the themer loader.');
            } else {
                $this->components->info('To manually configure Vite, add the following to your vite.config.js:');
                $this->line("\nimport { themerLoader } from './vite.themer.js';\n");
                $this->line('// In plugins -> laravel() configuration:');
                $this->line("input: [\n    // ... existing inputs,\n    ...themerLoader.inputs()\n],");
                $this->line("refresh: [\n    // ... existing paths,\n    ...themerLoader.refreshPaths()\n],");
            }
        }
    }

    /**
     * Create the vite.themer.js loader file.
     */
    protected function createViteThemerLoader(): void
    {
        $path = base_path('vite.themer.js');

        if (File::exists($path)) {
            return;
        }

        $stubPath = __DIR__.'/../../../resources/stubs/vite.themer.js.stub';

        if (File::exists($stubPath)) {
            $content = (string) File::get($stubPath);
            File::put($path, $content);
            $this->components->info('Created vite.themer.js loader.');
        }
    }
}
