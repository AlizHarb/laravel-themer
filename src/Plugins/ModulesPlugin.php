<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Plugins;

use AlizHarb\Themer\Contracts\ThemerPlugin;
use AlizHarb\Themer\Theme;
use AlizHarb\Themer\ThemeManager;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Plugin to handle theme discovery within modular structures.
 */
final class ModulesPlugin implements ThemerPlugin
{
    /**
     * Get the unique identifier for the plugin.
     */
    public function getId(): string
    {
        return 'modules';
    }

    /**
     * Register the plugin services.
     */
    public function register(Application $app, ThemeManager $manager, Collection $themes): void
    {
        // No registration logic needed
    }

    /**
     * Bootstrap the plugin services.
     */
    public function boot(Application $app, ThemeManager $manager, Collection $themes): void
    {
        if (!class_exists('AlizHarb\Modular\ModuleRegistry')) {
            return;
        }

        /** @var mixed $registry */
        $registry = $app->make('AlizHarb\Modular\ModuleRegistry');

        /** @var array<int, array{name: string, path: string, namespace: string}> $modules */
        $modules = $registry->getModules();

        foreach ($modules as $module) {
            $themePath = $module['path'].'/resources/theme';
            $themeJson = $themePath.'/theme.json';

            if (File::exists($themeJson)) {
                /** @var string $json */
                $json = File::get($themeJson);

                /** @var array<string, mixed>|null $config */
                $config = json_decode($json, true);

                if (!is_array($config)) {
                    continue;
                }

                $name = (string) ($config['name'] ?? $module['name']);
                $slug = (string) ($config['slug'] ?? Str::slug($name));
                $assetPath = (string) ($config['asset_path'] ?? '');
                $parent = $config['parent'] ?? null;
                $version = (string) ($config['version'] ?? '1.0.0');
                $author = $config['author'] ?? null;
                /** @var array<int, array{name: string, email?: string, role?: string}> $authors */
                $authors = $config['authors'] ?? [];

                $theme = new Theme(
                    name: $name,
                    slug: $slug,
                    path: $themePath,
                    assetPath: $assetPath,
                    parent: $parent,
                    config: $config,
                    version: $version,
                    author: $author,
                    authors: $authors,
                    hasViews: is_dir($themePath.'/resources/views'),
                    hasTranslations: is_dir($themePath.'/resources/lang') || is_dir($themePath.'/lang'),
                    hasProvider: file_exists($themePath.'/ThemeServiceProvider.php'),
                    hasLivewire: is_dir($themePath.'/app/Livewire') || is_dir($themePath.'/resources/views/livewire')
                );

                $manager->register($theme);
            }
        }
    }
}
