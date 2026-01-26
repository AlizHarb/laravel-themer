<?php

declare(strict_types=1);

namespace AlizHarb\Themer\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Artisan command to generate a new theme structure.
 */
final class MakeThemeCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'theme:make {name : The name of the theme} {--parent= : Optional parent theme name}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new theme with a standard directory structure';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        /** @var string $name */
        $name = (string) $this->argument('name');
        $slug = Str::slug($name);

        /** @var string $themesPath */
        $themesPath = config('themer.themes_path', base_path('themes'));
        $path = $themesPath.DIRECTORY_SEPARATOR.$slug;

        if (File::exists($path)) {
            $this->components->error(sprintf('Theme [%s] already exists at [%s].', $slug, $path));

            return self::FAILURE;
        }

        $this->createThemeStructure($path, $name, $slug);

        $this->components->info(sprintf('Theme [%s] created successfully at [%s].', $name, $path));

        return self::SUCCESS;
    }

    /**
     * Create the directory structure and initial files for the theme.
     */
    protected function createThemeStructure(string $path, string $name, string $slug): void
    {
        File::makeDirectory($path.'/app/Livewire', 0755, true);
        File::makeDirectory($path.'/resources/views/layouts', 0755, true);
        File::makeDirectory($path.'/resources/views/livewire', 0755, true);
        File::makeDirectory($path.'/resources/assets/css', 0755, true);
        File::makeDirectory($path.'/resources/assets/js', 0755, true);
        File::makeDirectory($path.'/lang', 0755, true);

        /** @var string|null $parent */
        $parent = $this->option('parent');

        $config = [
            '$schema' => 'https://raw.githubusercontent.com/alizharb/laravel-themer/main/resources/schemas/theme.schema.json',
            'name' => $name,
            'slug' => $slug,
            'asset_path' => 'themes/'.$slug,
            'parent' => $parent,
        ];

        File::put(
            $path.'/theme.json',
            (string) json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        $this->createPlaceholderAssets($path);
    }

    /**
     * Create placeholder asset files.
     */
    protected function createPlaceholderAssets(string $path): void
    {
        File::put($path.'/resources/assets/css/app.css', '/* Theme CSS */');
        File::put($path.'/resources/assets/js/app.js', '// Theme JS');
    }
}
