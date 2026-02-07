<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Console\Commands;

use AlizHarb\Themer\ThemeServiceProvider;
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
    protected $name = 'themer:install';

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
        $this->configureNpmWorkspaces();
        $this->configureNpmScripts();

        $this->components->info('Laravel Themer has been successfully installed! 🎨');

        if ($this->confirm('Would you like to show some love by starring the repo on GitHub? ⭐', true)) {
            $url = 'https://github.com/alizharb/laravel-themer';
            $os = PHP_OS_FAMILY;

            if ($os === 'Darwin') {
                exec("open {$url}");
            } elseif ($os === 'Windows') {
                exec("start {$url}");
            } elseif ($os === 'Linux') {
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
            '--provider' => ThemeServiceProvider::class,
            '--tag' => 'themer-config',
        ]);
    }

    /**
     * Ensure the themes directory exists.
     */
    protected function ensureThemesDirectoryExists(): void
    {
        /** @var string $path */
        $path = config('themer.themes_path', base_path('themes'));

        if (! File::isDirectory($path)) {
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

        if (! File::exists($viteConfigPath)) {
            return;
        }

        $this->createViteThemerLoader();

        $content = (string) File::get($viteConfigPath);

        if (! str_contains($content, 'vite.themer.js')) {
            $this->components->warn('Vite needs to be configured to load theme assets.');

            if ($this->confirm('Would you like to automatically configure vite.config.js?', true)) {
                // Add themerLoader import - handle both single-line and multiline imports
                if (! str_contains($content, 'themerLoader')) {
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

                $content = (string) preg_replace(
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
                    $content = (string) preg_replace(
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

    /**
     * Configure NPM Workspaces in package.json.
     */
    protected function configureNpmWorkspaces(): void
    {
        $packageJsonPath = base_path('package.json');

        if (! File::exists($packageJsonPath)) {
            return;
        }

        /** @var array<string, mixed> $packageJson */
        $packageJson = json_decode((string) File::get($packageJsonPath), true);

        /** @var string $themesRawPath */
        $themesRawPath = config('themer.themes_path', 'themes');
        $themesPath = str_replace(DIRECTORY_SEPARATOR, '/', $themesRawPath).'/*';
        /** @var array<int, string> $workspaces */
        $workspaces = (array) ($packageJson['workspaces'] ?? []);

        if (! in_array($themesPath, $workspaces, true)) {
            $this->components->warn('NPM Workspaces are recommended for per-theme assets.');

            if ($this->confirm('Would you like to automatically configure NPM Workspaces?', true)) {
                $workspaces[] = $themesPath;
                $packageJson['workspaces'] = array_values(array_unique($workspaces));

                File::put($packageJsonPath, (string) json_encode($packageJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                $this->components->info("Configured package.json workspaces to include {$themesPath}");
                $this->warn('Please run "npm install" to initialize workspaces.');
            }
        }
    }

    /**
     * Configure NPM Scripts in package.json.
     */
    protected function configureNpmScripts(): void
    {
        $packageJsonPath = base_path('package.json');

        if (! File::exists($packageJsonPath)) {
            return;
        }

        /** @var array<string, mixed> $packageJson */
        $packageJson = json_decode((string) File::get($packageJsonPath), true);

        /** @var array<string, string> $scripts */
        $scripts = (array) ($packageJson['scripts'] ?? []);
        $needsUpdate = false;

        $newScripts = [
            'themes:dev' => 'npm run dev --workspaces',
            'themes:build' => 'npm run build --workspaces',
        ];

        foreach ($newScripts as $key => $command) {
            if (! isset($scripts[$key])) {
                $scripts[$key] = $command;
                $needsUpdate = true;
            }
        }

        if ($needsUpdate) {
            $packageJson['scripts'] = $scripts;
            File::put($packageJsonPath, (string) json_encode($packageJson, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
            $this->components->info('Added shortcut scripts for themes to package.json');
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
        $options = parent::getOptions();

        return $options;
    }
}
