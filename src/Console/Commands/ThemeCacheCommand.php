<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Console\Commands;

use AlizHarb\Themer\ThemeManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ThemeCacheCommand extends Command
{
    protected $signature = 'theme:cache';
    protected $description = 'Create a cache file for faster theme loading';

    public function handle(ThemeManager $manager): int
    {
        $this->call('theme:clear');

        // Force scan by looking at raw config path since Manager usually auto-scans in boot
        $themesPath = config('themer.themes_path');

        // We manually rebuild the collection to ensure we get fresh data
        // Accessing the scan logic is tricky if it's protected or strictly reading cache
        // But we just cleared the cache, so $manager->scan() if called again would work?
        // Actually, $manager is singleton and already booted with potentially Empty or Cached data.

        // We need a way to "re-scan".
        // For now, let's reproduce the scan logic here or make scan public/refreshable.
        // The methods are:
        // 1. Manually scan directory here.
        // 2. Add 'refresh()' to Manager.

        // Let's implement manual scan here to keep Manager clean, mirroring Manager logic.
        if (!File::isDirectory($themesPath)) {
            $this->components->error("Themes directory not found at: $themesPath");

            return self::FAILURE;
        }

        $themes = collect(File::directories($themesPath))
            ->map(fn (string $dir) => $dir . '/theme.json')
            ->filter(fn (string $file) => File::exists($file))
            ->map(function (string $path) {
                $dir = dirname($path);
                $config = json_decode(File::get($path), true, 512, JSON_THROW_ON_ERROR);

                return [
                    'name' => $config['name'],
                    'path' => $dir,
                    'assetPath' => $config['asset_path'] ?? '',
                    'parent' => $config['parent'] ?? null,
                    'config' => $config,
                ];
            })->values()->all();

        $cachePath = $manager->getCachePath();

        $content = '<?php return ' . var_export($themes, true) . ';';
        File::put($cachePath, $content);

        $this->components->info('Themes cached successfully!');

        return self::SUCCESS;
    }
}
