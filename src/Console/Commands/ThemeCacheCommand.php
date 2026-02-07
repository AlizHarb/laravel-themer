<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Console\Commands;

use AlizHarb\Themer\ThemeManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class ThemeCacheCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'theme:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a cache file for faster theme loading';

    /**
     * Execute the console command.
     */
    public function handle(ThemeManager $manager): int
    {
        $this->call('theme:clear');

        /** @var string $themesPath */
        $themesPath = (string) config('themer.themes_path', base_path('themes'));

        if (! File::isDirectory($themesPath)) {
            $this->components->error("Themes directory not found at: {$themesPath}");

            return self::FAILURE;
        }

        /** @var array<int, array<string, mixed>> $themes */
        $themes = collect(File::directories($themesPath))
            ->map(fn (string $dir) => $dir.'/theme.json')
            ->filter(fn (string $file) => File::exists($file))
            ->map(function (string $path) {
                $dir = dirname($path);
                /** @var array<string, mixed> $config */
                $config = json_decode((string) File::get($path), true, 512, JSON_THROW_ON_ERROR);

                return [
                    'name' => (string) ($config['name'] ?? basename($dir)),
                    'slug' => (string) ($config['slug'] ?? Str::slug((string) ($config['name'] ?? basename($dir)))),
                    'path' => $dir,
                    'assetPath' => (string) ($config['asset_path'] ?? ''),
                    'parent' => $config['parent'] ?? null,
                    'config' => $config,
                    'version' => (string) ($config['version'] ?? '1.0.0'),
                    'author' => $config['author'] ?? null,
                    'authors' => (array) ($config['authors'] ?? []),
                    'hasViews' => is_dir($dir.'/resources/views'),
                    'hasTranslations' => is_dir($dir.'/resources/lang') || is_dir($dir.'/lang'),
                    'hasProvider' => file_exists($dir.'/ThemeServiceProvider.php'),
                    'hasLivewire' => is_dir($dir.'/app/Livewire') || is_dir($dir.'/resources/views/livewire'),
                    'removable' => (bool) ($config['removable'] ?? true),
                    'disableable' => (bool) ($config['disableable'] ?? true),
                    'screenshots' => (array) ($config['screenshots'] ?? []),
                    'tags' => (array) ($config['tags'] ?? []),
                ];
            })->values()->all();

        $cachePath = $manager->getCachePath();

        $content = '<?php return '.var_export($themes, true).';';
        File::put($cachePath, $content);

        $this->components->info('Themes cached successfully!');

        return self::SUCCESS;
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
