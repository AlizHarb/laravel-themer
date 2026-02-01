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
    protected $signature = 'theme:make {name : The name of the theme} {--parent= : Optional parent theme name} {--description= : Theme description} {--author= : Theme author} {--tags= : Comma-separated tags} {--provider : Generate a ThemeServiceProvider}';

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
        $name = $this->argument('name');
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
        File::makeDirectory($path.'/resources/assets/screenshots', 0755, true);
        File::makeDirectory($path.'/lang', 0755, true);

        /** @var string|null $parent */
        $parent = $this->option('parent');

        $config = [
            '$schema' => 'https://raw.githubusercontent.com/alizharb/laravel-themer/main/resources/schemas/theme.schema.json',
            'name' => $name,
            'slug' => $slug,
            'description' => $this->option('description') ?: 'A professional Laravel theme.',
            'version' => '1.0.0',
            'author' => $this->option('author'),
            'tags' => is_string($this->option('tags')) ? explode(',', $this->option('tags')) : ['modern', 'responsive'],
            'asset_path' => 'themes/'.$slug,
            'parent' => $parent,
            'screenshots' => [
                'resources/assets/screenshots/screenshot-light.png',
                'resources/assets/screenshots/screenshot-dark.png',
            ],
            'removable' => true,
            'disableable' => true,
        ];

        File::put(
            $path.'/theme.json',
            (string) json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        $this->createPlaceholderAssets($path);
        $this->createAssets($path, $name, $slug);
    }

    /**
     * Create the asset-related files (package.json, vite.config.js).
     */
    protected function createAssets(string $path, string $name, string $slug): void
    {
        $replace = [
            'name' => $name,
            'slug' => $slug,
            'studlyName' => Str::studly($name),
            'viteVersion' => $this->getViteVersion(),
        ];

        File::put($path.'/package.json', $this->getStubContents('package.json.stub', $replace));
        File::put($path.'/vite.config.js', $this->getStubContents('vite.config.js.stub', $replace));

        if ($this->option('provider')) {
            File::put($path.'/ThemeServiceProvider.php', $this->getStubContents('ThemeServiceProvider.php.stub', $replace));
        }
    }

    /**
     * Get the Vite version from the root package.json.
     */
    protected function getViteVersion(): string
    {
        $rootPackageJson = base_path('package.json');

        if (File::exists($rootPackageJson)) {
            $content = json_decode((string) File::get($rootPackageJson), true);

            if (is_array($content) && isset($content['devDependencies']['vite'])) {
                return (string) $content['devDependencies']['vite'];
            }
        }

        return '^6.0.0';
    }

    /**
     * Get the contents of a stub file and replace placeholders.
     *
     * @param array<string, string> $replace
     */
    protected function getStubContents(string $stub, array $replace = []): string
    {
        $stubPath = __DIR__.'/../../../resources/stubs/'.$stub;

        if (! File::exists($stubPath)) {
            return '';
        }

        $content = (string) File::get($stubPath);

        foreach ($replace as $key => $value) {
            $content = str_replace(['{{'.$key.'}}', '{{ '.$key.' }}'], (string) $value, $content);
        }

        return $content;
    }

    /**
     * Create placeholder asset files.
     */
    protected function createPlaceholderAssets(string $path): void
    {
        File::put($path.'/resources/assets/css/app.css', '/* Theme CSS */');
        File::put($path.'/resources/assets/js/app.js', '// Theme JS');

        $screenshotPath = __DIR__.'/../../../resources/assets/screenshots';
        if (File::isDirectory($screenshotPath)) {
            File::copyDirectory($screenshotPath, $path.'/resources/assets/screenshots');
        }
    }
}
