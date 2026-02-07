<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Console\Commands;

use AlizHarb\Themer\Theme;
use AlizHarb\Themer\ThemeManager;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

/**
 * Upgrade themes and ensure all resources are properly synchronized.
 */
final class ThemeUpgradeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'theme:upgrade {--theme= : The name or slug of the theme to upgrade} {--a|assets : Publish theme assets during upgrade} {--c|cache : Refresh theme cache after upgrade} {--npm : Run npm install for the theme} {--no-install : Do not run the install command after upgrade}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Upgrade themes and ensure all resources are properly synchronized.';

    /**
     * Execute the console command.
     */
    public function handle(ThemeManager $manager, Filesystem $files): int
    {
        $this->components->info('Upgrading Laravel Themer ecosystem...');

        /** @var string|null $themeOption */
        $themeOption = $this->option('theme');
        $themeName = is_string($themeOption) ? $themeOption : null;

        $themes = $themeName ? $manager->all()->filter(fn (Theme $t) => $t->name === $themeName || $t->slug === $themeName) : $manager->all();

        if ($themes->isEmpty()) {
            if ($themeName) {
                $this->components->error("Theme '{$themeName}' not found.");

                return self::FAILURE;
            }

            $this->components->warn('No themes found to upgrade.');

            return self::SUCCESS;
        }

        $processed = 0;
        foreach ($themes as $theme) {
            $this->upgradeTheme($theme, $files);
            $processed++;
        }

        if ($this->option('cache')) {
            $this->call('theme:clear');
        }

        if (! $this->option('no-install')) {
            $this->call('themer:install');
        }

        $this->info("Upgrade completed! {$processed} themes processed.");

        return self::SUCCESS;
    }

    /**
     * Upgrade a specific theme.
     */
    protected function upgradeTheme(Theme $theme, Filesystem $files): void
    {
        $this->components->task("Upgrading theme: {$theme->name}", function () use ($theme, $files) {
            $this->ensureThemePaths($theme, $files);
            $this->syncThemeJson($theme, $files);
            $this->syncThemeGitignore($theme, $files);
            $this->syncThemeAssets($theme, $files);

            if ($this->option('assets')) {
                $this->callSilent('theme:publish', ['theme' => $theme->slug]);
            }

            if ($this->option('npm')) {
                $this->callSilent('theme:npm', ['--theme' => $theme->slug, 'command' => ['install']]);
            }
        });
    }

    /**
     * Ensure standard theme directories exist.
     */
    protected function ensureThemePaths(Theme $theme, Filesystem $files): void
    {
        $paths = [
            'resources/views',
            'resources/assets',
            'resources/lang',
            'app/Livewire',
        ];

        foreach ($paths as $path) {
            $fullPath = $theme->path.'/'.$path;
            if (! $files->isDirectory($fullPath)) {
                $files->makeDirectory($fullPath, 0755, true);
            }
        }
    }

    /**
     * Synchronize and clean up theme.json.
     */
    protected function syncThemeJson(Theme $theme, Filesystem $files): void
    {
        $path = $theme->path.'/theme.json';

        if (! $files->exists($path)) {
            $config = [
                'name' => $theme->name,
                'slug' => $theme->slug,
                'version' => $theme->version,
                'description' => '',
                'parent' => $theme->parent,
            ];
        } else {
            $content = (string) $files->get($path);
            /** @var array<string, mixed> $config */
            $config = json_decode($content, true) ?: [];

            // Standardize $schema
            if (! isset($config['$schema'])) {
                $config['$schema'] = 'https://raw.githubusercontent.com/alizharb/laravel-themer/main/resources/schemas/theme.schema.json';
            }

            // Standardize slug if missing
            if (! isset($config['slug'])) {
                $config['slug'] = $theme->slug;
            }

            // Standardize version
            if (! isset($config['version'])) {
                $config['version'] = $theme->version ?: '1.0.0';
            }
        }

        $files->put(
            $path,
            (string) json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );
    }

    /**
     * Synchronize .gitignore file.
     */
    protected function syncThemeGitignore(Theme $theme, Filesystem $files): void
    {
        $path = $theme->path.'/.gitignore';

        if (! $files->exists($path)) {
            $stubPath = __DIR__.'/../../../resources/stubs/gitignore.stub';

            if ($files->exists($stubPath)) {
                $files->copy($stubPath, $path);
            }
        }
    }

    /**
     * Synchronize theme asset configuration (package.json, vite.config.js).
     */
    protected function syncThemeAssets(Theme $theme, Filesystem $files): void
    {
        // 1. Ensure package.json
        $packagePath = $theme->path.'/package.json';
        if (! $files->exists($packagePath)) {
            $files->put($packagePath, (string) json_encode([
                'name' => '@themes/'.$theme->slug,
                'private' => true,
                'version' => $theme->version,
                'type' => 'module',
            ], JSON_PRETTY_PRINT));
        }

        // 2. Ensure vite.config.js
        $vitePath = $theme->path.'/vite.config.js';
        if (! $files->exists($vitePath)) {
            $files->put($vitePath, "import { defineConfig } from 'vite';\nimport laravel from 'laravel-vite-plugin';\n\nexport default defineConfig({\n    plugins: [\n        laravel({\n            input: ['resources/assets/css/app.css', 'resources/assets/js/app.js'],\n            refresh: true,\n        }),\n    ],\n});\n");
        }
    }
}
